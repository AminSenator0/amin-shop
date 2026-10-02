<?php

namespace App\Enums;

enum WalletDepositStatus: string
{
    case Pending  = 'pending';
    case Paid     = 'paid';
    case Rejected = 'rejected';
    case Expired  = 'expired';

    public function label(): string
    {
        return match($this) {
            self::Pending  => 'در انتظار',
            self::Paid     => 'پرداخت‌شده',
            self::Rejected => 'رد شده',
            self::Expired  => 'منقضی',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending  => 'yellow',
            self::Paid     => 'green',
            self::Rejected => 'red',
            self::Expired  => 'gray',
        };
    }
}