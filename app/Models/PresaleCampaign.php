<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PresaleCampaign extends Model
{
    protected $fillable = [
        'title', 'starts_at', 'ends_at', 'is_extended',
        'extended_at', 'status', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'   => 'datetime',
            'ends_at'     => 'datetime',
            'is_extended' => 'boolean',
            'extended_at' => 'datetime',
            'closed_at'   => 'datetime',
        ];
    }

    public function presaleProducts(): HasMany
    {
        return $this->hasMany(PresaleProduct::class, 'campaign_id');
    }

    public function presaleOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'presale_campaign_id')
            ->where('order_type', 'presale');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // آیا الان بازه‌ی خرید پیش‌فروش است؟ (تمدید با جابه‌جایی ends_at اعمال می‌شود)
    public function isOpenNow(): bool
    {
        $now = now();

        return $this->isActive()
            && $now->greaterThanOrEqualTo($this->starts_at)
            && $now->lessThanOrEqualTo($this->ends_at);
    }
}