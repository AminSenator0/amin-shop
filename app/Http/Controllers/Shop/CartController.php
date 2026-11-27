<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\AddToCartRequest;
use App\Http\Requests\Shop\UpdateCartRequest;
use Illuminate\Http\Request;
use App\Services\CartService;
use App\Support\ShoppingFlow;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index()
    {
        return view('shop.cart', ['items' => $this->cart->items(), 'subtotal' => $this->cart->subtotal()]);
    }

    public function store(AddToCartRequest $request)
    {
        try {
            $this->cart->add(
                $request->validated('product_id'),
                $request->integer('quantity', 1),
                $request->input('size'),
                $request->input('color'),
                $request->input('custom_fields', []), // ← اضافه شده
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        ShoppingFlow::activate();

        return back()->with('success', 'محصول به سبد خرید اضافه شد.');
    }

    public function addPresale(Request $request)
    {
        $validated = $request->validate([
            'presale_product_id' => ['required', 'integer', 'exists:presale_products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = app(CartService::class);

        try {
            $cart->addPresale((int) $validated['presale_product_id'], (int) $validated['quantity']);
        } catch (\RuntimeException|\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('cart.index')->with('success', 'محصول پیش‌فروش به سبد اضافه شد.');
    }
    public function update(UpdateCartRequest $request, string $lineKey)
    {
        try {
            $this->cart->update($lineKey, $request->integer('quantity'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'سبد خرید به‌روزرسانی شد.');
    }

    public function destroy(string $lineKey)
    {
        $this->cart->remove($lineKey);

        return back()->with('success', 'محصول از سبد خرید حذف شد.');
    }
}