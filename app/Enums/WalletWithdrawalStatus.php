<?php

namespace App\Enums;

enum WalletWithdrawalStatus: string
{
    case Pending  = 'pending';
    case Paid     = 'paid';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::Pending  => 'در انتظار بررسی',
            self::Paid     => 'پرداخت‌شده',
            self::Rejected => 'رد شده',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending  => 'yellow',
            self::Paid     => 'green',
            self::Rejected => 'red',
        };
    }
}