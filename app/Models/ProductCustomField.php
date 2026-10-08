<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCustomField extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'label',
        'type',
        'display_type',
        'options',
        'is_required',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * جزئیات هر گزینه (موجودی/قیمت/تصویر) — ردیف‌های جدا برای lock امن در checkout
     */
    public function optionDetails(): HasMany
    {
        return $this->hasMany(ProductCustomFieldOption::class)->orderBy('sort_order');
    }

    /**
     * نوع نمایش مؤثر فیلد:
     * - فیلدهای بدون گزینه (text و…) → null (رندر پیش‌فرض فعلی)
     * - فیلدهای دارای گزینه → مقدار display_type (پیش‌فرض: buttons)
     */
    public function effectiveDisplayType(): ?string
    {
        if (empty($this->options) || ! is_array($this->options)) {
            return null;
        }

        return $this->display_type ?: 'buttons';
    }
}