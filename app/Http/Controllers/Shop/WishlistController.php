<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\WishlistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(WishlistService $wishlist): View
    {
        return view('shop.wishlist.index', ['products' => $wishlist->products()]);
    }

    public function toggle(Product $product, WishlistService $wishlist): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $added = $wishlist->toggle($product);

        return back()->with('success', $added ? 'به علاقه‌مندی‌ها اضافه شد.' : 'از علاقه‌مندی‌ها حذف شد.');
    }
}
