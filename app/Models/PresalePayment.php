<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresalePayment extends Model
{
    protected $fillable = [
        'order_id', 'user_id', 'amount', 'method', 'type', 'status',
        'authority', 'ref_id', 'exact_rial', 'expires_at',
        'paid_at', 'verified_at', 'fail_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount'     => 'integer',
            'exact_rial' => 'integer',
            'expires_at' => 'datetime',
            'paid_at'    => 'datetime',
        ];
    }

    public const METHOD_ZARINPAL = 'zarinpal';
    public const METHOD_C2C = 'c2c';

    public const TYPE_PRESALE = 'presale';       // پیش‌پرداخت اول
    public const TYPE_INSTALLMENT = 'installment'; // قسط تسویه

    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_FAILED = 'failed';

    public function order(): BelongsTo
    {
        return $this->belongsTo(PresaleOrder::class, 'presale_order_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    public function isC2cExpired(): bool
    {
        return $this->exact_rial !== null
            && $this->expires_at !== null
            && now()->greaterThan($this->expires_at);
    }
}