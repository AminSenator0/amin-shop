<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductCustomFieldOption extends Model
{
    protected $fillable = [
        'product_custom_field_id',
        'value',
        'stock',
        'price',
        'image',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'price' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(ProductCustomField::class, 'product_custom_field_id');
    }

    // ── کمک‌کننده‌های منطق fallback ──────────────────────────────
    // این سه متد، قلب قانون «خالی = از محصول» هستن

    public function hasCustomStock(): bool
    {
        return $this->stock !== null;
    }

    public function hasCustomPrice(): bool
    {
        return $this->price !== null;
    }

    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return Storage::disk('public')->url($this->image);
    }
}