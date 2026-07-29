<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends Model
{
    protected $fillable = [
        'name',
        'description',
        'cost',
        'free_above',
        'estimated_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function calculateCost(int $subtotal): int
    {
        if ($this->free_above && $subtotal >= $this->free_above) {
            return 0;
        }

        return $this->cost;
    }
}
