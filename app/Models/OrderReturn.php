<?php

namespace App\Models;

use App\Enums\ReturnStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderReturn extends Model
{
    public const DEDUCTION_PERCENT = 30;

    protected $fillable = [
        'order_id',
        'reason',
        'status',
        'refund_amount',
        'admin_note',
        'processed_at',
        'is_partial',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReturnStatus::class,
            'processed_at' => 'datetime',
            'is_partial' => 'boolean',
        ];
    }

    /**
     * مبلغی که پس از کسر ۳۰ درصد به کیف پول واریز می‌شود.
     */
    public function netRefundAmount(): int
    {
        return (int) round(
            ((int) $this->refund_amount) * (100 - self::DEDUCTION_PERCENT) / 100
        );
    }

    /**
     * مبلغ کسرشده از مبنای مرجوعی.
     */
    public function deductionAmount(): int
    {
        return max(
            0,
            (int) $this->refund_amount - $this->netRefundAmount()
        );
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderReturnItem::class);
    }
}