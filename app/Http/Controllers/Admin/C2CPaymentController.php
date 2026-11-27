<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\C2CPayment;
use Illuminate\Http\Request;

class C2CPaymentController extends Controller
{
    public function index()
    {
        $payments = C2CPayment::with('order.user')
            ->latest()
            ->paginate(20);
    
        // آمار از کل جدول، نه از صفحهٔ فعلی
        $pendingCount   = C2CPayment::where('status', 'pending')->count();
        $receiptCount   = C2CPayment::where('status', 'receipt_uploaded')->count();
        $verifiedCount  = C2CPayment::where('status', 'verified')->count();
        $expiredCount   = C2CPayment::where('status', 'expired')->count();
        $rejectedCount  = C2CPayment::where('status', 'rejected')->count(); // اگه اضافه کردی
    
        return view('admin.c2c.index', compact(
            'payments',
            'pendingCount',
            'receiptCount',
            'verifiedCount',
            'expiredCount',
            'rejectedCount'
        ));
    }

    public function verify(Request $request, C2CPayment $payment)
    {
        if (! in_array($payment->status, ['pending', 'receipt_uploaded'])) {
            return back()->with('error', 'این پرداخت قابل تأیید نیست.');
        }

        $payment->update([
            'status' => 'verified',
            'verified_at' => now(),
        ]);

        $order = $payment->order;

        // ─── پیش‌فروش ───
        if ($order->order_type === 'presale') {
            // پیدا کردن PresalePayment pending مربوطه
            $pp = \App\Models\PresalePayment::where('order_id', $order->id)
                ->where('method', 'c2c')
                ->where('status', 'pending')
                ->latest()
                ->first();

            if ($pp) {
                $paymentAmount = $pp->amount; // مبلغ درست = 200000 نه exact_rial/10
                $pp->update([
                    'status' => 'verified',
                    'verified_at' => now(),
                ]);
            } else {
                // Fallback: اگه پیدا نشد
                $paymentAmount = (int) floor($payment->exact_rial / 10);
                \App\Models\PresalePayment::create([
                    'order_id'    => $order->id,
                    'user_id'     => $order->user_id,
                    'amount'      => $paymentAmount,
                    'method'      => 'c2c',
                    'type'        => 'installment',
                    'status'      => 'verified',
                    'verified_at' => now(),
                ]);
            }

            $order->increment('presale_total_paid', $paymentAmount);

            $totalDue = ($order->presale_final_total ?? 0) + ($order->presale_penalties_total ?? 0);
            if ($totalDue > 0 && $order->presale_total_paid >= $totalDue) {
                $order->update([
                    'presale_status' => \App\Enums\PresaleStatus::Completed->value,
                    'payment_status' => \App\Enums\PaymentStatus::Paid,
                    'paid_at'        => now(),
                ]);
            } else {
                $order->update([
                    'presale_status' => \App\Enums\PresaleStatus::PartiallyPaid->value,
                    'payment_status' => \App\Enums\PaymentStatus::PartiallyPaid,
                ]);
            }

            return back()->with('success', 'پرداخت کارت به کارت پیش‌فروش تأیید شد.');
        }
        // ─── پایان پیش‌فروش ───

        $order->update([
            'payment_status' => \App\Enums\PaymentStatus::Paid,
            'status' => \App\Enums\OrderStatus::Paid,
            'paid_at' => now(),
        ]);

        return back()->with('success', 'پرداخت کارت به کارت تأیید شد.');
    }

    public function reject(Request $request, C2CPayment $payment)
    {
        if (! in_array($payment->status, ['pending', 'receipt_uploaded'])) {
            return back()->with('error', 'این پرداخت قابل رد نیست.');
        }
    
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $order = $payment->order;

        $payment->update([
            'status' => 'rejected',
            'admin_notes' => $request->input('reason'),
            'rejected_at' => now(),
        ]);

        // ─── پیش‌فروش ───
        if ($order->order_type === 'presale') {
            $pp = \App\Models\PresalePayment::where('order_id', $order->id)
                ->where('method', 'c2c')
                ->where('status', 'pending')
                ->latest()
                ->first();

            if ($pp) {
                $paymentAmount = $pp->amount;
                $pp->update(['status' => 'rejected']);
                
                // کم کردن با محافظت از منفی شدن
                $currentPaid = $order->presale_total_paid ?? 0;
                $order->update(['presale_total_paid' => max(0, $currentPaid - $paymentAmount)]);
            }

            // اگه هیچ پرداخت تأییدشده‌ای نمونده
            $verifiedCount = \App\Models\PresalePayment::where('order_id', $order->id)
                ->where('status', 'verified')
                ->count();
            
            if ($verifiedCount === 0) {
                $order->update([
                    'presale_status' => \App\Enums\PresaleStatus::AwaitingPayment->value,
                    'payment_status' => \App\Enums\PaymentStatus::Pending,
                ]);
            }

            return back()->with('success', 'رسید رد شد.');
        }
        // ─── پایان پیش‌فروش ───

        $order->update([
            'payment_status' => \App\Enums\PaymentStatus::Failed,
            'status' => \App\Enums\OrderStatus::Cancelled,
        ]);

        app(\App\Services\OrderService::class)->refundWalletPart($order);

        return back()->with('success', 'پرداخت رد و سفارش لغو شد.');
    }
}