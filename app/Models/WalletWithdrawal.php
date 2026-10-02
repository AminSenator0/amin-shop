<?php

namespace App\Models;

use App\Enums\WalletWithdrawalStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletWithdrawal extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_id',
        'amount',
        'sheba',
        'status',
        'admin_note',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'status'       => WalletWithdrawalStatus::class,
        'amount'       => 'integer',
        'processed_at' => 'datetime',
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
        return $query->where('status', WalletWithdrawalStatus::Pending);
    }
}