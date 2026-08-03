<?php

namespace App\Models;

use App\Enums\LogAction;
use App\Enums\LogCategory;
use App\Enums\LogSeverity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = [
        'user_id', 'action', 'category', 'severity',
        'ip_address', 'user_agent', 'device_fingerprint',
        'url', 'method', 'payload', 'old_values', 'new_values',
        'description', 'reference_type', 'reference_id', 'session_id',
        'created_at',
    ];

    protected $casts = [
        'action' => LogAction::class,
        'category' => LogCategory::class,
        'severity' => LogSeverity::class,
        'payload' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    public function scopeByCategory($query, LogCategory $category)
    {
        return $query->where('category', $category);
    }

    public function scopeBySeverity($query, LogSeverity $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByAction($query, LogAction $action)
    {
        return $query->where('action', $action);
    }

    public function scopeInDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeSuspicious($query)
    {
        return $query->whereIn('severity', [LogSeverity::HIGH, LogSeverity::CRITICAL]);
    }

    public function getMaskedIpAttribute(): string
    {
        if (!$this->ip_address) return '-';
        $parts = explode('.', $this->ip_address);
        if (count($parts) === 4) {
            return "{$parts[0]}.{$parts[1]}.xxx.xxx";
        }
        return substr($this->ip_address, 0, 7) . '...';
    }
}