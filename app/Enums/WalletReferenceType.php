<?php

namespace App\Enums;

enum WalletReferenceType: string
{
    case ZarinpalDeposit = 'zarinpal_deposit'; // شارژ با زرین‌پال
    case C2CDeposit      = 'c2c_deposit';      // شارژ کارت به کارت
    case OrderPayment    = 'order_payment';    // پرداخت سفارش (کامل یا ترکیبی)
    case OrderRefund     = 'order_refund';     // مرجوعی / انصراف پرداخت ترکیبی
    case WithdrawalHold  = 'withdrawal_hold';  // بلوکه شدن مبلغ برداشت
    case WithdrawalRefund = 'withdrawal_refund'; // رد برداشت → برگشت مبلغ
    case AdminAdjustment = 'admin_adjustment'; // تنظیم دستی ادمین
    case OrderReturnRefund = 'order_return_refund'; // ⬅️ بازگشت وجه مرجوعی

    public function label(): string
    {
        return match($this) {
            self::ZarinpalDeposit  => 'شارژ زرین‌پال',
            self::C2CDeposit       => 'شارژ کارت به کارت',
            self::OrderPayment     => 'پرداخت سفارش',
            self::OrderRefund      => 'بازگشت وجه',
            self::WithdrawalHold   => 'برداشت از کیف پول',
            self::WithdrawalRefund => 'برگشت برداشت',
            self::AdminAdjustment  => 'تنظیم ادمین',
            self::OrderReturnRefund => 'بازگشت وجه مرجوعی',
        };
    }
}