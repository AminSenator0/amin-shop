<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order',
        'max_uses',
        'used_count',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_coupons')->withTimestamps();
    }

    public function isAvailable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function isValid(int $subtotal): bool
    {
        if (! $this->isAvailable()) {
            return false;
        }

        if ($subtotal < $this->min_order) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(int $subtotal): int
    {
        if ($subtotal <= 0) {
            return 0;
        }

        if ($this->type === 'fixed') {
            return min($this->value, $subtotal);
        }

        $percent = min(100, max(0, (int) $this->value));

        return min($subtotal, (int) round($subtotal * ($percent / 100)));
    }

    public function typeLabel(): string
    {
        return $this->type === 'fixed' ? 'مبلغ ثابت' : 'درصدی';
    }

    public function valueLabel(): string
    {
        if ($this->type === 'fixed') {
            return format_price($this->value);
        }

        return format_number($this->value).'٪';
    }

    public function statusLabel(): string
    {
        if (! $this->is_active) {
            return 'غیرفعال';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'منقضی‌شده';
        }

        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return 'تمام‌شده';
        }

        return 'قابل استفاده';
    }
}
