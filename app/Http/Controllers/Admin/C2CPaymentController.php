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

        $payment->order->update([
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
    
        $payment->update([
            'status' => 'rejected',
            'admin_notes' => $request->input('reason'),
            'rejected_at' => now(),
        ]);
    
        $payment->order->update([
            'payment_status' => \App\Enums\PaymentStatus::Failed,
            'status' => \App\Enums\OrderStatus::Cancelled,
        ]);
    
        return back()->with('success', 'پرداخت رد و سفارش لغو شد.');
    }
}