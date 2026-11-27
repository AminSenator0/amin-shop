<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'options',
        'custom_fields', // ← اینو اضافه کن
        'price',
        'quantity',
        'total',
        'presale_product_id',
        'presale_final_price',
        'presale_penalty',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

        public function presaleProduct(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PresaleProduct::class);
    }

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'custom_fields' => 'array', // ← اضافه شده

        ];
    }

    public function optionsLabel(): ?string
    {
        $size = $this->options['size'] ?? null;
        $color = $this->options['color'] ?? null;

        $parts = [];

        if ($size) {
            $parts[] = 'سایز '.$size;
        }

        if ($color) {
            $parts[] = 'رنگ '.$color;
        }

        return $parts !== [] ? implode(' · ', $parts) : null;
    }
        public function isPresale(): bool
    {
        return $this->presale_product_id !== null;
    }
}
