<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\RecentlyViewedService;
use App\Support\ProductListReturn;
use App\Support\ShoppingFlow;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        if ($request->boolean('shop')) {
            ShoppingFlow::activate();
        }

        // ✅ images هم eager load شد (برای کارت محصول)
        $query = Product::with(['category', 'brand', 'images'])
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->where('is_active', true);

        // ─── دسته‌بندی ─────────────────────────────
        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        // ─── برند (چندتایی) ───────────────────────
        if ($request->has('brands')) {
            $brandSlugs = array_filter((array) $request->brands);
            if (! empty($brandSlugs)) {
                $query->whereHas('brand', fn ($q) => $q->whereIn('slug', $brandSlugs));
            }
        } elseif ($request->filled('brand')) {
            $query->whereHas('brand', fn ($q) => $q->where('slug', $request->brand));
        }

        // ─── جستجو ────────────────────────────────
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // ─── رنج قیمت ─────────────────────────────
        if ($request->filled('min_price')) {
            $query->where('price', '>=', (int) $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', (int) $request->max_price);
        }

        // ─── فقط موجود ────────────────────────────
        if ($request->boolean('in_stock')) {
            $query->where(function ($q) {
                $q->whereHas('variants', fn ($vq) => $vq->where('stock', '>', 0))
                  ->orWhere(function ($q2) {
                      $q2->doesntHave('variants')->where('stock', '>', 0);
                  });
            });
        }

        // ─── فقط تخفیف‌دار ────────────────────────
        if ($request->boolean('discount')) {
            $query->whereNotNull('compare_price')
                  ->whereColumn('compare_price', '>', 'price');
        }

        // ─── حداقل امتیاز ─────────────────────────
        if ($request->filled('min_rating')) {
            $minRating = (float) $request->min_rating;
            $query->whereRaw('(
                SELECT AVG(rating) 
                FROM reviews 
                WHERE reviews.product_id = products.id 
                AND reviews.is_approved = 1
            ) >= ?', [$minRating]);
        }

        // ─── مرتب‌سازی ─────────────────────────────
        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'newest' => $query->latest(),
            'discount' => $query
                ->whereNotNull('compare_price')
                ->whereColumn('compare_price', '>', 'price')
                ->orderByRaw('(compare_price - price) / compare_price DESC'),
            'bestseller' => $query->orderByDesc(
                \App\Models\OrderItem::selectRaw('COALESCE(SUM(quantity), 0)')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->whereColumn('order_items.product_id', 'products.id')
                    ->where('orders.status', '!=', \App\Enums\OrderStatus::Cancelled)
            ),
            default => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        $activeBrands = array_filter((array) $request->brands);
        $maxProductPrice = Product::where('is_active', true)->max('price') ?? 0;

        ProductListReturn::remember($request);

        return view('shop.products.index', compact(
            'products', 'categories', 'brands', 'sort', 'activeBrands', 'maxProductPrice'
        ));
    }

    public function show(Request $request, string $slug, RecentlyViewedService $recentlyViewed)
    {
        // ✅ withCount('approvedReviews') اضافه شد
        $product = Product::with(['category', 'brand', 'images', 'variants', 'attributeValues.attribute'])
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $recentlyViewed->track($product->id);

        $relatedProducts = Product::with(['category', 'images'])
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->take(4)
            ->get();

        $recentlyViewedProducts = $recentlyViewed->products(8)
            ->filter(fn ($item) => $item->id !== $product->id)
            ->take(4)
            ->values();

        $reviewsQuery = $product->reviews()->with('user')->latest();

        if (auth()->check()) {
            $reviews = $reviewsQuery
                ->where(function ($query) {
                    $query->where('is_approved', true)
                        ->orWhere('user_id', auth()->id());
                })
                ->get();
        } else {
            $reviews = $product->approvedReviews()->with('user')->latest()->get();
        }

        // ✅ از withCount استفاده شد → دیگه کوئری اضافی نمی‌زنه
        $approvedCount = $product->approved_reviews_count;

        // ✅ فقط ۱ کوئری با groupBy به جای ۵ کوئری جدا
        $rawDistribution = $product->approvedReviews()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating');

        $ratingDistribution = collect(range(5, 1))->mapWithKeys(function ($star) use ($rawDistribution) {
            return [$star => $rawDistribution[$star] ?? 0];
        });

        $canReview = auth()->check()
            && auth()->user()->hasPurchasedProduct($product->id)
            && ! $product->reviews()->where('user_id', auth()->id())->exists();

        $productsBackUrl = ProductListReturn::resolve($request, $product);
        $productsBackLabel = ProductListReturn::label($productsBackUrl);

        return view('shop.products.show', compact(
            'product',
            'relatedProducts',
            'recentlyViewedProducts',
            'reviews',
            'approvedCount',
            'ratingDistribution',
            'canReview',
            'productsBackUrl',
            'productsBackLabel',
        ));
    }
}