<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\PresaleStatus;
use App\Enums\WalletReferenceType;
use App\Models\Order;
use App\Models\PresalePayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PresalePaymentService
{
    public function __construct(
        private ZarinpalService $zarinpal,
        private WalletService $wallet,
    ) {}

    /**
     * درخواست پرداخت بیعانه از طریق زرین‌پال.
     */
    public function requestZarinpalDeposit(Order $order): array
    {
        $amount = $this->getDepositAmount($order);

        return DB::transaction(function () use ($order, $amount) {
            $payment = $this->createPaymentRecord($order, $amount, 'zarinpal', 'presale');

            $result = $this->zarinpal->requestPayment($order, $amount);

            $payment->update(['authority' => $result['authority']]);
                        $order->forceFill(['payment_authority' => $result['authority']])->save();
            return $result;
        });
    }

    /**
     * درخواست پرداخت قسط از طریق زرین‌پال.
     */
    public function requestInstallmentZarinpal(Order $order, int $amount): array
    {
        $this->validateInstallment($order, $amount);

        return DB::transaction(function () use ($order, $amount) {
            $payment = $this->createPaymentRecord($order, $amount, 'zarinpal', 'installment');

            $result = $this->zarinpal->requestPayment($order, $amount);

            $payment->update(['authority' => $result['authority']]);
                       $order->forceFill(['payment_authority' => $result['authority']])->save();

            return $result;
        });
    }


    public function payInstallmentWithWallet(Order $order, int $amount): void
    {
        $wallet = $order->user->wallet;

        if (!$wallet || $wallet->balance < $amount) {
            throw new \RuntimeException('موجودی کیف پول کافی نیست.');
        }

        DB::transaction(function () use ($order, $amount, $wallet) {
            // اول PresalePayment بسازیم تا ID منحصربفرد داشته باشیم
            $payment = PresalePayment::create([
                'order_id'    => $order->id,
                'user_id'     => $order->user_id,
                'amount'      => $amount,
                'method'      => 'wallet',
                'type'        => 'installment',
                'status'      => 'verified',
                'verified_at' => now(),
            ]);

            $oldBalance = $wallet->balance;
            $wallet->decrement('balance', $amount);
            $newBalance = $oldBalance - $amount;

            if (class_exists(\App\Models\WalletTransaction::class)) {
                \App\Models\WalletTransaction::create([
                    'wallet_id'      => $wallet->id,
                    'amount'         => -$amount,
                    'balance_after'  => $newBalance,
                    'type'           => 'debit',
                    'description'    => "پرداخت قسط پیش‌فروش سفارش {$order->order_number}",
                    'reference_type' => \App\Enums\WalletReferenceType::PresaleRefund->value,
                    'reference_id'   => $payment->id,  // ← payment ID نه order ID
                ]);
            }

            $order->increment('presale_total_paid', $amount);
            $this->syncOrderPresaleStatus($order);
        });
    }

    /**
     * پرداخت با کیف پول (بیعانه یا قسط).
     */
    /**
     * پرداخت از کیف پول (بیعانه یا قسط)
     */
    public function payWithWallet(Order $order, int $amount, string $type = 'installment'): PresalePayment
    {
        if ($amount < 100000) {
            throw new \RuntimeException('حداقل مبلغ پرداخت ۱۰۰,۰۰۰ تومان است.');
        }

        return DB::transaction(function () use ($order, $amount, $type) {
            $order->refresh();

            if ($order->presale_status === PresaleStatus::Completed->value) {
                throw new \RuntimeException('این سفارش قبلاً تسویه شده است.');
            }
            if ($order->presale_status === PresaleStatus::Cancelled->value) {
                throw new \RuntimeException('این سفارش لغو شده است.');
            }

            $wallet = $order->user->wallet;
            if (! $wallet || $wallet->balance < $amount) {
                throw new \RuntimeException('موجودی کیف پول کافی نیست.');
            }

            $payment = PresalePayment::create([
                'order_id'   => $order->id,
                'user_id'    => $order->user_id,
                'amount'     => $amount,
                'method'     => 'wallet',
                'type'       => $type,
                'status'     => 'verified',
                'paid_at'    => now(),
                'verified_at'=> now(),
            ]);

            // کم کردن از کیف پول
            $wallet->decrement('balance', $amount);

            // ثبت تراکنش کیف پول
            if (class_exists(\App\Models\WalletTransaction::class)) {
            \App\Models\WalletTransaction::create([
                'wallet_id'      => $wallet->id,
                'amount'         => -$amount,
                'balance_after'  => $wallet->balance - $amount,
                'type'           => 'debit',
                'description'    => "پرداخت {$type} پیش‌فروش سفارش {$order->order_number}",
                'reference_type' => \App\Enums\WalletReferenceType::PresaleRefund->value,
                'reference_id'   => $payment->id,
            ]);
            }

            $order->increment('presale_total_paid', $amount);
            $this->syncOrderPresaleStatus($order);

            return $payment;
        });
    }
    /**
     * ایجاد پرداخت کارت‌به‌کارت (بیعانه یا قسط).
     */
    public function createC2c(Order $order, string $type = 'presale'): PresalePayment
    {
        $amount = $type === 'presale'
            ? $this->getDepositAmount($order)
            : throw new \RuntimeException('برای پرداخت قسط کارت‌به‌کارت، مبلغ را مشخص کنید.');

        return $this->createPaymentRecord($order, $amount, 'c2c', $type);
    }

    public function createInstallmentC2c(Order $order, int $amount): PresalePayment
    {
        $this->validateInstallment($order, $amount);

        return $this->createPaymentRecord($order, $amount, 'c2c', 'installment');
    }

    /**
     * تایید پرداخت زرین‌پال.
     */
    public function verifyZarinpal(string $authority, int $amountToman): ?PresalePayment
    {
        $payment = PresalePayment::where('authority', $authority)->lockForUpdate()->first();

        if (! $payment) {
            return null;
        }

        if ($payment->isVerified()) {
            return $payment;
        }

        $result = $this->zarinpal->verifyPayment($authority, $amountToman);

        $this->verifyPayment($payment, $amountToman, $result['ref_id'] ?? null);

        return $payment;
    }

    /**
     * تایید پرداخت کارت‌به‌کارت توسط ادمین.
     */
    public function verifyC2c(PresalePayment $payment): void
    {
        if ($payment->isVerified()) {
            return;
        }

        $this->verifyPayment($payment, $payment->amount);
    }

    /**
     * آپلود رسید کارت‌به‌کارت.
     */
    public function uploadReceipt(PresalePayment $payment, $file): void
    {
        $path = $file->store('presale-receipts', 'public');

        $payment->update([
            'screenshot_path' => $path,
            'status'          => 'pending_review',
        ]);
    }

    /**
     * بازگردانی تمام پرداخت‌های تاییدشده به کیف پول (هنگام لغو سفارش).
     */
    public function refundToWallet(Order $order): void
    {
        if ($order->presale_refunded_at) {
            return; // idempotent
        }

        $totalPaid = (int) $order->presale_total_paid;

        if ($totalPaid <= 0) {
            $order->update(['presale_refunded_at' => now()]);
            return;
        }

        $wallet = $this->wallet->getOrCreateForUser($order->user);

        DB::transaction(function () use ($order, $totalPaid, $wallet) {
            $this->wallet->credit(
                $wallet,
                $totalPaid,
                WalletReferenceType::PresaleRefund,
                $order->id,
                "استرداد سفارش لغوشده {$order->order_number}"
            );

            $order->update([
                'presale_refunded_at' => now(),
                'presale_total_paid'  => 0,
            ]);
        });
    }

    // ─────────────────────────────────────────────
    //  خصوصی
    // ─────────────────────────────────────────────

    private function createPaymentRecord(Order $order, int $amount, string $method, string $type): PresalePayment
    {
        return PresalePayment::create([
            'order_id'    => $order->id,
            'user_id'     => $order->user_id,
            'amount'      => $amount,
            'method'      => $method,
            'type'        => $type,
            'status'      => 'pending',
            'authority'   => null,
            'exact_rial'  => $method === 'c2c' ? $this->exactRialAmount($amount) : null,
        ]);
    }

    private function verifyPayment(PresalePayment $payment, int $amountToman, ?string $refId = null): void
    {
        DB::transaction(function () use ($payment, $amountToman, $refId) {
            $payment->update([
                'status'    => 'verified',
                'paid_at'   => now(),
                'ref_id'    => $refId,
                'verified_at' => now(),
            ]);

            $order = Order::lockForUpdate()->findOrFail($payment->order_id);

            // idempotent: فقط یک بار جمع بزن
            if (! $order->presalePayments()->where('id', $payment->id)->where('status', 'verified')->exists()) {
                return;
            }

            $newTotal = (int) $order->presale_total_paid + $amountToman;
            $order->update([
                'presale_total_paid' => $newTotal,
                'paid'               => $newTotal,
            ]);

            // sync status
            $this->syncOrderPresaleStatus($order);

            // اگر بیعانه اولیه بود و هنوز قیمت نهایی نداریم
            if ($payment->type === 'presale' && ! $order->presale_final_total) {
                $order->update([
                    'presale_status' => PresaleStatus::AwaitingFinalPrice->value,
                    'payment_status' => PaymentStatus::PartiallyPaid,
                ]);
            }
        });
    }

    /**
     * همگام‌سازی وضعیت سفارش بر اساس پرداخت‌ها
     */
    public function syncOrderPresaleStatus(Order $order): void
    {
        $order->refresh();

        if ($order->presale_status === PresaleStatus::Cancelled->value) {
            return;
        }

        $totalDue = ($order->presale_final_total ?? 0) + ($order->presale_penalties_total ?? 0);
        $paid = $order->presale_total_paid ?? 0;

        if ($totalDue > 0 && $paid >= $totalDue) {
            $order->update([
                'presale_status'    => PresaleStatus::Completed->value,
                'presale_completed_at'=> now(),
                'status'            => \App\Enums\OrderStatus::Paid,
                'payment_status'    => \App\Enums\PaymentStatus::Paid,
                'paid_at'           => now(),
            ]);
        } elseif ($paid > 0) {
            $order->update([
                'presale_status' => PresaleStatus::PartiallyPaid->value,
                'payment_status' => \App\Enums\PaymentStatus::PartiallyPaid,
            ]);
        }
    }

    private function getDepositAmount(Order $order): int
    {
        return (int) $order->payable;
    }

    private function amountDue(Order $order): int
    {
        $final = (int) ($order->presale_final_total ?? 0);
        $penalty = (int) ($order->presale_penalties_total ?? 0);

        if ($final === 0) {
            return (int) $order->payable;
        }

        $installmentsPaid = max(0, (int) $order->presale_total_paid - (int) $order->payable);
        $baseRemaining = max(0, $final + $penalty - (int) $order->subtotal);

        return max(0, $baseRemaining - $installmentsPaid);
    }

    private function validateInstallment(Order $order, int $amount): void
    {
        if (! $order->presale_final_total) {
            throw new \RuntimeException('قیمت نهایی هنوز تعیین نشده است.');
        }

        $remaining = $this->amountDue($order);

        if ($amount < 100000 && $remaining > 100000) {
            throw new \RuntimeException('حداقل مبلغ قسط ۱۰۰٬۰۰۰ تومان است.');
        }

        if ($amount > $remaining) {
            throw new \RuntimeException('مبلغ قسط از مانده بیشتر است.');
        }
    }

    private function exactRialAmount(int $amountToman): int
    {
        return ($amountToman * 10) + random_int(100, 999);
    }
}