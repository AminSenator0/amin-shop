<?php

namespace App\Http\Controllers\Shop;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Faq;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Slider;
use App\Models\User;
use App\Services\RecentlyViewedService;
use App\Services\WishlistService;
use App\Support\HomepageContent;
use App\Support\StoreSettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    private function productQuery()
    {
        return Product::with(['category', 'images'])
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating');
    }

    public function index(RecentlyViewedService $recentlyViewed)
    {
        // ── کش داده‌های عمومی (بالاترین تأثیر روی TTFB) ──

        $featuredProducts = Cache::remember('home_featured_products', 600, fn () =>
            $this->productQuery()
                ->where('is_active', true)
                ->where('is_featured', true)
                ->latest()
                ->take(8)
                ->get()
        );

        $latestProducts = Cache::remember('home_latest_products', 600, fn () =>
            $this->productQuery()
                ->where('is_active', true)
                ->latest()
                ->take(8)
                ->get()
        );

        $categories = Cache::remember('home_categories', 1800, fn () =>
            Category::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
        );

        $sliders = Cache::remember('home_sliders', 1800, fn () =>
            Slider::active()
        );

        $banners = Cache::remember('home_banners', 1800, fn () =>
            Banner::active('home')
        );

        $bestsellerIds = Cache::remember('home_bestseller_ids', 900, function () {
            return OrderItem::query()
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', '!=', OrderStatus::Cancelled)
                ->selectRaw('order_items.product_id, SUM(order_items.quantity) as total_sold')
                ->groupBy('order_items.product_id')
                ->orderByDesc('total_sold')
                ->limit(8)
                ->pluck('product_id');
        });

        $bestsellerProducts = $bestsellerIds->isNotEmpty()
            ? Cache::remember('home_bestseller_products', 900, function () use ($bestsellerIds) {
                return $this->productQuery()
                    ->whereIn('id', $bestsellerIds)
                    ->where('is_active', true)
                    ->get()
                    ->sortBy(fn ($p) => $bestsellerIds->search($p->id))
                    ->values();
            })
            : collect();

        $discountedProducts = Cache::remember('home_discounted_products', 600, fn () =>
            $this->productQuery()
                ->where('is_active', true)
                ->whereNotNull('compare_price')
                ->whereColumn('compare_price', '>', 'price')
                ->orderByRaw('(compare_price - price) / compare_price DESC')
                ->take(8)
                ->get()
        );

        $heroMaxDiscount = (int) $discountedProducts->max(fn (Product $p) => $p->discountPercent());

        $canonicalBrandSlugs = ['samsung', 'apple', 'sony', 'lg', 'xiaomi', 'asus', 'lenovo', 'nike', 'adidas', 'puma'];
        $slugOrder = implode(',', array_map(
            fn (string $slug) => "'{$slug}'",
            $canonicalBrandSlugs
        ));

        $brands = Cache::remember('home_brands', 1800, function () use ($slugOrder) {
            return Brand::query()
                ->where('is_active', true)
                ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
                ->orderByRaw("CASE WHEN slug IN ({$slugOrder}) THEN 0 ELSE 1 END")
                ->orderBy('name')
                ->get()
                ->unique('name')
                ->take(12)
                ->values();
        });

        $testimonialReviews = Cache::remember('home_testimonial_reviews', 900, fn () =>
            Review::with(['user', 'product'])
                ->where('is_approved', true)
                ->whereNotNull('comment')
                ->where('comment', '!=', '')
                ->whereHas('product', fn ($query) => $query->where('is_active', true))
                ->latest()
                ->take(12)
                ->get()
        );

        $faqs = Cache::remember('home_faqs', 1800, fn () => Faq::activeForHomepage());
        $showFaqViewAll = Cache::remember('home_faq_view_all', 1800, fn () => Faq::hasMoreForHomepage());
        $blogPosts = Cache::remember('home_blog_posts', 1800, fn () => BlogPost::published(3));

        // ── داده‌های کاربر خاص (بدون کش) ──
        $recentlyViewedProducts = $recentlyViewed->products(8);

        $recommendedProducts = collect();
        if ($categoryId = $recentlyViewed->lastCategoryId()) {
            $excludeIds = $recentlyViewed->ids();
            $recommendedProducts = $this->productQuery()
                ->where('is_active', true)
                ->where('category_id', $categoryId)
                ->when(! empty($excludeIds), fn ($q) => $q->whereNotIn('id', $excludeIds))
                ->inRandomOrder()
                ->take(8)
                ->get();
        }

        // ── داده‌های حساس به زمان (بدون کش یا کش خیلی کوتاه) ──
        $featuredCoupon = null;
        if (StoreSettings::bool('homepage_coupon_bar_enabled')) {
            $couponId = StoreSettings::int('homepage_featured_coupon_id');
            $featuredCoupon = $couponId > 0
                ? Coupon::find($couponId)
                : Coupon::where('is_active', true)->latest()->first();

            if ($featuredCoupon && ! $featuredCoupon->isAvailable()) {
                $featuredCoupon = null;
            }
        }

        $flashSaleEndsAt = null;
        $flashSaleProducts = collect();
        if (StoreSettings::bool('homepage_flash_sale_enabled')) {
            $endsAt = StoreSettings::get('homepage_flash_sale_ends_at');
            if ($endsAt) {
                $flashSaleEndsAt = Carbon::parse($endsAt);
                if ($flashSaleEndsAt->isFuture()) {
                    $flashSaleProducts = $discountedProducts->take(6);
                } else {
                    $flashSaleEndsAt = null;
                }
            }
        }

        $shopStats = Cache::remember('home_shop_stats', 900, fn () => [
            'orders' => Order::where('status', '!=', OrderStatus::Cancelled)->count(),
            'customers' => User::count(),
            'avgRating' => round(Review::where('is_approved', true)->avg('rating') ?? 0, 1),
            'reviews' => Review::where('is_approved', true)->count(),
        ]);

        // ─ه شخصی‌سازی Hero (کاربر خاص — بدون کش) ──
        $heroPersonalization = null;
        if (auth()->check()) {
            $user = auth()->user();
            $nameParts = preg_split('/\s+/u', trim($user->name), 2);

            $heroPersonalization = [
                'firstName' => $nameParts[0] ?: $user->name,
                'wishlistCount' => app(WishlistService::class)->count(),
                'activeOrder' => Order::query()
                    ->where('user_id', $user->id)
                    ->whereIn('status', [OrderStatus::Paid, OrderStatus::Processing, OrderStatus::Shipped])
                    ->latest()
                    ->first(),
            ];
        }

        $homeCollections = HomepageContent::collections(
            $categories,
            $discountedProducts,
            $bestsellerProducts,
            $heroMaxDiscount,
            StoreSettings::int('free_shipping_threshold'),
        );

        $sizeChartSample = HomepageContent::sizeChartSample();

        return view('shop.home', compact(
            'featuredProducts',
            'latestProducts',
            'categories',
            'sliders',
            'banners',
            'bestsellerProducts',
            'discountedProducts',
            'brands',
            'testimonialReviews',
            'faqs',
            'showFaqViewAll',
            'blogPosts',
            'recentlyViewedProducts',
            'recommendedProducts',
            'featuredCoupon',
            'flashSaleEndsAt',
            'flashSaleProducts',
            'shopStats',
            'heroMaxDiscount',
            'heroPersonalization',
            'homeCollections',
            'sizeChartSample',
        ));
    }
}
