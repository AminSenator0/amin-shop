<?php

namespace App\Enums;

enum LogAction: string
{
    // Auth
    case LOGIN_SUCCESS = 'login_success';
    case LOGIN_FAILED = 'login_failed';
    case MULTIPLE_LOGIN_FAILED = 'multiple_login_failed';
    case LOGOUT = 'logout';
    case PASSWORD_CHANGED = 'password_changed';
    case FORGOT_PASSWORD_REQUESTED = 'forgot_password_requested';
    case RESET_PASSWORD_SUCCESS = 'reset_password_success';
    case EMAIL_PHONE_CHANGED = 'email_phone_changed';
    case ACCOUNT_TOGGLED = 'account_toggled';
    case NEW_DEVICE_OR_IP = 'new_device_or_ip';

    // Admin
    case ADMIN_LOGIN_SUCCESS = 'admin_login_success';
    case ADMIN_LOGIN_FAILED = 'admin_login_failed';
    case USER_CREATED = 'user_created';
    case USER_DELETED = 'user_deleted';
    case USER_UPDATED = 'user_updated';
    case ROLE_PERMISSION_CHANGED = 'role_permission_changed';
    case PRODUCT_CREATED = 'product_created';
    case PRODUCT_UPDATED = 'product_updated';
    case PRODUCT_DELETED = 'product_deleted';
    case PRICE_CHANGED = 'price_changed';
    case STOCK_CHANGED = 'stock_changed';
    case DISCOUNT_CHANGED = 'discount_changed';
    case COUPON_CREATED = 'coupon_created';
    case COUPON_DELETED = 'coupon_deleted';
    case SITE_SETTINGS_CHANGED = 'site_settings_changed';
    case PAYMENT_SETTINGS_CHANGED = 'payment_settings_changed';
    case SMTP_SETTINGS_CHANGED = 'smtp_settings_changed';
    case ORDER_STATUS_CHANGED = 'order_status_changed';
    case ORDER_CANCELLED = 'order_cancelled';

    // Payment
    case PAYMENT_CREATED = 'payment_created';
    case PAYMENT_STARTED = 'payment_started';
    case PAYMENT_SUCCESS = 'payment_success';
    case PAYMENT_FAILED = 'payment_failed';
    case PAYMENT_CALLBACK = 'payment_callback';
    case DUPLICATE_TRANSACTION = 'duplicate_transaction';
    case AMOUNT_MISMATCH = 'amount_mismatch';
    case WRONG_ORDER_PAYMENT = 'wrong_order_payment';
    case PAYMENT_STATUS_MANUAL = 'payment_status_manual';
    case REFUND_PROCESSED = 'refund_processed';
    case MULTIPLE_PAYMENT_ATTEMPTS = 'multiple_payment_attempts';

    // Order
    case ORDER_CREATED = 'order_created';
    case ORDER_PAID = 'order_paid';
    case ORDER_CANCELLED_USER = 'order_cancelled_user';
    case ORDER_REFUNDED = 'order_refunded';
    case ORDER_STATUS_CHANGED_AUTO = 'order_status_changed_auto';
    case ORDER_ITEM_CHANGED = 'order_item_changed';

    // Coupon
    case COUPON_CREATED_ADMIN = 'coupon_created_admin';
    case COUPON_DELETED_ADMIN = 'coupon_deleted_admin';
    case COUPON_PERCENT_CHANGED = 'coupon_percent_changed';
    case COUPON_AMOUNT_CHANGED = 'coupon_amount_changed';
    case COUPON_EXPIRY_CHANGED = 'coupon_expiry_changed';
    case COUPON_LIMIT_CHANGED = 'coupon_limit_changed';
    case COUPON_UNAUTHORIZED_USE = 'coupon_unauthorized_use';
    case COUPON_OVERUSE_ATTEMPT = 'coupon_overuse_attempt';

    // Suspicious
    case MASS_LOGIN_FAILED = 'mass_login_failed';
    case MASS_FORGOT_PASSWORD = 'mass_forgot_password';
    case MASS_RESET_REQUESTS = 'mass_reset_requests';
    case ENDPOINT_FLOOD = 'endpoint_flood';
    case ADMIN_ACCESS_ATTEMPT = 'admin_access_attempt';
    case USER_HIT_ADMIN_ENDPOINTS = 'user_hit_admin_endpoints';
    case RAPID_PRICE_CHANGES = 'rapid_price_changes';
    case COUPON_REUSE_ATTEMPT = 'coupon_reuse_attempt';
    case ORDER_AMOUNT_TAMPERED = 'order_amount_tampered';
    case PAYMENT_CALLBACK_MISMATCH = 'payment_callback_mismatch';
    case ABNORMAL_HTTP_REQUESTS = 'abnormal_http_requests';
    case MASS_403_ERRORS = 'mass_403_errors';

    // Error
    case EXCEPTION_THROWN = 'exception_thrown';

    public function category(): LogCategory
    {
        return match(true) {
            str_starts_with($this->value, 'login_'),
            str_starts_with($this->value, 'logout'),
            str_starts_with($this->value, 'password_'),
            str_starts_with($this->value, 'forgot_'),
            str_starts_with($this->value, 'reset_'),
            str_starts_with($this->value, 'email_phone_'),
            str_starts_with($this->value, 'account_'),
            str_starts_with($this->value, 'new_device') => LogCategory::AUTH,

            str_starts_with($this->value, 'admin_'),
            str_starts_with($this->value, 'user_'),
            str_starts_with($this->value, 'role_'),
            str_starts_with($this->value, 'product_'),
            str_starts_with($this->value, 'price_'),
            str_starts_with($this->value, 'stock_'),
            str_starts_with($this->value, 'discount_'),
            str_starts_with($this->value, 'site_'),
            str_starts_with($this->value, 'payment_settings_'),
            str_starts_with($this->value, 'smtp_'),
            str_starts_with($this->value, 'order_status_'),
            str_starts_with($this->value, 'order_cancelled') => LogCategory::ADMIN,

            str_starts_with($this->value, 'payment_'),
            str_starts_with($this->value, 'duplicate_'),
            str_starts_with($this->value, 'amount_'),
            str_starts_with($this->value, 'wrong_'),
            str_starts_with($this->value, 'refund_'),
            str_starts_with($this->value, 'multiple_payment') => LogCategory::PAYMENT,

            str_starts_with($this->value, 'order_') => LogCategory::ORDER,

            str_starts_with($this->value, 'coupon_') => LogCategory::COUPON,

            str_starts_with($this->value, 'mass_'),
            str_starts_with($this->value, 'endpoint_'),
            str_starts_with($this->value, 'admin_access_'),
            str_starts_with($this->value, 'user_hit_'),
            str_starts_with($this->value, 'rapid_'),
            str_starts_with($this->value, 'coupon_reuse'),
            str_starts_with($this->value, 'order_amount_'),
            str_starts_with($this->value, 'payment_callback_'),
            str_starts_with($this->value, 'abnormal_'),
            str_starts_with($this->value, 'mass_403') => LogCategory::SUSPICIOUS,

            default => LogCategory::ERROR,
        };
    }

    public function label(): string
    {
        return match($this) {
            self::LOGIN_SUCCESS => 'ورود موفق',
            self::LOGIN_FAILED => 'ورود ناموفق',
            self::MULTIPLE_LOGIN_FAILED => 'چندین تلاش ناموفق',
            self::LOGOUT => 'خروج از حساب',
            self::PASSWORD_CHANGED => 'تغییر رمز عبور',
            self::FORGOT_PASSWORD_REQUESTED => 'درخواست Forgot Password',
            self::RESET_PASSWORD_SUCCESS => 'استفاده موفق Reset Password',
            self::EMAIL_PHONE_CHANGED => 'تغییر ایمیل/شماره',
            self::ACCOUNT_TOGGLED => 'فعال/غیرفعال شدن حساب',
            self::NEW_DEVICE_OR_IP => 'ورود از IP/دستگاه جدید',

            self::ADMIN_LOGIN_SUCCESS => 'ورود Admin',
            self::ADMIN_LOGIN_FAILED => 'ورود ناموفق Admin',
            self::USER_CREATED => 'ایجاد کاربر',
            self::USER_DELETED => 'حذف کاربر',
            self::USER_UPDATED => 'تغییر اطلاعات کاربر',
            self::ROLE_PERMISSION_CHANGED => 'تغییر Role/Permission',
            self::PRODUCT_CREATED => 'ایجاد محصول',
            self::PRODUCT_UPDATED => 'ویرایش محصول',
            self::PRODUCT_DELETED => 'حذف محصول',
            self::PRICE_CHANGED => 'تغییر قیمت',
            self::STOCK_CHANGED => 'تغییر موجودی',
            self::DISCOUNT_CHANGED => 'تغییر تخفیف',
            self::COUPON_CREATED => 'ایجاد/حذف کد تخفیف',
            self::COUPON_DELETED => 'حذف کد تخفیف',
            self::SITE_SETTINGS_CHANGED => 'تغییر تنظیمات سایت',
            self::PAYMENT_SETTINGS_CHANGED => 'تغییر تنظیمات پرداخت',
            self::SMTP_SETTINGS_CHANGED => 'تغییر تنظیمات SMTP',
            self::ORDER_STATUS_CHANGED => 'تغییر وضعیت سفارش',
            self::ORDER_CANCELLED => 'لغو سفارش',

            self::PAYMENT_CREATED => 'ایجاد Payment',
            self::PAYMENT_STARTED => 'شروع پرداخت',
            self::PAYMENT_SUCCESS => 'موفقیت پرداخت',
            self::PAYMENT_FAILED => 'پرداخت ناموفق',
            self::PAYMENT_CALLBACK => 'Callback درگاه',
            self::DUPLICATE_TRANSACTION => 'تراکنش تکراری',
            self::AMOUNT_MISMATCH => 'مبلغ متفاوت Order و Gateway',
            self::WRONG_ORDER_PAYMENT => 'پرداخت برای Order اشتباه',
            self::PAYMENT_STATUS_MANUAL => 'تغییر دستی وضعیت پرداخت',
            self::REFUND_PROCESSED => 'Refund',
            self::MULTIPLE_PAYMENT_ATTEMPTS => 'تلاش چندباره پرداخت',

            self::ORDER_CREATED => 'ایجاد سفارش',
            self::ORDER_PAID => 'پرداخت سفارش',
            self::ORDER_CANCELLED_USER => 'لغو سفارش توسط کاربر',
            self::ORDER_REFUNDED => 'بازگشت وجه سفارش',
            self::ORDER_STATUS_CHANGED_AUTO => 'تغییر وضعیت سفارش',
            self::ORDER_ITEM_CHANGED => 'تغییر آیتم سفارش',

            self::COUPON_CREATED_ADMIN => 'ایجاد Coupon',
            self::COUPON_DELETED_ADMIN => 'حذف Coupon',
            self::COUPON_PERCENT_CHANGED => 'تغییر درصد تخفیف',
            self::COUPON_AMOUNT_CHANGED => 'تغییر مبلغ تخفیف',
            self::COUPON_EXPIRY_CHANGED => 'تغییر تاریخ انقضا',
            self::COUPON_LIMIT_CHANGED => 'تغییر محدودیت استفاده',
            self::COUPON_UNAUTHORIZED_USE => 'استفاده غیرمجاز Coupon',
            self::COUPON_OVERUSE_ATTEMPT => 'استفاده بیش از سقف مجاز',

            self::MASS_LOGIN_FAILED => 'تعداد زیاد Login Failed',
            self::MASS_FORGOT_PASSWORD => 'تعداد زیاد Forgot Password',
            self::MASS_RESET_REQUESTS => 'تعداد زیاد Reset برای کاربران مختلف',
            self::ENDPOINT_FLOOD => 'درخواست زیاد به Endpoint',
            self::ADMIN_ACCESS_ATTEMPT => 'تلاش دسترسی به /admin',
            self::USER_HIT_ADMIN_ENDPOINTS => 'User عادی و Endpointهای Admin',
            self::RAPID_PRICE_CHANGES => 'تغییرات زیاد قیمت',
            self::COUPON_REUSE_ATTEMPT => 'تلاش استفاده چندباره Coupon',
            self::ORDER_AMOUNT_TAMPERED => 'تغییر مبلغ Order',
            self::PAYMENT_CALLBACK_MISMATCH => 'Callback با مبلغ متفاوت',
            self::ABNORMAL_HTTP_REQUESTS => 'درخواست‌های HTTP غیرعادی',
            self::MASS_403_ERRORS => 'خطاهای Authorization/403 زیاد',

            self::EXCEPTION_THROWN => 'خطای سیستمی',
        };
    }

    public function defaultSeverity(): LogSeverity
    {
        return match($this) {
            self::LOGIN_SUCCESS,
            self::LOGOUT,
            self::RESET_PASSWORD_SUCCESS,
            self::PAYMENT_SUCCESS,
            self::ORDER_CREATED,
            self::ORDER_PAID,
            self::ADMIN_LOGIN_SUCCESS => LogSeverity::INFO,
    
            self::LOGIN_FAILED,
            self::FORGOT_PASSWORD_REQUESTED,
            self::PAYMENT_FAILED,
            self::PAYMENT_CALLBACK,
            self::ORDER_CANCELLED_USER,
            self::COUPON_UNAUTHORIZED_USE => LogSeverity::WARNING,
    
            self::MULTIPLE_LOGIN_FAILED,
            self::ADMIN_LOGIN_FAILED,
            self::DUPLICATE_TRANSACTION,
            self::AMOUNT_MISMATCH,
            self::WRONG_ORDER_PAYMENT,
            self::PAYMENT_STATUS_MANUAL,
            self::COUPON_OVERUSE_ATTEMPT,
            self::MASS_LOGIN_FAILED,
            self::MASS_FORGOT_PASSWORD,
            self::ADMIN_ACCESS_ATTEMPT,
            self::USER_HIT_ADMIN_ENDPOINTS,
            self::COUPON_REUSE_ATTEMPT,
            self::MASS_403_ERRORS,
            self::PASSWORD_CHANGED,          // ← تغییر از INFO به HIGH
            self::EMAIL_PHONE_CHANGED,       // ← تغییر از CRITICAL به HIGH (برای ادمین trigger می‌شه)
            self::SITE_SETTINGS_CHANGED => LogSeverity::HIGH, // ← تغییر از INFO به HIGH
    
            self::NEW_DEVICE_OR_IP,
            self::MASS_RESET_REQUESTS,
            self::ENDPOINT_FLOOD,
            self::RAPID_PRICE_CHANGES,
            self::ORDER_AMOUNT_TAMPERED,
            self::PAYMENT_CALLBACK_MISMATCH,
            self::ABNORMAL_HTTP_REQUESTS,
            self::EXCEPTION_THROWN,
            self::PAYMENT_SETTINGS_CHANGED,  // ← تغییر از INFO به CRITICAL
            self::SMTP_SETTINGS_CHANGED => LogSeverity::CRITICAL, // ← تغییر از INFO به CRITICAL
    
            default => LogSeverity::INFO,
        };
    }
}