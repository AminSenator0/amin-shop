<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\C2CCheck;
use App\Models\C2CPayment;
use App\Models\Order;
use Illuminate\Http\Request;

class C2CPaymentController extends Controller
{
    /**
     * نمایش صفحه پرداخت کارت به کارت
     */
    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }
    
        $payment = $order->c2cPayment;
    
        if (! $payment) {
            $payment = C2CPayment::create([
                'order_id' => $order->id,
                'exact_rial' => $order->total * 10,
                'tracking_code' => $order->order_number,
                'status' => 'pending',
                'expires_at' => now()->addMinutes(15),
            ]);
            $order->setRelation('c2cPayment', $payment);
        }
    
        $latestCheck = $payment->checks()->latest()->first();
    
        return view('shop.c2c.show', compact('order', 'payment', 'latestCheck'));
    }

    /**
     * درخواست بررسی تراکنش (API)
     */
    public function requestCheck(Request $request, C2CPayment $payment)
    {
        // فقط صاحب سفارش
        if ($payment->order->user_id !== $request->user()->id) {
            abort(403, 'This order does not belong to you.');
        }

        // آیا قبلاً درخواست فعالی وجود دارد؟
        $existing = C2CCheck::where('c2c_payment_id', $payment->id)
            ->whereIn('status', ['pending', 'checking'])
            ->first();

        if ($existing) {
            return response()->json([
                'check_id' => $existing->id,
                'status' => $existing->status,
            ]);
        }

        // آیا پرداخت قبلاً تأیید یا رد شده است؟
        if (in_array($payment->status, ['verified', 'rejected'])) {
            return response()->json([
                'message' => 'Payment already processed'
            ], 422);
        }

        // ایجاد درخواست بررسی جدید
        $check = C2CCheck::create([
            'c2c_payment_id' => $payment->id,
            'amount' => $payment->exact_rial,
            'tracking_code' => $payment->tracking_code ?? $payment->order->order_number,
            'status' => 'pending',
            'check_from' => now()->subMinutes(3), // ✅ تغییر به ۳ دقیقه
        ]);

        return response()->json([
            'check_id' => $check->id,
            'status' => 'pending',
        ]);
    }

    /**
     * دریافت وضعیت بررسی (API)
     */
    public function checkStatus(Request $request, C2CCheck $check)
    {
        // فقط صاحب سفارش
        if ($check->c2cPayment->order->user_id !== $request->user()->id) {
            abort(403);
        }

        return response()->json([
            'status' => $check->status,
            'found' => $check->status === 'found',
            'result' => $check->result ? json_decode($check->result) : null,
            'resolved_at' => $check->resolved_at,
        ]);
    }
}