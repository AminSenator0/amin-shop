<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'full_name',
        'phone',
        'province',
        'city',
        'address',
        'postal_code',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fullAddress(): string
    {
        return "{$this->province}، {$this->city}، {$this->address} - کد پستی: {$this->postal_code}";
    }

    public function toSnapshot(): array
    {
        return [
            'title' => $this->title,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'province' => $this->province,
            'city' => $this->city,
            'address' => $this->address,
            'postal_code' => $this->postal_code,
        ];
    }
}
