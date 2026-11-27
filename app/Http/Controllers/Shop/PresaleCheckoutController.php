<?php

namespace App\Http\Controllers\Shop;

use App\Enums\PresaleStatus;
use App\Http\Controllers\Controller;
use App\Models\PresaleCampaign;
use App\Models\PresaleOrder;
use App\Models\PresaleOrderItem;
use App\Services\PresaleCartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresaleCheckoutController extends Controller
{
    public function __construct(
        private PresaleCartService $cart,
    ) {}

    public function index()
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('presale.cart.index')
                ->with('error', 'سبد پیش‌فروش شما خالی است.');
        }

        $items = $this->cart->items();
        $subtotal = $this->cart->subtotal();
        $addresses = auth()->user()->addresses()->latest()->get();
        $defaultAddress = auth()->user()->defaultAddress();

        return view('shop.presale.checkout', compact('items', 'subtotal', 'addresses', 'defaultAddress'));
    }

    public function store(Request $request)
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('presale.cart.index')
                ->with('error', 'سبد پیش‌فروش شما خالی است.');
        }

        $validated = $request->validate([
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
        ]);

        $address = auth()->user()->addresses()->findOrFail($validated['address_id']);
        $cartItems = $this->cart->items();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'سبد پیش‌فروش نامعتبر است.');
        }

        // همه آیتم‌ها باید از یک کمپین باشند
        $campaignId = $cartItems->first()['presale_product']->campaign_id;
        $campaign = PresaleCampaign::findOrFail($campaignId);

        if (! $campaign->isOpenNow()) {
            return back()->with('error', 'مهلت پیش‌فروش به پایان رسیده است.');
        }

        try {
            $order = DB::transaction(function () use ($address, $cartItems, $campaign) {
                $subtotal = $cartItems->sum(fn ($item) => $item['subtotal']);

                $order = PresaleOrder::create([
                    'user_id'          => auth()->id(),
                    'campaign_id'      => $campaign->id,
                    'order_number'     => PresaleOrder::generateOrderNumber(),
                    'status'           => PresaleStatus::AwaitingFinalPrice,
                    'subtotal'         => $subtotal,
                    'total_paid'       => 0,
                    'shipping_address' => [
    'name' => $address->recipient_name ?? $address->title ?? auth()->user()->name,
    'phone' => $address->phone ?? auth()->user()->phone,
    'address' => $address->full_address ?? $address->address,
    'postal_code' => $address->postal_code ?? null,
    'city' => $address->city ?? null,
    'province' => $address->province ?? null,
],
                ]);

                foreach ($cartItems as $item) {
                    $presaleProduct = $item['presale_product'];
                    $product = $presaleProduct->product;

                    PresaleOrderItem::create([
                        'presale_order_id'   => $order->id,
                        'presale_product_id' => $presaleProduct->id,
                        'product_id'         => $product?->id,
                        'product_name'       => $product?->name ?? 'محصول نامشخص',
                        'product_sku'        => $product?->sku,
                        'product_image'      => $product?->images->first()?->path,
                        'presale_price'      => $presaleProduct->presale_price,
                        'final_price'        => null, // هنوز مشخص نشده
                        'penalty'            => 0,
                        'quantity'           => $item['quantity'],
                    ]);
                }

                // لاگ ایجاد سفارش
                \App\Models\PresaleEvent::create([
                    'presale_order_id' => $order->id,
                    'actor_id'         => auth()->id(),
                    'action'           => 'created',
                    'payload'          => ['subtotal' => $subtotal],
                    'ip'               => request()->ip(),
                ]);

                return $order;
            }, 3);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->cart->clear();

        return redirect()->route('presale.payment', $order)
            ->with('success', 'سفارش پیش‌فروش ثبت شد. لطفاً پیش‌پرداخت را انجام دهید.');
    }

    public function payment(PresaleOrder $presaleOrder)
    {
        if ($presaleOrder->user_id !== auth()->id()) {
            abort(403);
        }

        if ($presaleOrder->status !== PresaleStatus::AwaitingFinalPrice) {
            return redirect()->route('user.presale-orders.show', $presaleOrder)
                ->with('error', 'این سفارش قابل پرداخت نیست.');
        }

        return view('shop.presale.payment', compact('presaleOrder'));
    }
}