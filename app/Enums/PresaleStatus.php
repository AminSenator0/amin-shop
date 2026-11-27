<?php

namespace App\Enums;

enum PresaleStatus: string
{
    case AwaitingFinalPrice = 'awaiting_final_price'; // پرداخت اول انجام شده، قیمت نهایی نیامده
    case AwaitingPayment = 'awaiting_payment';        // قیمت نهایی مشخص شد، هنوز واریز نکرده
    case PartiallyPaid = 'partially_paid';            // قسطی واریز کرده
    case Completed = 'completed';                     // تسویه کامل
    case Cancelled = 'cancelled';                     // لغو توسط ادمین + برگشت به کیف پول

    public function label(): string
    {
        return match ($this) {
            self::AwaitingFinalPrice => 'در انتظار تعیین قیمت نهایی',
            self::AwaitingPayment    => 'در انتظار تسویه',
            self::PartiallyPaid      => 'تسویه مرحله‌ای (ناقص)',
            self::Completed          => 'تکمیل‌شده',
            self::Cancelled          => 'لغوشده',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AwaitingFinalPrice => 'amber',
            self::AwaitingPayment    => 'blue',
            self::PartiallyPaid      => 'indigo',
            self::Completed          => 'green',
            self::Cancelled          => 'red',
        };
    }

    public function isOpen(): bool
    {
        return ! in_array($this, [self::Completed, self::Cancelled], true);
    }
}