<?php
// app/Http/Controllers/Payment/C2CPaymentController.php
namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\C2CPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class C2CPaymentController extends Controller
{
    public function __construct(
        private C2CPaymentService $service
    ) {}

    /**
     * نمایش صفحهٔ پرداخت کارت به کارت
     */
    public function show(Order $order)
    {
        // اطمینان از اینکه سفارش متعلق به کاربر جاری است
         if ($order->user_id !== auth()->id()) { abort(403); }

        $c2c = $this->service->create($order);

        // اگر منقضی شده باشد
        if ($c2c->isExpired() && $c2c->status === 'pending') {
            $c2c->update(['status' => 'expired']);
        }

        return view('payments.c2c', compact('order', 'c2c'));
    }

    /**
     * بررسی وضعیت پرداخت (برای AJAX هر ۵ ثانیه)
     */
    public function checkStatus(Order $order): JsonResponse
    {
        $c2c = $order->c2cPayment;

        if (!$c2c) {
            return response()->json(['verified' => false]);
        }

        if ($c2c->status === 'verified') {
            return response()->json([
                'verified'     => true,
                'redirect_url' => route('user.orders.show', $order)
            ]);
        }

        if ($c2c->isExpired() && $c2c->status === 'pending') {
            $c2c->update(['status' => 'expired']);
            return response()->json(['verified' => false, 'expired' => true]);
        }

        return response()->json([
            'verified' => false,
            'expired'  => false,
            'remaining_seconds' => now()->diffInSeconds($c2c->expires_at, false)
        ]);
    }

    /**
     * آپلود رسید دستی توسط کاربر
     */
    public function uploadReceipt(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'receipt' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $c2c = $order->c2cPayment;

        if (!$c2c || !in_array($c2c->status, ['pending', 'expired'])) {
            return response()->json([
                'success' => false,
                'message' => 'وضعیت سفارش نامعتبر است.'
            ], 400);
        }

        $path = $request->file('receipt')->store('receipts', 'public');

        $c2c->update([
            'receipt_path' => $path,
            'status'       => 'receipt_uploaded'
        ]);

        return response()->json(['success' => true]);
    }
}