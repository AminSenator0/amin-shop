<?php

namespace App\Models;

use App\Enums\WalletReferenceType;
use App\Enums\WalletTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'type',
        'reference_type',
        'reference_id',
        'amount',
        'balance_after',
        'description',
        'created_by',
        'ip',
    ];

    protected $casts = [
        'type'           => WalletTransactionType::class,
        'reference_type' => WalletReferenceType::class,
        'amount'         => 'integer',
        'balance_after'  => 'integer',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}