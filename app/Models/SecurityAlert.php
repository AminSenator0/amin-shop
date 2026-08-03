<?php

namespace App\Models;

use App\Enums\LogSeverity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'alert_type', 'severity', 'ip_address', 'user_id',
        'message', 'evidence', 'is_resolved', 'resolved_by', 'resolved_at',
    ];

    protected $casts = [
        'severity' => LogSeverity::class,
        'evidence' => 'array',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', LogSeverity::CRITICAL);
    }

    public function markResolved(int $adminId): void
    {
        $this->update([
            'is_resolved' => true,
            'resolved_by' => $adminId,
            'resolved_at' => now(),
        ]);
    }
}