<?php

namespace App\Enums;

enum ReplyChannel: string
{
    case Panel = 'panel';
    case PanelSms = 'panel_sms';
    case Sms = 'sms';
    case Email = 'email';

    public function label(): string
    {
        return match ($this) {
            self::Panel => 'پاسخ در پنل',
            self::PanelSms => 'پنل + پیامک اطلاع‌رسانی',
            self::Sms => 'پاسخ با پیامک',
            self::Email => 'پاسخ با ایمیل',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Panel => 'فقط در پنل کاربری مشتری ثبت می‌شود.',
            self::PanelSms => 'در پنل ثبت می‌شود و پیامک «پاسخ جدید دارید» ارسال می‌شود.',
            self::Sms => 'متن پاسخ مستقیماً با پیامک برای مشتری ارسال می‌شود.',
            self::Email => 'متن پاسخ با ایمیل برای مشتری ارسال می‌شود.',
        };
    }

    public function savesToPanel(): bool
    {
        return true;
    }

    public function sendsSmsNotification(): bool
    {
        return $this === self::PanelSms;
    }

    public function sendsSmsBody(): bool
    {
        return $this === self::Sms;
    }

    public function sendsEmail(): bool
    {
        return $this === self::Email;
    }
}
