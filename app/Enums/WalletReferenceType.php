<?php

namespace App\Enums;

enum WalletReferenceType: string
{
    case ZarinpalDeposit = 'zarinpal_deposit';
    case C2CDeposit      = 'c2c_deposit';
    case OrderPayment    = 'order_payment';
    case OrderRefund     = 'order_refund';
    case WithdrawalHold  = 'withdrawal_hold';
    case WithdrawalRefund = 'withdrawal_refund';
    case AdminAdjustment = 'admin_adjustment';
    case OrderReturnRefund = 'order_return_refund';
    case PresalePayment = 'presale_payment';
    case PresaleRefund  = 'presale_refund';

    public function label(): string
    {
        return match($this) {
            self::ZarinpalDeposit   => 'شارژ زرین‌پال',
            self::C2CDeposit        => 'شارژ کارت به کارت',
            self::OrderPayment      => 'پرداخت سفارش',
            self::OrderRefund       => 'بازگشت وجه',
            self::WithdrawalHold    => 'برداشت از کیف پول',
            self::WithdrawalRefund  => 'برگشت برداشت',
            self::AdminAdjustment   => 'تنظیم ادمین',
            self::OrderReturnRefund => 'بازگشت وجه مرجوعی',
            self::PresalePayment    => 'پرداخت قسط پیش‌فروش',
            self::PresaleRefund     => 'برگشت وجه پیش‌فروش',
        };
    }
}