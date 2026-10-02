<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $fillable = ['user_id', 'balance', 'is_active'];

    protected $casts = [
        'balance'   => 'integer',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)->latest();
    }

    public function deposits(): HasMany
    {
        return $this->hasMany(WalletDeposit::class)->latest();
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(WalletWithdrawal::class)->latest();
    }

    /**
     * مبلغ قابل استفاده از کیف پول برای یک سفارش (پرداخت ترکیبی)
     */
    public function usableFor(int $payableAmount): int
    {
        return min($this->balance, $payableAmount);
    }
}