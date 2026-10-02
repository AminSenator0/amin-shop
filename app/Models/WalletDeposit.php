<?php

namespace App\Models;

use App\Enums\WalletDepositStatus;
use App\Enums\WalletGateway;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletDeposit extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_id',
        'amount',
        'gateway',
        'status',
        'authority',
        'receipt_path',
        'tracking_code',
        'rejection_reason',
        'paid_at',
        'processed_by',
    ];

    protected $casts = [
        'gateway'  => WalletGateway::class,
        'status'   => WalletDepositStatus::class,
        'amount'   => 'integer',
        'paid_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', WalletDepositStatus::Pending);
    }
}