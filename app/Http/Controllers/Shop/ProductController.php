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

        $query = Product::with(['category', 'brand'])
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->where('is_active', true);

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('brand')) {
            $query->whereHas('brand', fn ($q) => $q->where('slug', $request->brand));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('sort')) {
            match ($request->sort) {
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
        } else {
            $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        ProductListReturn::remember($request);

        return view('shop.products.index', compact('products', 'categories', 'brands'));
    }

    public function show(Request $request, string $slug, RecentlyViewedService $recentlyViewed)
    {
        $product = Product::with(['category', 'brand', 'images'])
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

        $approvedCount = $product->approvedReviews()->count();
        $ratingDistribution = collect(range(5, 1))->mapWithKeys(function ($star) use ($product) {
            return [$star => $product->approvedReviews()->where('rating', $star)->count()];
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
