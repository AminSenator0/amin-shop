<?php

namespace App\Models;

use App\Enums\ReturnStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderReturn extends Model
{
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderReturnItem::class);
    }
}

