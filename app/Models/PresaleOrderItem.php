<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresaleOrderItem extends Model
{
    protected $fillable = [
        'presale_order_id', 'presale_product_id', 'product_id',
        'product_name', 'product_sku', 'product_image',
        'presale_price', 'final_price', 'penalty', 'quantity',
    ];

    protected function casts(): array
    {
        return [
            'presale_price' => 'integer',
            'final_price'   => 'integer',
            'penalty'       => 'integer',
            'quantity'      => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PresaleOrder::class, 'presale_order_id');
    }

    public function presaleProduct(): BelongsTo
    {
        return $this->belongsTo(PresaleProduct::class, 'presale_product_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function presaleSubtotal(): int
    {
        return (int) $this->presale_price * (int) $this->quantity;
    }

    public function finalSubtotal(): int
    {
        return $this->final_price !== null
            ? (int) $this->final_price * (int) $this->quantity
            : 0;
    }

    public function penaltySubtotal(): int
    {
        return (int) $this->penalty * (int) $this->quantity;
    }

    public function isPriced(): bool
    {
        return $this->final_price !== null;
    }
}