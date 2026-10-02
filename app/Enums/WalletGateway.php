<?php

namespace App\Enums;

enum WalletGateway: string
{
    case Zarinpal = 'zarinpal';
    case C2C      = 'c2c';

    public function label(): string
    {
        return match($this) {
            self::Zarinpal => 'زرین‌پال',
            self::C2C      => 'کارت به کارت',
        };
    }
}