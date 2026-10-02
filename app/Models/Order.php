<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReturnStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'payment_status',
        'payment_ref',
        'tracking_code',
        'payment_authority',
        'shipping_method_id',
        'payment_method',
        'coupon_id',
        'coupon_code',
        'subtotal',
        'shipping_cost',
        'discount_amount',
        'total',
        'shipping_address',
        'notes',
        'internal_notes',
        'paid_at',
        'c2c_discount',
        'payable',
        'wallet_amount',
        'shipped_at',
        'delivered_at',
        'admin_read_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'shipping_address' => 'array',
            'payment_method' => 'string',
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'wallet_amount' => 'integer',
            'delivered_at' => 'datetime',
            'admin_read_at' => 'datetime',
        ];
    }

    public function scopeUnreadByAdmin(Builder $query): Builder
    {
        return $query->whereNull('admin_read_at');
    }

    public function isUnreadByAdmin(): bool
    {
        return $this->admin_read_at === null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function c2cPayment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(C2CPayment::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(OrderActivity::class)->latest();
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-'.now()->format('Ymd').'-'.strtoupper(substr(uniqid(), -6));
    }

        /**
     * مبلغی که باید از درگاه پرداخت شود (پس از کسر سهم کیف پول).
     *
     * ⚠️ همیشه سمت سرور محاسبه می‌شود — هرگز به ورودی کاربر اعتماد نمی‌کند.
     * برای سفارش‌های قدیمی payable=0 → fallback به total
     */
    public function gatewayPayable(): int
    {
        $base = (int) ($this->payable > 0 ? $this->payable : $this->total);

        return max(0, $base - (int) $this->wallet_amount);
    }

    /**
     * آیا سفارش کاملاً با کیف پول پرداخت شده؟
     */
    public function isFullyPaidByWallet(): bool
    {
        return (int) $this->wallet_amount > 0 && $this->gatewayPayable() === 0;
    }
    public function canBePaidOnline(): bool
    {
        if (in_array($this->status, [OrderStatus::Cancelled, OrderStatus::Shipped, OrderStatus::Delivered], true)) {
            return false;
        }

        if (! in_array($this->status, [OrderStatus::Pending, OrderStatus::Failed], true)) {
            return false;
        }

        return in_array($this->payment_status, [PaymentStatus::Pending, PaymentStatus::Failed], true);
    }

    public function canBeCancelledByUser(): bool
    {
        if ($this->status === OrderStatus::Cancelled) {
            return false;
        }

        return in_array($this->status, [OrderStatus::Pending, OrderStatus::Failed, OrderStatus::Paid], true);
    }

    public function canBeCancelledByAdmin(): bool
    {
        if ($this->status === OrderStatus::Cancelled) {
            return false;
        }

        return ! in_array($this->status, [OrderStatus::Shipped, OrderStatus::Delivered], true);
    }

    public function canBeReturnedByUser(): bool
    {
        if ($this->status !== OrderStatus::Delivered) {
            return false;
        }

        return ! $this->returns()
            ->whereIn('status', [ReturnStatus::Pending, ReturnStatus::Approved])
            ->exists();
    }

    public function hasOpenReturn(): bool
    {
        return $this->returns()
            ->whereIn('status', [ReturnStatus::Pending, ReturnStatus::Approved])
            ->exists();
    }

    public function latestReturn(): ?OrderReturn
    {
        return $this->returns()->latest()->first();
    }
}
