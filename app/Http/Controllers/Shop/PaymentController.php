<?php

namespace App\Http\Controllers\Shop;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PresaleStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PresalePayment;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PresalePaymentService;
use App\Services\ZarinpalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct(
        private ZarinpalService $zarinpal,
        private OrderService $orders,
        private CartService $cart,
    ) {}

    /**
     * مدیریت Callback زرین‌پال (هم عادی و هم پیش‌فروش).
     */
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

        // ─── بررسی پیش‌فروش اول ─────────────────────────────
        $presalePayment = PresalePayment::where('authority', $authority)->lockForUpdate()->first();

        if ($presalePayment) {
            return $this->handlePresaleCallback($presalePayment);
        }
        // ─── پایان بررسی پیش‌فروش ─────────────────────────────

        // ─── خرید عادی (کد فعلی بدون تغییر) ───────────────────
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

               $verifyResult = $this->zarinpal->verifyPayment($order->payment_authority, $order->total);

                $order->update([
                    'payment_status' => PaymentStatus::Paid,
                    'payment_ref' => $verifyResult['ref_id'],
                    'paid_at' => now(),
                    'status' => OrderStatus::Paid,
                ]);

                //$this->orders->completeOrderAfterPayment($order);

                return $order;
            });
        } catch (\RuntimeException $e) {
            return $this->paymentErrorRedirect($e->getMessage());
        }

        if ($order === null) {
            return $this->paymentErrorRedirect('سفارش مربوط به این پرداخت یافت نشد.');
        }

        if ($alreadyPaid) {
            return redirect()
                ->route('user.orders.show', $order)
                ->with('info', 'این پرداخت قبلاً تایید شده است.');
        }

        return redirect()
            ->route('user.orders.show', $order)
            ->with('success', 'پرداخت با موفقیت انجام شد. از خرید شما سپاسگزاریم.');
    }

    /**
     * مدیریت Callback پیش‌فروش (پرداخت بیعانه/قسط).
     */
    private function handlePresaleCallback(PresalePayment $presalePayment)
    {
        $order = \App\Models\Order::find($presalePayment->order_id);

        if (! $order) {
            return redirect()->route('home')->with('error', 'سفارش یافت نشد.');
        }

        // اگه payment_authority روی Order ذخیره نشده، از PresalePayment بگیر
        if (empty($order->payment_authority) || strlen((string) $order->payment_authority) !== 36) {
            $order->forceFill(['payment_authority' => $presalePayment->authority])->save();
        }

        $alreadyVerified = false;

        try {
            DB::transaction(function () use ($presalePayment, $order, &$alreadyVerified) {
                if ($presalePayment->status === 'verified') {
                    $alreadyVerified = true;

                    return;
                }

                                \Illuminate\Support\Facades\Log::info('Before verifyPayment', [
                    'order_id' => $order->id,
                    'order_payment_authority' => $order->payment_authority,
                    'order_payment_authority_len' => strlen((string) $order->payment_authority),
                    'presale_authority' => $presalePayment->authority,
                    'presale_authority_len' => strlen((string) $presalePayment->authority),
                ]);

                $verifyResult = $this->zarinpal->verifyPayment($order->payment_authority, $presalePayment->amount);
                $presalePayment->update([
                    'status' => 'verified',
                    'ref_id' => $verifyResult['ref_id'],
                    'verified_at' => now(),
                ]);

                $order->increment('presale_total_paid', $presalePayment->amount);

                $totalDue = ($order->presale_final_total ?? 0) + ($order->presale_penalties_total ?? 0);
                $paid = $order->presale_total_paid;

                if ($totalDue > 0 && $paid >= $totalDue) {
                    $order->update([
                        'presale_status' => PresaleStatus::Completed->value,
                        'presale_completed_at' => now(),
                        'status' => OrderStatus::Paid,
                        'payment_status' => PaymentStatus::Paid,
                        'paid_at' => now(),
                    ]);
                } elseif ($paid > 0) {
                    $order->update([
                        'presale_status' => PresaleStatus::PartiallyPaid->value,
                        'payment_status' => PaymentStatus::PartiallyPaid,
                    ]);
                }
            });
        } catch (\RuntimeException $e) {
            return $this->paymentErrorRedirect($e->getMessage());
        }

        if ($alreadyVerified) {
            return redirect()
                ->route('user.orders.show', $order)
                ->with('info', 'این پرداخت قبلاً تایید شده است.');
        }

        $message = $presalePayment->type === 'presale'
            ? 'پرداخت بیعانه با موفقیت انجام شد.'
            : 'پرداخت قسط با موفقیت انجام شد.';

        return redirect()
            ->route('user.orders.show', $order)
            ->with('success', $message);
    }

    /**
     * پرداخت ناموفق یا لغو شده را ثبت می‌کند.
     */
    private function handleUnsuccessfulPayment(string $authority, string $reason): void
    {
        $order = Order::where('payment_authority', $authority)->first();

        if ($order && $order->payment_status !== PaymentStatus::Paid) {
            $this->orders->markPaymentFailed($order, $reason);
        }
    }

    /**
     * ریدایرکت به صفحه خطا با پیام.
     */
    private function paymentErrorRedirect(string $message)
    {
        return redirect()->route('home')->with('error', $message);
    }
}