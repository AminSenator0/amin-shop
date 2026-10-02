<?php

namespace App\Services;

use App\Enums\LogAction;
use App\Enums\LogSeverity;
use App\Enums\WalletReferenceType;
use App\Enums\WalletTransactionType;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\InvalidWalletOperationException;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

/**
 * قلب امنیت کیف پول.
 *
 * قوانین غیرقابل‌مذاکره:
 * 1. موجودی فقط و فقط از داخل این سرویس تغییر می‌کند (هیچ controller/model دیگری اجازه update داردکت balance را ندارد)
 * 2. هر تغییر موجودی داخل DB::transaction و با قفل ردیف کیف پول (lockForUpdate) انجام می‌شود
 * 3. هر تغییر حتماً یک رکورد در wallet_transactions (دفتر کل) می‌سازد — دفتر کل immutable است
 * 4. تراکنش با یک مرجع (reference) فقط یک بار اعمال می‌شود (idempotency) — هم در کد زیر قفل، هم unique index در دیتابیس
 * 5. موجودی هرگز منفی نمی‌شود
 * 6. هر عملیات در AuditLog ثبت می‌شود
 */
class WalletService
{
    /**
     * گرفتن کیف پول کاربر؛ در صورت نبودن می‌سازد.
     */
    public function getOrCreateForUser(User $user): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'is_active' => true]
        );
    }

    /**
     * واریز به کیف پول.
     */
    public function credit(Wallet $wallet, int $amount, WalletReferenceType $referenceType, ?int $referenceId = null, ?string $description = null, ?User $actor = null): WalletTransaction
    {
        return $this->apply($wallet, $amount, WalletTransactionType::Credit, $referenceType, $referenceId, $description, $actor);
    }

    /**
     * برداشت از کیف پول — در صورت موجودی ناکافی exception پرتاب می‌کند.
     */
    public function debit(Wallet $wallet, int $amount, WalletReferenceType $referenceType, ?int $referenceId = null, ?string $description = null, ?User $actor = null): WalletTransaction
    {
        return $this->apply($wallet, $amount, WalletTransactionType::Debit, $referenceType, $referenceId, $description, $actor);
    }

    /**
     * هسته‌ی اعمال تراکنش — هرگز مستقیم صدا نزن.
     */
    private function apply(Wallet $wallet, int $amount, WalletTransactionType $direction, WalletReferenceType $referenceType, ?int $referenceId, ?string $description, ?User $actor): WalletTransaction
    {
        if ($amount <= 0) {
            throw new InvalidWalletOperationException('مبلغ تراکنش باید بزرگ‌تر از صفر باشد.');
        }

        return DB::transaction(function () use ($wallet, $amount, $direction, $referenceType, $referenceId, $description, $actor) {

            // ۱) قفل ردیف کیف پول → ترتیب تراکنش‌ها تضمین می‌شود؛ دو درخواست هم‌زمان پشت سر هم اعمال می‌شوند
            /** @var Wallet $locked */
            $locked = Wallet::lockForUpdate()->findOrFail($wallet->id);

            if (! $locked->is_active) {
                throw new InvalidWalletOperationException('کیف پول این کاربر غیرفعال است.');
            }

            // ۲) Idempotency: اگر این مرجع قبلاً اعمال شده، همان تراکنش برمی‌گردد (بدون تغییر موجودی)
            if ($referenceId !== null) {
                $existing = WalletTransaction::query()
                    ->where('wallet_id', $locked->id)
                    ->where('reference_type', $referenceType->value)
                    ->where('reference_id', $referenceId)
                    ->where('type', $direction->value)
                    ->first();

                if ($existing) {
                    return $existing;
                }
            }

            // ۳) کنترل موجودی — داخل قفل، پس race condition معنایی ندارد
            if ($direction === WalletTransactionType::Debit && $locked->balance < $amount) {
                AuditLogService::log(
                    LogAction::WALLET_DEBIT_FAILED,
                    $actor?->id ?? $locked->user_id,
                    [
                        'wallet_id'      => $locked->id,
                        'amount'         => $amount,
                        'balance'        => $locked->balance,
                        'reference_type' => $referenceType->value,
                        'reference_id'   => $referenceId,
                    ],
                    severity: LogSeverity::HIGH,
                );

                throw new InsufficientFundsException();
            }

            // ۴) محاسبه‌ی موجودی جدید بدون decrement/increment (که race condition ایجاد می‌کند)
            $newBalance = $direction === WalletTransactionType::Credit
                ? $locked->balance + $amount
                : $locked->balance - $amount;

            if ($newBalance > PHP_INT_MAX) {
                throw new InvalidWalletOperationException('مبلغ تراکنش نامعتبر است.');
            }

            $locked->balance = $newBalance;
            $locked->save();

            // ۵) ثبت در دفتر کل (هر تراکنش balance_after خودش را دارد → قابل ممیزی)
            $transaction = WalletTransaction::create([
                'wallet_id'      => $locked->id,
                'type'           => $direction,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'amount'         => $amount,
                'balance_after'  => $newBalance,
                'description'    => $description,
                'created_by'     => $actor?->id,
                'ip'             => Request::ip(),
            ]);

            // ۶) AuditLog — تنظیم دستی ادمین = HIGH، بقیه INFO
            AuditLogService::log(
                $direction === WalletTransactionType::Credit ? LogAction::WALLET_CREDITED : LogAction::WALLET_DEBITED,
                $actor?->id ?? $locked->user_id,
                [
                    'wallet_id'      => $locked->id,
                    'amount'         => $amount,
                    'balance_after'  => $newBalance,
                    'reference_type' => $referenceType->value,
                    'reference_id'   => $referenceId,
                ],
                referenceType: $referenceType->value,
                referenceId: $referenceId,
                severity: $referenceType === WalletReferenceType::AdminAdjustment
                    ? LogSeverity::HIGH
                    : LogSeverity::INFO,
            );

            return $transaction;
        });
    }
}