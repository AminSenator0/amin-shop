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
        $featuredProducts = $this->productQuery()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest()
            ->take(8)
            ->get();

        $latestProducts = $this->productQuery()
            ->where('is_active', true)
            ->latest()
            ->take(8)
            ->get();

        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $sliders = Slider::active();
        $banners = Banner::active('home');

        $bestsellerIds = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', OrderStatus::Cancelled)
            ->selectRaw('order_items.product_id, SUM(order_items.quantity) as total_sold')
            ->groupBy('order_items.product_id')
            ->orderByDesc('total_sold')
            ->limit(8)
            ->pluck('product_id');

        $bestsellerProducts = $bestsellerIds->isNotEmpty()
            ? $this->productQuery()
                ->whereIn('id', $bestsellerIds)
                ->where('is_active', true)
                ->get()
                ->sortBy(fn ($p) => $bestsellerIds->search($p->id))
                ->values()
            : collect();

        $discountedProducts = $this->productQuery()
            ->where('is_active', true)
            ->whereNotNull('compare_price')
            ->whereColumn('compare_price', '>', 'price')
            ->orderByRaw('(compare_price - price) / compare_price DESC')
            ->take(8)
            ->get();

        $heroMaxDiscount = (int) $discountedProducts->max(fn (Product $p) => $p->discountPercent());

        $canonicalBrandSlugs = ['samsung', 'apple', 'sony', 'lg', 'xiaomi', 'asus', 'lenovo', 'nike', 'adidas', 'puma'];
        $slugOrder = implode(',', array_map(
            fn (string $slug) => "'{$slug}'",
            $canonicalBrandSlugs
        ));

        $brands = Brand::query()
            ->where('is_active', true)
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->orderByRaw("CASE WHEN slug IN ({$slugOrder}) THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get()
            ->unique('name')
            ->take(12)
            ->values();

        $testimonialReviews = Review::with(['user', 'product'])
            ->where('is_approved', true)
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->whereHas('product', fn ($query) => $query->where('is_active', true))
            ->latest()
            ->take(12)
            ->get();

        $faqs = Faq::activeForHomepage();
        $showFaqViewAll = Faq::hasMoreForHomepage();
        $blogPosts = BlogPost::published(3);

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

        $shopStats = [
            'orders' => Order::where('status', '!=', OrderStatus::Cancelled)->count(),
            'customers' => User::count(),
            'avgRating' => round(Review::where('is_approved', true)->avg('rating') ?? 0, 1),
            'reviews' => Review::where('is_approved', true)->count(),
        ];

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
