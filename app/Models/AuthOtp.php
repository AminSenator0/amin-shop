<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthOtp extends Model
{
    protected $fillable = [
        'channel',
        'destination',
        'purpose',
        'code_hash',
        'payload',
        'attempts',
        'expires_at',
        'consumed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isConsumed(): bool
    {
        return $this->consumed_at !== null;
    }
}
