<?php

namespace App\Enums;

enum LogCategory: string
{
    case AUTH = 'auth';
    case ADMIN = 'admin';
    case PAYMENT = 'payment';
    case ORDER = 'order';
    case COUPON = 'coupon';
    case SUSPICIOUS = 'suspicious';
    case ERROR = 'error';

    public function label(): string
    {
        return match($this) {
            self::AUTH => 'ورود و احراز هویت',
            self::ADMIN => 'لاگ مدیریت',
            self::PAYMENT => 'پرداخت‌ها',
            self::ORDER => 'سفارش‌ها',
            self::COUPON => 'کد تخفیف',
            self::SUSPICIOUS => 'رفتارهای مشکوک',
            self::ERROR => 'خطاها',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::AUTH => '🔑',
            self::ADMIN => '👨‍💼',
            self::PAYMENT => '💳',
            self::ORDER => '📦',
            self::COUPON => '🎟️',
            self::SUSPICIOUS => '🚨',
            self::ERROR => '⚠️',
        };
    }
}