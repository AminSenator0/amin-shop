<?php

namespace App\Enums;

enum ReturnStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'در انتظار بررسی',
            self::Approved => 'تأیید شده',
            self::Rejected => 'رد شده',
            self::Refunded => 'بازگشت وجه',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Approved => 'blue',
            self::Rejected => 'rose',
            self::Refunded => 'emerald',
        };
    }
}
