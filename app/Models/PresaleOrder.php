<?php

namespace App\Models;

use App\Enums\PresaleStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PresaleOrder extends Model
{
    protected $fillable = [
        'user_id', 'campaign_id', 'order_number', 'status',
        'subtotal', 'final_total', 'penalties_total', 'total_paid',
        'deadline_at', 'completed_at', 'cancelled_at', 'cancel_reason',
    ];

    protected function casts(): array
    {
        return [
            'status'          => PresaleStatus::class,
            'subtotal'        => 'integer',
            'final_total'     => 'integer',
            'penalties_total' => 'integer',
            'total_paid'      => 'integer',
            'deadline_at'     => 'datetime',
            'completed_at'    => 'datetime',
            'cancelled_at'    => 'datetime',
        ];
    }

    public static function generateOrderNumber(): string
    {
        return 'PRE-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    // ─── روابط ─────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(PresaleCampaign::class, 'campaign_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PresaleOrderItem::class, 'presale_order_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PresalePayment::class, 'presale_order_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(PresaleEvent::class, 'presale_order_id')->latest();
    }

    // ─── محاسبات مالی (فقط خواندنی — همه سمت سرور) ──────────

    /** مبلغ سررسید: قیمت نهایی + جریمه‌ها */
    public function amountDue(): int
    {
        return (int) ($this->final_total ?? 0) + (int) $this->penalties_total;
    }

    /** مانده قابل پرداخت — هرگز منفی نمی‌شود */
    public function remaining(): int
    {
        return max(0, $this->amountDue() - (int) $this->total_paid);
    }

    public function hasFinalPrice(): bool
    {
        return $this->final_total !== null;
    }

    public function isSettled(): bool
    {
        return $this->hasFinalPrice() && $this->remaining() === 0;
    }

    public function isOverdue(): bool
    {
        return $this->deadline_at !== null
            && now()->greaterThan($this->deadline_at)
            && $this->status !== PresaleStatus::Completed;
    }

    /**
     * همگام‌سازی وضعیت بر اساس مبالغ — بعد از هر پرداخت/تغییر قیمت صدا زده می‌شود.
     * completed فقط وقتی که final_total کاملاً مشخص باشد.
     */
    public function syncStatus(): void
    {
        if (! $this->status->isOpen()) {
            return;
        }

        if ($this->isSettled()) {
            $this->status = PresaleStatus::Completed;
            $this->completed_at ??= now();
        } elseif ($this->hasFinalPrice()) {
            $this->status = ((int) $this->total_paid - (int) $this->subtotal) > 0
                ? PresaleStatus::PartiallyPaid
                : PresaleStatus::AwaitingPayment;
        } else {
            $this->status = PresaleStatus::AwaitingFinalPrice;
        }
    }
}