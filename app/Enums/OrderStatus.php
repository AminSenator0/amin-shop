<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'در انتظار پرداخت',
            self::Paid => 'پرداخت شده',
            self::Processing => 'در حال آماده‌سازی',
            self::Shipped => 'ارسال شده',
            self::Delivered => 'تحویل داده شده',
            self::Failed => 'ناموفق',
            self::Cancelled => 'لغو شده',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Paid => 'blue',
            self::Processing => 'indigo',
            self::Shipped => 'purple',
            self::Delivered => 'green',
            self::Failed => 'rose',
            self::Cancelled => 'red',
        };
    }
}
