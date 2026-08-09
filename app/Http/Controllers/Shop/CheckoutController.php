<?php

namespace App\Http\Controllers\Shop;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\CheckoutRequest;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\OrderService;
use App\Services\ZarinpalService;
use App\Services\C2CPaymentService; // ← اضافه شده
use App\Support\StoreSettings;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CouponService $coupons,
        private ZarinpalService $zarinpal,
        private OrderService $orders,
        private C2CPaymentService $c2c, // ← اضافه شده
    ) {}

    public function index()
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'سبد خرید شما خالی است.');
        }

        $items = $this->cart->items();
        $subtotal = $this->cart->subtotal();
        $discount = $this->coupons->discount($subtotal);
        $appliedCoupon = $this->coupons->getApplied();
        $shippingMethods = ShippingMethod::where('is_active', true)->get();
        $addresses = auth()->user()->addresses()->latest()->get();
        $defaultAddress = auth()->user()->defaultAddress();
        $minOrderAmount = StoreSettings::int('min_order_amount');

        return view('shop.checkout', compact('items', 'subtotal', 'discount', 'appliedCoupon', 'shippingMethods', 'addresses', 'defaultAddress', 'minOrderAmount'));
    }

    public function store(CheckoutRequest $request)
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'سبد خرید شما خالی است.');
        }

        $validated = $request->validated();
        $minOrderAmount = StoreSettings::int('min_order_amount');
        $previewSubtotal = $this->cart->subtotal();

        if ($minOrderAmount > 0 && $previewSubtotal < $minOrderAmount) {
            return back()->with('error', 'حداقل مبلغ سفارش '.format_price($minOrderAmount).' است.');
        }

        $address = auth()->user()->addresses()->findOrFail($validated['address_id']);
        $cartItems = $this->cart->items();
        $couponCode = $this->coupons->getApplied()?->code;

        try {
            $order = DB::transaction(function () use ($validated, $address, $cartItems, $couponCode, $minOrderAmount) {
                $shippingMethod = ShippingMethod::query()
                    ->whereKey($validated['shipping_method_id'])
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->firstOrFail();

                $subtotal = 0;
                $resolvedLines = [];

                foreach ($cartItems as $item) {
                    $product = Product::query()
                        ->whereKey($item['product']->id)
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->first();

                    if (! $product || ! $product->hasEnoughStock($item['quantity'], $item['size'] ?? null, $item['color'] ?? null)) {
                        throw new \RuntimeException("موجودی {$item['product']->name} کافی نیست.");
                    }

                    $lineTotal = $product->price * $item['quantity'];
                    $subtotal += $lineTotal;

                    $resolvedLines[] = [
                        'product' => $product,
                        'quantity' => $item['quantity'],
                        'size' => $item['size'] ?? null,
                        'color' => $item['color'] ?? null,
                        'custom_fields' => $item['custom_fields'] ?? [],
                        'line_total' => $lineTotal,
                    ];
                }

                if ($resolvedLines === []) {
                    throw new \RuntimeException('سبد خرید شما خالی است.');
                }

                if ($minOrderAmount > 0 && $subtotal < $minOrderAmount) {
                    throw new \RuntimeException('حداقل مبلغ سفارش '.format_price($minOrderAmount).' است.');
                }

                $coupon = null;
                $discount = 0;

                if ($couponCode) {
                    $coupon = Coupon::query()
                        ->where('code', $couponCode)
                        ->lockForUpdate()
                        ->first();

                    if ($coupon && $coupon->isValid($subtotal)) {
                        $discount = $coupon->calculateDiscount($subtotal);
                    } else {
                        $coupon = null;
                        $discount = 0;
                    }
                }

                $discount = min($discount, $subtotal);
                $shippingCost = $shippingMethod->calculateCost($subtotal - $discount);
                $total = max(0, $subtotal - $discount) + $shippingCost;
               // $total = $cart->items->sum(fn($item) => $item->price * $item->quantity);

                // ⬇️⬇️⬇️ تخفیف کارت به کارت ⬇️⬇️⬇️
                $c2cDiscount = 0;
                $payable = $total;
                
                if ($validated['payment_method'] === 'c2c') {
                    $discountPercent = (float) (\App\Support\StoreSettings::get('c2c_discount_percent') ?? 1);
                    $c2cDiscount = round($total * $discountPercent / 100);
                    $payable = $total - $c2cDiscount;
                }

                $order = Order::create([
                    'user_id' => auth()->id(),
                    'order_number' => Order::generateOrderNumber(),
                    'status' => OrderStatus::Pending,
                    'payment_status' => PaymentStatus::Pending,
                    'payment_method' => $validated['payment_method'], // ← اضافه شده
                    'shipping_method_id' => $shippingMethod->id,
                    'coupon_id' => $coupon?->id,
                    'coupon_code' => $coupon?->code,
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shippingCost,
                    'discount_amount' => $discount,
                    'total' => $total,
                    'c2c_discount' => $c2cDiscount,        // ⬅️ اضافه شد
                    'payable' => $payable,                 // ⬅️ اضافه شد
                    'shipping_address' => $address->toSnapshot(),
                    'notes' => $validated['notes'] ?? null,
                ]);

                if ($coupon) {
                    $coupon->increment('used_count');
                }

                foreach ($resolvedLines as $line) {
                    $product = $line['product'];
                    $product->decrementVariantStock($line['size'] ?? null, $line['color'] ?? null, $line['quantity']);

                    $options = array_filter([
                        'size' => $line['size'],
                        'color' => $line['color'],
                    ]);

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'options' => $options !== [] ? $options : null,
                        'custom_fields' => $line['custom_fields'] !== [] ? $line['custom_fields'] : null,
                        'price' => $product->price,
                        'quantity' => $line['quantity'],
                        'total' => $line['line_total'],
                    ]);
                }

                $itemsSum = (int) $order->items()->sum('total');
                if ($itemsSum !== $subtotal) {
                    throw new \RuntimeException('مغایرت در محاسبه مبلغ سفارش.');
                }

                $this->orders->logActivity($order, 'created', 'سفارش توسط مشتری ثبت شد.');

                return $order;
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->cart->clear();
        $this->coupons->remove();

        $this->orders->sendConfirmationEmail($order);

        // ← تغییر: ریدایرکت بر اساس روش پرداخت
        if ($order->payment_method === 'c2c') {
            return redirect()->route('c2c.show', $order)->with('success', 'سفارش ثبت شد. لطفاً پرداخت کارت به کارت را انجام دهید.');
        }

        return redirect()->route('checkout.payment', $order)->with('success', 'سفارش ثبت شد. لطفاً پرداخت را انجام دهید.');
    }

    public function payment(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->payment_status === PaymentStatus::Paid) {
            return redirect()->route('user.orders.show', $order);
        }

        // ← اضافه: اگر روش کارت به کارت بود، به صفحه C2C هدایت کن
        if ($order->payment_method === 'c2c') {
            return redirect()->route('c2c.show', $order);
        }

        if (! $order->canBePaidOnline()) {
            return redirect()
                ->route('user.orders.show', $order)
                ->with('error', 'این سفارش قابل پرداخت نیست.');
        }

        if (! $this->zarinpal->isConfigured()) {
            return redirect()
                ->route('user.orders.show', $order)
                ->with('error', 'درگاه زرین‌پال پیکربندی نشده است.');
        }

        try {
            if ($order->payment_status === PaymentStatus::Failed) {
                $this->orders->reserveStockForPaymentRetry($order);
                $order->refresh();
            }

            $payment = $this->zarinpal->requestPayment($order);
            $order->update(['payment_authority' => $payment['authority']]);

            return redirect()->away($payment['redirect_url']);
        } catch (\RuntimeException $e) {
            $this->orders->markPaymentFailed($order->fresh(), $e->getMessage());
            $this->cart->restoreFromOrder($order->fresh());

            if ($order->coupon_code) {
                try {
                    $this->coupons->remember($order->coupon_code);
                } catch (\RuntimeException) {
                    // ignore
                }
            }

            return redirect()
                ->route('user.orders.show', $order)
                ->with('error', $e->getMessage());
        }
    }

    public function processPayment(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        return redirect()->route('checkout.payment', $order);
    }
}