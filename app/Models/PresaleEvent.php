<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresaleEvent extends Model
{
    protected $fillable = [
        'presale_order_id', 'actor_id', 'action', 'payload', 'ip',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PresaleOrder::class, 'presale_order_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}