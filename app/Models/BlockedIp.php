<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockedIp extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address', 'reason', 'blocked_by',
        'blocked_until', 'failed_attempts', 'metadata',
    ];

    protected $casts = [
        'blocked_until' => 'datetime',
        'metadata' => 'array',
    ];

    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('blocked_until')
              ->orWhere('blocked_until', '>', now());
        });
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('blocked_until')
                     ->where('blocked_until', '<=', now());
    }

    public function isActive(): bool
    {
        return is_null($this->blocked_until) || $this->blocked_until->isFuture();
    }
}