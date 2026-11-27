<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresaleProduct extends Model
{
    protected $fillable = [
        'campaign_id', 'product_id', 'custom_fields', 'presale_price',
        'final_price', 'penalty_amount', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'presale_price'  => 'integer',
            'final_price'    => 'integer',
            'penalty_amount' => 'integer',
            'is_active'      => 'boolean',
            'custom_fields'  => 'array',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(PresaleCampaign::class, 'campaign_id');
            }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customFieldsLabel(): ?string
    {
        if (empty($this->custom_fields)) {
            return null;
        }

        $labels = [];
        foreach ($this->custom_fields as $fieldId => $data) {
            $field = \App\Models\ProductCustomField::find($fieldId);
            $option = \App\Models\ProductCustomFieldOption::find($data['option_id'] ?? null);
            if ($field && $option) {
                $labels[] = "{$field->label}: {$option->value}";
            }
        }

        return $labels !== [] ? implode(' · ', $labels) : null;
    }
}