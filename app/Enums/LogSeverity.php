<?php

namespace App\Enums;

enum LogSeverity: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return match($this) {
            self::INFO => 'INFO',
            self::WARNING => 'WARNING',
            self::HIGH => 'HIGH',
            self::CRITICAL => 'CRITICAL',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::INFO => 'emerald',
            self::WARNING => 'amber',
            self::HIGH => 'orange',
            self::CRITICAL => 'rose',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::INFO => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
            self::WARNING => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
            self::HIGH => 'bg-orange-500/15 text-orange-400 border-orange-500/30',
            self::CRITICAL => 'bg-rose-500/15 text-rose-400 border-rose-500/30',
        };
    }
}