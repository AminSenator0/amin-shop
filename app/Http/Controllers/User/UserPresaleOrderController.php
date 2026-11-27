<?php

namespace App\Http\Controllers\User;

use App\Enums\PresaleStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PresalePaymentService;
use Illuminate\Http\Request;

class UserPresaleOrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->where('order_type', 'presale')
            ->with(['items.product', 'presaleCampaign'])
            ->latest()
            ->paginate(10);

        return view('user.presale-orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id() || $order->order_type !== 'presale') {
            abort(403);
        }

        $order->load(['items.product', 'presaleCampaign', 'presalePayments']);

        $totalDue = ($order->presale_final_total ?? 0) + ($order->presale_penalties_total ?? 0);
        $baseRemaining = max(0, $totalDue - $order->subtotal);
        $installmentsPaid = max(0, ($order->presale_total_paid ?? 0) - $order->payable);
        $remaining = max(0, $baseRemaining - $installmentsPaid);
        $minInstallment = min(100000, $remaining);
        $deadlinePassed = $order->presale_deadline_at && now()->gt($order->presale_deadline_at);

        return view('user.presale-orders.show', compact('order', 'totalDue', 'remaining', 'minInstallment', 'deadlinePassed'));
    }

    public function pay(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id() || $order->order_type !== 'presale') {
            abort(403);
        }

        $totalDue = ($order->presale_final_total ?? 0) + ($order->presale_penalties_total ?? 0);
        $baseRemaining = max(0, $totalDue - $order->subtotal);
        $installmentsPaid = max(0, ($order->presale_total_paid ?? 0) - $order->payable);
        $remaining = max(0, $baseRemaining - $installmentsPaid);

        if ($remaining <= 0) {
            return back()->with('error', 'هیچ مبلغ باقی‌مانده‌ای برای پرداخت وجود ندارد.');
        }

        $minAmount = min(100000, $remaining);

        $validated = $request->validate([
            'amount'          => ['required', 'integer', 'min:' . $minAmount, 'max:' . $remaining],
            'payment_method'  => ['required', 'in:online,c2c,wallet'],
        ]);

        $service = app(PresalePaymentService::class);

        // کیف پول
        // کیف پول
        if ($validated['payment_method'] === 'wallet') {
            try {
                $service->payInstallmentWithWallet($order, $validated['amount']);
            } catch (\RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }
            return redirect()->route('user.presale-orders.show', $order)
                ->with('success', 'پرداخت قسط با موفقیت انجام شد.');
        }

        // زرین‌پال
        if ($validated['payment_method'] === 'online') {
            try {
                $result = $service->requestInstallmentZarinpal($order, $validated['amount']);
            } catch (\RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }
            return redirect()->away($result['redirect_url']);
        }

        // کارت به کارت
        if ($validated['payment_method'] === 'c2c') {
            \App\Models\PresalePayment::create([
                'order_id' => $order->id,
                'user_id'  => $order->user_id,
                'amount'   => $validated['amount'],
                'method'   => 'c2c',
                'type'     => 'installment',
                'status'   => 'pending',
            ]);
                      session()->put('presale_installment_amount', $validated['amount']);
return redirect()->route('c2c.show', $order);
        }

        return back()->with('error', 'روش پرداخت نامعتبر است.');
    }

        public function payDeposit(Order $order)
    {
        if ($order->user_id !== auth()->id() || $order->order_type !== 'presale') {
            abort(403);
        }

        if ($order->payment_status !== \App\Enums\PaymentStatus::Pending) {
            return back()->with('error', 'این سفارش قبلاً پرداخت شده است.');
        }

        if ($order->presale_payment_deadline_at && now()->gt($order->presale_payment_deadline_at)) {
            return back()->with('error', 'مهلت پرداخت بیعانه به پایان رسیده است.');
        }

        $service = app(PresalePaymentService::class);
        $method = $order->payment_method ?? 'online';

        // کیف پول
        // کیف پول
        if ($method === 'wallet') {
            try {
                $service->payWithWallet($order, $order->payable, 'presale');
            } catch (\RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }
            return redirect()->route('user.orders.show', $order)
                ->with('success', 'پرداخت بیعانه با موفقیت انجام شد.');
        }

        // زرین‌پال
        if ($method === 'online') {
            try {
                $result = $service->requestZarinpalDeposit($order, $order->payable);
            } catch (\RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }
            return redirect()->away($result['redirect_url']);
        }
        // کارت به کارت
        if ($method === 'c2c') {
            session()->put('presale_installment_amount', $order->payable);
            return redirect()->route('c2c.show', $order);
        }

        return back()->with('error', 'روش پرداخت نامعتبر است.');
    }
}