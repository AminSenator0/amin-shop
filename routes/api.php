<?php

use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;
use App\Models\C2CCheck;
use App\Models\C2CPayment;
use Illuminate\Http\Request;
use App\Enums\PaymentStatus;
use App\Enums\OrderStatus;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ========== Route عمومی (تست) ==========
Route::get('/ping', function() {
    return response()->json(['pong' => true]);
});

// ========== API گوشی فروشنده (محافظت‌شده با API Key + Rate Limit) ==========
Route::middleware(['seller.auth', 'throttle:seller-poll'])->group(function () {

    // گوشی می‌پرسه: کار جدید هست؟
    Route::get('/seller/pending-check', function (Request $request) {
        $check = C2CCheck::where('status', 'pending')
            ->with('c2cPayment.order')
            ->oldest()
            ->first();

        if (! $check) {
            return response()->json(['has_job' => false]);
        }

        $check->update(['status' => 'checking']);

        return response()->json([
            'has_job' => true,
            'check_id' => $check->id,
            'amount' => $check->amount,
            'tracking_code' => $check->tracking_code,
            'check_from' => $check->check_from?->toIso8601String(),
        ]);
    });

    // گوشی نتیجه رو برمی‌گردونه
    Route::post('/seller/check-result/{check}', function (Request $request, C2CCheck $check) {
        // فقط چک‌های checking رو قبول کن
        if ($check->status !== 'checking') {
            return response()->json(['message' => 'Check already resolved'], 409);
        }

        $request->validate([
            'found' => 'required|boolean',
            'sms_body' => 'nullable|string|max:2000',
            'sms_sender' => 'nullable|string|max:50',
            'sms_date' => 'nullable|string',
        ]);

        $check->update([
            'status' => $request->boolean('found') ? 'found' : 'not_found',
            'result' => json_encode($request->only('found', 'sms_body', 'sms_sender', 'sms_date')),
            'resolved_at' => now(),
        ]);

        if ($request->boolean('found')) {
            $payment = $check->c2cPayment;

            \DB::transaction(function () use ($payment, $check) {
                $payment->update([
                    'status' => 'verified',
                    'verified_at' => now(),
                ]);

                $payment->order->update([
                    'payment_status' => PaymentStatus::Paid,
                    'status' => OrderStatus::Paid,
                    'paid_at' => now(),
                ]);
            });
        }

        return response()->json(['message' => 'ok']);
    });
});

// ========== API سایت (کاربران لاگین‌شده با Sanctum یا Session) ==========
Route::middleware('auth:sanctum')->group(function () {

    // درخواست بررسی تراکنش
    Route::post('/c2c/request-check/{payment}', function (Request $request, C2CPayment $payment) {
        // فقط صاحب سفارش می‌تونه درخواست بده
        if ($payment->order->user_id !== $request->user()->id) {
            abort(403, 'This order does not belong to you.');
        }

        // اگه قبلاً یه چک pending یا checking داره
        $existing = C2CCheck::where('c2c_payment_id', $payment->id)
            ->whereIn('status', ['pending', 'checking'])
            ->first();

        if ($existing) {
            return response()->json([
                'check_id' => $existing->id,
                'status' => $existing->status,
            ]);
        }

        // اگه پرداخت قبلاً تایید/رد شده
        if (in_array($payment->status, ['verified', 'rejected'])) {
            return response()->json(['message' => 'Payment already processed'], 422);
        }

        $check = C2CCheck::create([
            'c2c_payment_id' => $payment->id,
            'amount' => $payment->exact_rial,
            'tracking_code' => $payment->tracking_code ?? $payment->order->order_number,
            'status' => 'pending',
            'check_from' => now()->subMinutes(3),
        ]);

        return response()->json([
            'check_id' => $check->id,
            'status' => 'pending',
        ]);
    });

    // دریافت وضعیت بررسی
    Route::get('/c2c/check-status/{check}', function (Request $request, C2CCheck $check) {
        // فقط صاحب سفارش می‌تونه وضعیت رو ببینه
        if ($check->c2cPayment->order->user_id !== $request->user()->id) {
            abort(403);
        }

        return response()->json([
            'status' => $check->status,
            'found' => $check->status === 'found',
            'result' => $check->result ? json_decode($check->result) : null,
            'resolved_at' => $check->resolved_at,
        ]);
    });

    // ====== Routeهای قبلی v1 ======
    Route::prefix('v1')->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('api.orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('api.orders.show');
    });
});