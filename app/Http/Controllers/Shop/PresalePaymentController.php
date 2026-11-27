<?php

namespace App\Http\Controllers\Shop;

use App\Enums\PresaleStatus;
use App\Http\Controllers\Controller;
use App\Models\PresaleOrder;
use App\Models\PresalePayment;
use App\Services\PresaleCartService;
use App\Services\PresalePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresalePaymentController extends Controller
{
    public function __construct(
        private PresalePaymentService $payments,
        private PresaleCartService $cart,
    ) {}

    // ─── پیش‌پرداخت اول ───────────────────────────

    public function payZarinpal(PresaleOrder $presaleOrder)
    {
        if ($presaleOrder->user_id !== auth()->id()) {
            abort(403);
        }

        if ($presaleOrder->status !== PresaleStatus::AwaitingFinalPrice) {
            return back()->with('error', 'این سفارش قابل پرداخت پیش‌پرداخت نیست.');
        }

        try {
            $result = $this->payments->requestZarinpal($presaleOrder);

            return redirect()->away($result['redirect_url']);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function payC2c(PresaleOrder $presaleOrder)
    {
        if ($presaleOrder->user_id !== auth()->id()) {
            abort(403);
        }

        if ($presaleOrder->status !== PresaleStatus::AwaitingFinalPrice) {
            return back()->with('error', 'این سفارش قابل پرداخت پیش‌پرداخت نیست.');
        }

        try {
            $payment = $this->payments->createC2c($presaleOrder);

            return view('shop.presale.c2c', compact('presaleOrder', 'payment'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ─── قسط تسویه ───────────────────────────────

    public function payInstallment(Request $request, PresaleOrder $presaleOrder)
    {
        if ($presaleOrder->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $presaleOrder->hasFinalPrice()) {
            return back()->with('error', 'قیمت نهایی هنوز مشخص نشده است.');
        }

        if (! $presaleOrder->status->isOpen()) {
            return back()->with('error', 'این سفارش بسته شده است.');
        }

        $validated = $request->validate([
            'amount'         => ['required', 'integer', 'min:100000'],
            'payment_method' => ['required', 'in:zarinpal,c2c,wallet'],
        ]);

        $amount = (int) $validated['amount'];

        // اگر کیف پول انتخاب شد
        if ($validated['payment_method'] === 'wallet') {
            try {
                $this->payments->payWithWallet($presaleOrder, $amount);

                return redirect()->route('user.presale-orders.show', $presaleOrder)
                    ->with('success', 'پرداخت از کیف پول انجام شد.');
            } catch (\RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        // زرین‌پال
        if ($validated['payment_method'] === 'zarinpal') {
            try {
                $result = $this->payments->requestInstallmentZarinpal($presaleOrder, $amount);

                return redirect()->away($result['redirect_url']);
            } catch (\RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        // کارت‌به‌کارت
        try {
            $payment = $this->payments->createInstallmentC2c($presaleOrder, $amount);

            return view('shop.presale.c2c', compact('presaleOrder', 'payment'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ─── Callback زرین‌پال ─────────────────────────

    public function callback(Request $request)
    {
        $authority = trim((string) $request->query('Authority', ''));
        $status = (string) $request->query('Status', '');

        if ($authority === '' || $status !== 'OK') {
            if ($authority !== '') {
                $this->handleFailed($authority, 'پرداخت لغو شد یا ناموفق بود.');
            }
            return redirect()->route('user.presale-orders.index')
                ->with('error', 'پرداخت لغو شد یا ناموفق بود.');
        }

        try {
            $order = $this->payments->verifyZarinpal($authority);
        } catch (\RuntimeException $e) {
            $this->handleFailed($authority, $e->getMessage());

            $payment = PresalePayment::where('authority', $authority)->first();
            $order = $payment?->order;

            if ($order && auth()->id() === $order->user_id) {
                return redirect()->route('user.presale-orders.show', $order)
                    ->with('error', $e->getMessage());
            }

            return redirect()->route('user.presale-orders.index')
                ->with('error', $e->getMessage());
        }

        if (! $order) {
            return redirect()->route('home')->with('error', 'سفارش یافت نشد.');
        }

        // اگر سفارش تکمیل شد → تایید نهایی
        if ($order->status === PresaleStatus::Completed) {
            $this->cart->clear();
            return redirect()->route('user.presale-orders.show', $order)
                ->with('success', 'تسویه کامل انجام شد. سفارش شما تکمیل و تایید نهایی شد.');
        }

        return redirect()->route('user.presale-orders.show', $order)
            ->with('success', 'پرداخت با موفقیت انجام شد.');
    }

    // ─── کارت‌به‌کارت: آپلود رسید ──────────────────

    public function uploadReceipt(Request $request, PresalePayment $presalePayment)
    {
        $order = $presalePayment->order;

        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($presalePayment->status !== PresalePayment::STATUS_PENDING) {
            return back()->with('error', 'این پرداخت قبلاً ثبت شده است.');
        }

        $validated = $request->validate([
            'receipt_image' => ['required', 'image', 'max:5120'], // 5MB
        ]);

        $path = $request->file('receipt_image')->store('presale-receipts', 'private');

        $presalePayment->update([
            'receipt_path' => $path,
            'status'       => 'pending_review', // منتظر تایید ادمین
        ]);

        return back()->with('success', 'رسید آپلود شد. پس از بررسی ادمین تأیید می‌شود.');
    }

    // ─── کمکی ─────────────────────────────────────

    private function handleFailed(string $authority, string $reason): void
    {
        $payment = PresalePayment::where('authority', $authority)->first();

        if (! $payment) {
            return;
        }

        $payment->update([
            'status'      => PresalePayment::STATUS_FAILED,
            'fail_reason' => $reason,
        ]);
    }
}