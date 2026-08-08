<?php
// app/Services/C2CPaymentService.php
namespace App\Services;

use App\Models\C2CPayment;
use App\Models\Order;

class C2CPaymentService
{
    /**
     * تولید مبلغ دقیق ریالی و ثبت در دیتابیس
     */
    public function create(Order $order): C2CPayment
    {
        // اگر قبلاً ساخته شده، همان را برگردان
        if ($order->c2cPayment) {
            return $order->c2cPayment;
        }

        $toman = (int) $order->total;        // فرض: total به تومان است
        $rialBase = $toman * 10;             // تبدیل به ریال
        $randomSuffix = random_int(100, 9990); // یک عدد ۳ تا ۴ رقمی برای شناسایی
        
        // اطمینان از یکتا بودن (در صورت تصادم بسیار نادر)
        do {
            $exactRial = $rialBase + $randomSuffix;
            $exists = C2CPayment::where('exact_rial', $exactRial)
                ->where('status', 'pending')
                ->exists();
        } while ($exists);

        return C2CPayment::create([
            'order_id'     => $order->id,
            'exact_rial'   => $exactRial,
            'expires_at'   => now()->addMinutes(15),
            'status'       => 'pending',
        ]);
    }

    /**
     * تأیید از طریق اپ موبایل (بر اساس مبلغ واریزی)
     */
    public function verifyByAmount(int $amount): ?C2CPayment
    {
        $payment = C2CPayment::where('exact_rial', $amount)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->with('order')
            ->first();

        if ($payment) {
            $payment->update([
                'status'      => 'verified',
                'verified_at' => now(),
            ]);
            
            // آپدیت وضعیت سفارش اصلی
            $payment->order->update([
                'payment_status' => 'paid',
                'status'         => 'processing' // یا هر وضعیت دلخواه
            ]);
        }

        return $payment;
    }
}