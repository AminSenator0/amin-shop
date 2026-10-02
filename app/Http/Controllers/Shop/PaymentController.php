<?php

namespace App\Http\Controllers\Shop;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\OrderService;
use App\Services\ZarinpalService;
use App\Support\ShoppingFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{


    public function __construct(
        private ZarinpalService $zarinpal,
        private OrderService $orders,
        private CartService $cart,
        private CouponService $coupons,
    ) {}

    public function callback(Request $request)
    {
        $authority = trim((string) $request->query('Authority', ''));
        $status = (string) $request->query('Status', '');

        if ($authority === '' || $status !== 'OK') {
            if ($authority !== '') {
                $this->handleUnsuccessfulPayment($authority, 'پرداخت لغو شد یا ناموفق بود.');
            }

            return $this->paymentErrorRedirect('پرداخت لغو شد یا ناموفق بود.');
        }

        if (! $this->zarinpal->isConfigured()) {
            return redirect()->route('home')->with('error', 'درگاه پرداخت پیکربندی نشده است.');
        }

        $alreadyPaid = false;

        try {
            $order = DB::transaction(function () use ($authority, &$alreadyPaid) {
                $order = Order::query()
                    ->where('payment_authority', $authority)
                    ->lockForUpdate()
                    ->first();

                if (! $order) {
                    return null;
                }

                if ($order->payment_status === PaymentStatus::Paid) {
                    $alreadyPaid = true;

                    return $order;
                }

                if ($order->status === OrderStatus::Cancelled) {
                    throw new \RuntimeException('این سفارش لغو شده و قابل تایید پرداخت نیست.');
                }

                if (! $order->canBePaidOnline()) {
                    throw new \RuntimeException('وضعیت این سفارش برای تایید پرداخت معتبر نیست.');
                }

                // مبلغ فقط از دیتابیس خوانده می‌شود — پارامترهای callback قابل اعتماد نیستند
                $result = $this->zarinpal->verifyPayment($authority, (int) $order->gatewayPayable());
                $order->update([
                    'payment_status' => PaymentStatus::Paid,
                    'status' => OrderStatus::Paid,
                    'payment_ref' => (string) ($result['ref_id'] ?? ''),
                    'paid_at' => now(),
                ]);

                return $order->fresh();
            });
        } catch (\RuntimeException $e) {
            if ($authority !== '') {
                $this->handleUnsuccessfulPayment($authority, $e->getMessage());
            }

            $order = Order::where('payment_authority', $authority)->first();

            if ($order && auth()->check() && (auth()->id() === $order->user_id || auth()->user()->isAdmin())) {
                return redirect()->route('user.orders.show', $order)->with('error', $e->getMessage());
            }

            return redirect()->route('home')->with('error', $e->getMessage());
        }

        if (! $order) {
            return redirect()->route('home')->with('error', 'سفارش یافت نشد.');
        }

        if (! $alreadyPaid) {
            $this->cart->clear();
            $this->coupons->remove();
            ShoppingFlow::deactivate();
        }

        $message = $alreadyPaid
            ? 'این سفارش قبلاً پرداخت شده است.'
            : 'پرداخت با موفقیت انجام شد.';

        if (! $alreadyPaid && $order->payment_ref) {
            $message .= ' کد پیگیری: '.$order->payment_ref;
        }

        if (auth()->check() && (auth()->id() === $order->user_id || auth()->user()?->isAdmin())) {
            return redirect()->route('user.orders.show', $order)->with('success', $message);
        }

        return redirect()->route('login')->with('success', $message.' شماره سفارش: '.$order->order_number);
    }

    private function handleUnsuccessfulPayment(string $authority, string $reason): void
    {
        $order = Order::query()
            ->where('payment_authority', $authority)
            ->first();

        if (! $order) {
            return;
        }

        $this->orders->markPaymentFailed($order, $reason);
        $this->cart->restoreFromOrder($order->fresh());

        if ($order->coupon_code) {
            try {
                $this->coupons->remember($order->coupon_code);
            } catch (\RuntimeException) {
                // کوپن منقضی شده باشد — نادیده گرفته می‌شود
            }
        }
    }

    private function paymentErrorRedirect(string $message)
    {
        if (auth()->check()) {
            return redirect()->route('user.orders.index')->with('error', $message);
        }

        return redirect()->route('login')->with('error', $message);
    }
}
