<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\PresaleCartService;
use Illuminate\Http\Request;

class PresaleCartController extends Controller
{
    public function __construct(
        private PresaleCartService $cart,
    ) {}

    public function index()
    {
        $items = $this->cart->items();
        $subtotal = $this->cart->subtotal();

        return view('shop.presale.cart', compact('items', 'subtotal'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'presale_product_id' => ['required', 'integer', 'exists:presale_products,id'],
            'quantity'           => ['integer', 'min:1', 'max:50'],
        ]);

        try {
            $this->cart->add(
                (int) $validated['presale_product_id'],
                (int) ($validated['quantity'] ?? 1),
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('presale.cart.index')
            ->with('success', 'محصول به سبد پیش‌فروش اضافه شد.');
    }

    public function update(Request $request, int $presaleProductId)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:50'],
        ]);

        try {
            $this->cart->update($presaleProductId, (int) $validated['quantity']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('presale.cart.index')
            ->with('success', 'سبد به‌روزرسانی شد.');
    }

    public function destroy(int $presaleProductId)
    {
        $this->cart->remove($presaleProductId);

        return redirect()->route('presale.cart.index')
            ->with('success', 'محصول از سبد حذف شد.');
    }
}