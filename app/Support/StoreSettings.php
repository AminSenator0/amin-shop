<?php

namespace App\Support;

use App\Models\StoreSetting;
use Illuminate\Support\Facades\Storage;

class StoreSettings
{
    public const DEFAULTS = [
        'store_name' => 'فروشگاه آنلاین',
        'tagline' => 'خرید آنلاین محصولات فیزیکی با ارسال سریع',
        'currency' => 'تومان',
        'primary_color' => '#16827D', // از config/store-theme.php — preset teal
        'accent_color' => '#2EC4B6',
        'logo' => '',
        'favicon' => '',
        'meta_description' => 'خرید آنلاین محصولات با ارسال سریع، پرداخت امن و پشتیبانی واقعی.',
        'footer_description' => 'فروشگاه آنلاین معتبر با ارسال سریع به سراسر کشور. خرید آسان، پرداخت امن و پشتیبانی پاسخگو.',
        'contact_email' => '',
        'contact_phone' => '',
        'contact_address' => '',
        'contact_hours' => 'شنبه تا پنجشنبه ۹ تا ۱۸',
        'social_instagram' => '',
        'social_telegram' => '',
        'social_whatsapp' => '',
        'maps_url' => '',
        'about_content' => "ما یک فروشگاه آنلاین محصولات فیزیکی هستیم که با هدف ارائه تجربه خرید ساده و مطمئن راه‌اندازی شده‌ایم.\n\nما مجموعه‌ای از محصولات باکیفیت را با ارسال سریع به سراسر کشور در اختیار شما قرار می‌دهیم.\n\n• ضمانت اصالت کالا\n• ارسال سریع و مطمئن\n• پشتیبانی پاسخگو\n• پرداخت امن آنلاین",
        'rules_content' => "ثبت سفارش\nثبت سفارش به منزله پذیرش قیمت و شرایط فروش است. پس از ثبت سفارش، پیامک یا ایمیل تایید ارسال می‌شود.\n\nپرداخت\nپرداخت از طریق درگاه زرین‌پال انجام می‌شود. تا زمان تایید پرداخت، سفارش در وضعیت «در انتظار پرداخت» باقی می‌ماند.\n\nارسال\nزمان تحویل بسته به روش ارسال انتخابی متفاوت است. کد رهگیری پس از ارسال در پنل کاربری قابل مشاهده است.\n\nمرجوعی\nدر صورت مغایرت کالا با سفارش یا آسیب‌دیدگی در حمل‌ونقل، حداکثر تا ۷ روز پس از تحویل امکان مرجوعی وجود دارد.\n\nحریم خصوصی\nاطلاعات شخصی شما محرمانه بوده و صرفاً برای پردازش سفارش استفاده می‌شود.",
        'return_days' => '7',
        'min_order_amount' => '0',
        'free_shipping_threshold' => '500000',
        'promo_banner_title' => 'ارسال رایگان برای سفارش‌های بالای {threshold}',
        'promo_banner_text' => 'همین حالا خرید کنید و از تخفیف‌های فصلی بهره‌مند شوید.',
        'auto_approve_reviews' => '0',
        'newsletter_enabled' => '1',
        'whatsapp_float_enabled' => '1',
        'maintenance_mode' => '0',
        'maintenance_message' => 'فروشگاه موقتاً در حال به‌روزرسانی است. به زودی برمی‌گردیم.',
        // صفحه اصلی
        'homepage_coupon_bar_enabled' => '1',
        'homepage_featured_coupon_id' => '',
        'homepage_free_shipping_bar_enabled' => '1',
        'homepage_flash_sale_enabled' => '0',
        'homepage_flash_sale_ends_at' => '',
        'homepage_brands_enabled' => '1',
        'homepage_bestsellers_enabled' => '1',
        'homepage_discounted_enabled' => '1',
        'homepage_reviews_enabled' => '1',
        'homepage_faq_enabled' => '1',
        'homepage_blog_enabled' => '1',
        'homepage_recently_viewed_enabled' => '1',
        'homepage_recommendations_enabled' => '1',
        'homepage_quick_track_enabled' => '1',
        'homepage_contact_section_enabled' => '1',
        'homepage_map_enabled' => '1',
        'homepage_video_enabled' => '0',
        'homepage_video_url' => '',
        'homepage_app_banner_enabled' => '0',
        'homepage_app_download_url' => '',
        'homepage_app_download_text' => 'اپلیکیشن ما را دانلود کنید',
        'homepage_payment_trust_enabled' => '1',
        'payment_gateways' => 'زرین‌پال',
        'enamad_url' => '',
        'enamad_image' => '',
        'social_instagram_embed' => '',
        'homepage_why_us_enabled' => '1',
        'homepage_how_it_works_enabled' => '1',
        'homepage_collections_enabled' => '1',
        'homepage_about_snippet_enabled' => '1',
        'homepage_size_guide_enabled' => '1',
        'homepage_sticky_nav_enabled' => '1',
        'newsletter_incentive_text' => '۱۰٪ تخفیف اولین خرید با عضویت در خبرنامه',
        // درگاه زرین‌پال (خالی = استفاده از .env در صورت وجود)
        'zarinpal_merchant_id' => '',
        'zarinpal_sandbox' => '1',
        // پایهٔ URL برای callback (خالی = APP_URL)
        'zarinpal_callback_base_url' => '',
        // پیامک کاوه‌نگار
        'sms_driver' => 'log',
        'sms_mode' => 'simple',
        'sms_kavenegar_api_key' => '',
        'sms_kavenegar_sender' => '',
        'sms_template_order_processing' => 'orderprocessing',
        'sms_template_order_shipped' => 'ordershipped',
        'sms_template_order_delivered' => 'orderdelivered',
        'sms_template_message_reply_notice' => 'messagereply',
        'sms_template_message_reply_body' => 'messagereplybody',
        'sms_template_auth_otp' => 'authotp',
        'sms_meli_auth' => 'credentials',
        'sms_meli_username' => '',
        'sms_meli_password' => '',
        'sms_meli_api_key' => '',
        'sms_meli_from' => '',
        'sms_meli_body_order_processing' => '',
        'sms_meli_body_order_shipped' => '',
        'sms_meli_body_order_delivered' => '',
        'sms_meli_body_message_reply_notice' => '',
        'sms_meli_body_message_reply_body' => '',
        'sms_meli_body_auth_otp' => '',
        // ایمیل / SMTP (خالی = استفاده از .env در صورت وجود)
        'mail_mailer' => 'log',
        'mail_host' => '',
        'mail_port' => '587',
        'mail_username' => '',
        'mail_password' => '',
        'mail_encryption' => 'tls',
        'mail_from_address' => '',
        'mail_from_name' => '',
        // ورود / OTP
        'auth_otp_enabled' => '1',
        'auth_otp_channel' => 'mobile',
    ];

    public static function smsDriver(): string
    {
        $row = StoreSetting::query()->where('key', 'sms_driver')->value('value');

        if ($row !== null && $row !== '') {
            return in_array($row, ['log', 'kavenegar', 'melipayamak'], true) ? $row : 'log';
        }

        return (string) config('services.sms.driver', 'log');
    }

    public static function smsMode(): string
    {
        $row = StoreSetting::query()->where('key', 'sms_mode')->value('value');

        if ($row !== null && $row !== '') {
            return in_array($row, ['simple', 'lookup'], true) ? $row : 'simple';
        }

        return 'simple';
    }

    public static function smsKavenegarApiKey(): string
    {
        $fromStore = trim((string) (StoreSetting::query()->where('key', 'sms_kavenegar_api_key')->value('value') ?? ''));

        if ($fromStore !== '') {
            return $fromStore;
        }

        return trim((string) config('services.sms.kavenegar.api_key', ''));
    }

    public static function smsKavenegarSender(): string
    {
        $fromStore = trim((string) (StoreSetting::query()->where('key', 'sms_kavenegar_sender')->value('value') ?? ''));

        if ($fromStore !== '') {
            return $fromStore;
        }

        return trim((string) config('services.sms.kavenegar.sender', ''));
    }

    public static function smsMeliAuth(): string
    {
        $row = StoreSetting::query()->where('key', 'sms_meli_auth')->value('value');

        if ($row !== null && $row !== '') {
            return in_array($row, ['credentials', 'api_key'], true) ? $row : 'credentials';
        }

        $fromConfig = trim((string) config('services.sms.melipayamak.auth', 'credentials'));

        return in_array($fromConfig, ['credentials', 'api_key'], true) ? $fromConfig : 'credentials';
    }

    public static function smsMeliUsername(): string
    {
        $fromStore = trim((string) (StoreSetting::query()->where('key', 'sms_meli_username')->value('value') ?? ''));

        if ($fromStore !== '') {
            return $fromStore;
        }

        return trim((string) config('services.sms.melipayamak.username', ''));
    }

    public static function smsMeliPassword(): string
    {
        $fromStore = trim((string) (StoreSetting::query()->where('key', 'sms_meli_password')->value('value') ?? ''));

        if ($fromStore !== '') {
            return $fromStore;
        }

        return trim((string) config('services.sms.melipayamak.password', ''));
    }

    public static function smsMeliApiKey(): string
    {
        $fromStore = trim((string) (StoreSetting::query()->where('key', 'sms_meli_api_key')->value('value') ?? ''));

        if ($fromStore !== '') {
            return $fromStore;
        }

        return trim((string) config('services.sms.melipayamak.api_key', ''));
    }

    public static function smsMeliFrom(): string
    {
        $fromStore = trim((string) (StoreSetting::query()->where('key', 'sms_meli_from')->value('value') ?? ''));

        if ($fromStore !== '') {
            return $fromStore;
        }

        return trim((string) config('services.sms.melipayamak.from', ''));
    }

    public static function smsMeliBodyId(string $key): int
    {
        return (int) trim((string) self::get('sms_meli_body_'.$key, '0'));
    }

    public static function smsTemplate(string $key): string
    {
        $settingKey = 'sms_template_'.$key;

        return trim((string) self::get($settingKey, ''));
    }

    public static function smsIsConfigured(): bool
    {
        return match (self::smsDriver()) {
            'kavenegar' => self::smsKavenegarApiKey() !== '',
            'melipayamak' => self::smsMeliAuth() === 'api_key'
                ? self::smsMeliApiKey() !== ''
                : self::smsMeliUsername() !== '' && self::smsMeliPassword() !== '',
            default => false,
        };
    }

    public static function smsApiKeyMasked(): string
    {
        return self::maskSecret(self::smsKavenegarApiKey());
    }

    public static function smsMeliPasswordMasked(): string
    {
        return self::maskSecret(self::smsMeliPassword());
    }

    public static function smsMeliApiKeyMasked(): string
    {
        return self::maskSecret(self::smsMeliApiKey());
    }

    private static function maskSecret(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (strlen($value) <= 8) {
            return str_repeat('*', strlen($value));
        }

        return substr($value, 0, 4).str_repeat('*', max(4, strlen($value) - 8)).substr($value, -4);
    }

    public static function zarinpalMerchantId(): string
    {
        $fromStore = trim((string) (StoreSetting::query()->where('key', 'zarinpal_merchant_id')->value('value') ?? ''));

        if ($fromStore !== '') {
            return $fromStore;
        }

        return trim((string) config('zarinpal.merchant_id', ''));
    }

    public static function zarinpalSandbox(): bool
    {
        $row = StoreSetting::query()->where('key', 'zarinpal_sandbox')->value('value');

        if ($row !== null) {
            return filter_var($row, FILTER_VALIDATE_BOOLEAN);
        }

        return (bool) config('zarinpal.sandbox', true);
    }

    public static function zarinpalIsConfigured(): bool
    {
        return self::zarinpalMerchantId() !== '';
    }

    /**
     * پایهٔ دامنهٔ callback (بدون اسلش پایانی).
     * اگر در پنل خالی باشد، از APP_URL استفاده می‌شود.
     */
    public static function zarinpalCallbackBaseUrl(): string
    {
        $fromStore = trim((string) (StoreSetting::query()->where('key', 'zarinpal_callback_base_url')->value('value') ?? ''));

        if ($fromStore !== '') {
            return self::tryNormalizeCallbackBaseUrl($fromStore)
                ?? rtrim((string) config('app.url', ''), '/');
        }

        return rtrim((string) config('app.url', ''), '/');
    }

    /** آدرس کامل callback که به زرین‌پال ارسال می‌شود. */
    public static function zarinpalCallbackUrl(): string
    {
        $path = route('payment.callback', absolute: false);

        if (! str_starts_with($path, '/')) {
            $path = '/'.$path;
        }

        return self::zarinpalCallbackBaseUrl().$path;
    }

    /** دامنهٔ ثبت‌شونده در پنل زرین‌پال (hostname). */
    public static function zarinpalCallbackDomain(): string
    {
        $host = parse_url(self::zarinpalCallbackBaseUrl(), PHP_URL_HOST);

        return is_string($host) ? $host : '';
    }

    /** نرمال‌سازی آدرس پایه؛ در صورت نامعتبر بودن null برمی‌گرداند. */
    public static function tryNormalizeCallbackBaseUrl(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (! preg_match('#^https?://#i', $value)) {
            $value = 'https://'.$value;
        }

        $parts = parse_url($value);

        if (! is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }

        $scheme = strtolower((string) $parts['scheme']);
        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower((string) $parts['host']);
        $isIp = filter_var($host, FILTER_VALIDATE_IP) !== false;
        $isLocal = in_array($host, ['localhost'], true);
        $isDomain = (bool) preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i', $host);

        if (! $isIp && ! $isLocal && ! $isDomain) {
            return null;
        }

        $base = $scheme.'://'.$host;

        if (! empty($parts['port'])) {
            $base .= ':'.$parts['port'];
        }

        $path = isset($parts['path']) ? rtrim((string) $parts['path'], '/') : '';
        if ($path !== '' && $path !== '/') {
            $base .= $path;
        }

        return $base;
    }

    public static function normalizeCallbackBaseUrl(string $value): string
    {
        return self::tryNormalizeCallbackBaseUrl($value)
            ?? rtrim((string) config('app.url', ''), '/');
    }

    public static function mailMailer(): string
    {
        $row = StoreSetting::query()->where('key', 'mail_mailer')->value('value');

        if ($row !== null && $row !== '') {
            return in_array($row, ['log', 'smtp'], true) ? $row : 'log';
        }

        $fallback = (string) config('mail.env_defaults.mailer', config('mail.default', 'log'));

        return in_array($fallback, ['log', 'smtp'], true) ? $fallback : 'log';
    }

    public static function mailHost(): string
    {
        $fromStore = trim((string) (StoreSetting::query()->where('key', 'mail_host')->value('value') ?? ''));

        if ($fromStore !== '') {
            return $fromStore;
        }

        return trim((string) config('mail.env_defaults.host', config('mail.mailers.smtp.host', '')));
    }

    public static function mailPort(): int
    {
        $fromStore = trim((string) (StoreSetting::query()->where('key', 'mail_port')->value('value') ?? ''));

        if ($fromStore !== '') {
            $port = (int) $fromStore;

            return $port > 0 ? $port : 587;
        }

        $fromEnv = (int) config('mail.env_defaults.port', config('mail.mailers.smtp.port', 587));

        return $fromEnv > 0 ? $fromEnv : 587;
    }

    public static function mailUsername(): string
    {
        $fromStore = trim((string) (StoreSetting::query()->where('key', 'mail_username')->value('value') ?? ''));

        if ($fromStore !== '') {
            return $fromStore;
        }

        return trim((string) config('mail.env_defaults.username', config('mail.mailers.smtp.username', '')));
    }

    public static function mailPassword(): string
    {
        $fromStore = trim((string) (StoreSetting::query()->where('key', 'mail_password')->value('value') ?? ''));

        if ($fromStore !== '') {
            return $fromStore;
        }

        return trim((string) config('mail.env_defaults.password', config('mail.mailers.smtp.password', '')));
    }

    /** tls | ssl | none — نگاشت به scheme لاراول */
    public static function mailEncryption(): string
    {
        $row = StoreSetting::query()->where('key', 'mail_encryption')->value('value');

        if ($row !== null && $row !== '') {
            return in_array($row, ['tls', 'ssl', 'none'], true) ? $row : 'tls';
        }

        $scheme = strtolower(trim((string) config('mail.env_defaults.scheme', config('mail.mailers.smtp.scheme', ''))));

        return match ($scheme) {
            'smtps' => 'ssl',
            'smtp' => 'tls',
            default => ((int) config('mail.env_defaults.port', config('mail.mailers.smtp.port', 587)) === 465) ? 'ssl' : 'tls',
        };
    }

    public static function mailScheme(): ?string
    {
        return match (self::mailEncryption()) {
            'ssl' => 'smtps',
            'none' => 'smtp',
            default => null,
        };
    }

    public static function mailFromAddress(): string
    {
        $fromStore = trim((string) (StoreSetting::query()->where('key', 'mail_from_address')->value('value') ?? ''));

        if ($fromStore !== '') {
            return $fromStore;
        }

        return trim((string) config('mail.env_defaults.from_address', config('mail.from.address', '')));
    }

    public static function mailFromName(): string
    {
        $fromStore = trim((string) (StoreSetting::query()->where('key', 'mail_from_name')->value('value') ?? ''));

        if ($fromStore !== '') {
            return $fromStore;
        }

        $fromEnv = trim((string) config('mail.env_defaults.from_name', config('mail.from.name', '')));

        return $fromEnv !== '' ? $fromEnv : (string) self::get('store_name', config('app.name'));
    }

    public static function mailIsConfigured(): bool
    {
        if (self::mailMailer() !== 'smtp') {
            return false;
        }

        return self::mailHost() !== '' && self::mailFromAddress() !== '';
    }

    public static function mailPasswordMasked(): string
    {
        return self::maskSecret(self::mailPassword());
    }

    public static function authOtpEnabled(): bool
    {
        $row = StoreSetting::query()->where('key', 'auth_otp_enabled')->value('value');

        if ($row !== null) {
            return filter_var($row, FILTER_VALIDATE_BOOLEAN);
        }

        return filter_var(self::DEFAULTS['auth_otp_enabled'], FILTER_VALIDATE_BOOLEAN);
    }

    /** mobile | email */
    public static function authOtpChannel(): string
    {
        $row = StoreSetting::query()->where('key', 'auth_otp_channel')->value('value');

        if ($row !== null && $row !== '') {
            return in_array($row, ['mobile', 'email'], true) ? $row : 'mobile';
        }

        return 'mobile';
    }

    public static function authOtpChannelReady(): bool
    {
        if (! self::authOtpEnabled()) {
            return false;
        }

        return match (self::authOtpChannel()) {
            'mobile' => self::smsIsConfigured() || self::smsDriver() === 'log',
            'email' => self::mailMailer() === 'log' || self::mailIsConfigured(),
            default => false,
        };
    }

    /** اعمال تنظیمات پنل روی کانفیگ runtime میل */
    public static function applyMailConfig(): void
    {
        $mailer = self::mailMailer();

        config([
            'mail.default' => $mailer,
            'mail.from.address' => self::mailFromAddress() ?: 'hello@example.com',
            'mail.from.name' => self::mailFromName(),
        ]);

        if ($mailer !== 'smtp') {
            return;
        }

        config([
            'mail.mailers.smtp.host' => self::mailHost() ?: '127.0.0.1',
            'mail.mailers.smtp.port' => self::mailPort(),
            'mail.mailers.smtp.username' => self::mailUsername() !== '' ? self::mailUsername() : null,
            'mail.mailers.smtp.password' => self::mailPassword() !== '' ? self::mailPassword() : null,
            'mail.mailers.smtp.scheme' => self::mailScheme(),
        ]);
    }

    /** تبدیل مبلغ واحد پول فروشگاه به ریال برای API زرین‌پال */
    public static function zarinpalAmountInRials(int $amount): int
    {
        $currency = mb_strtolower(trim((string) self::get('currency', 'تومان')));

        if (str_contains($currency, 'ریال') || str_contains($currency, 'rial')) {
            return $amount;
        }

        return $amount * 10;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $fallback = $default ?? (self::DEFAULTS[$key] ?? null);

        return StoreSetting::get($key, $fallback);
    }

    public static function bool(string $key): bool
    {
        return filter_var(self::get($key), FILTER_VALIDATE_BOOLEAN);
    }

    public static function int(string $key): int
    {
        return (int) normalize_numeric_string((string) self::get($key, '0'));
    }

    public static function imageUrl(string $key): ?string
    {
        $path = self::get($key);

        return $path ? asset('storage/'.$path) : null;
    }

    public static function logoUrl(): string
    {
        $path = self::get('logo');

        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/'.$path);
        }

        return asset('images/store-logo.svg');
    }

    public static function faviconUrl(): string
    {
        $path = self::get('favicon');

        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/'.$path);
        }

        return asset('favicon.svg');
    }

    public static function faviconMimeType(): string
    {
        $path = strtolower(parse_url(self::faviconUrl(), PHP_URL_PATH) ?? '');

        return match (true) {
            str_ends_with($path, '.svg') => 'image/svg+xml',
            str_ends_with($path, '.webp') => 'image/webp',
            default => 'image/png',
        };
    }

    public static function publishPublicFavicon(): void
    {
        $path = self::get('favicon');

        if ($path && Storage::disk('public')->exists($path)) {
            copy(
                Storage::disk('public')->path($path),
                public_path('favicon.ico')
            );

            return;
        }

        if (is_file(public_path('favicon.svg'))) {
            copy(public_path('favicon.svg'), public_path('favicon.ico'));
        }
    }

    public static function whatsappUrl(?string $phone = null): ?string
    {
        $phone = $phone ?? self::get('social_whatsapp');
        if (! $phone) {
            return null;
        }

        $normalized = normalize_mobile($phone);
        if (! $normalized) {
            return null;
        }

        return 'https://wa.me/98'.ltrim($normalized, '0');
    }

    public static function trustBadges(): array
    {
        $stored = self::get('trust_badges');
        if ($stored) {
            $decoded = json_decode($stored, true);
            if (is_array($decoded) && count($decoded) > 0) {
                return $decoded;
            }
        }

        $days = self::int('return_days');

        return [
            ['icon' => 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12', 'title' => 'ارسال سریع', 'desc' => 'تحویل ۱ تا ۳ روز کاری'],
            ['icon' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z', 'title' => 'پرداخت امن', 'desc' => 'درگاه زرین‌پال'],
            ['icon' => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99', 'title' => $days.' روز بازگشت', 'desc' => 'ضمانت مرجوعی کالا'],
            ['icon' => 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.375c0-2.278-1.847-4.125-4.125-4.125H4.125C1.847 2.25 0 4.097 0 6.375v11.25C0 19.653 1.847 21.5 4.125 21.5h13.875c2.278 0 4.125-1.847 4.125-4.125v-4.286c0-1.024-.69-1.868-1.575-2.152', 'title' => 'پشتیبانی آنلاین', 'desc' => 'پاسخگویی سریع'],
        ];
    }

    /** @return array<string, array{name: string, description: string, primary: string, accent: string}> */
    public static function colorPresets(): array
    {
        return config('store-theme.presets', []);
    }

    public static function defaultColorPresetId(): string
    {
        return (string) config('store-theme.default_preset', 'teal');
    }

    /** @return array<string, mixed> */
    public static function derivationRules(): array
    {
        return config('store-theme.derivation', []);
    }

    /** @return array{primary: string, accent: string} */
    public static function colorsFromPreset(string $presetId): array
    {
        $presets = self::colorPresets();
        $fallbackId = self::defaultColorPresetId();
        $preset = $presets[$presetId] ?? $presets[$fallbackId] ?? reset($presets);

        return [
            'primary' => self::normalizeHex($preset['primary']),
            'accent' => self::normalizeHex($preset['accent']),
        ];
    }

    public static function isValidColorPresetId(string $presetId): bool
    {
        return array_key_exists($presetId, self::colorPresets());
    }

    public static function resolveColorPresetId(?string $primary = null, ?string $accent = null): string
    {
        $primary = self::normalizeHex($primary ?? (string) self::get('primary_color'));
        $accent = self::normalizeHex($accent ?? (string) self::get('accent_color'));

        foreach (self::colorPresets() as $id => $preset) {
            if (
                self::normalizeHex($preset['primary']) === $primary
                && self::normalizeHex($preset['accent']) === $accent
            ) {
                return $id;
            }
        }

        return self::defaultColorPresetId();
    }

    /** @return array<string, string> */
    public static function paletteLabels(): array
    {
        return [
            'primary' => 'اصلی',
            'primary-hover' => 'هاور اصلی',
            'primary-dark' => 'اصلی تیره',
            'primary-deeper' => 'عمیق',
            'accent' => 'تأکید',
            'accent-hover' => 'هاور تأکید',
            'surface' => 'سطح',
            'background' => 'پس‌زمینه',
            'text' => 'متن',
            'muted' => 'کمرنگ',
            'on-hero' => 'متن بنر',
            'border' => 'حاشیه',
            'hero-from' => 'بنر (شروع)',
            'hero-via' => 'بنر (میانه)',
            'hero-to' => 'بنر (پایان)',
        ];
    }

    /** @return array<string, string> */
    public static function deriveThemePalette(string $primary, string $accent): array
    {
        $primary = self::normalizeHex($primary);
        $accent = self::normalizeHex($accent);
        $rules = self::derivationRules();

        $primaryDark = self::darkenHex($primary, (float) ($rules['primary_dark_darken'] ?? 0.25));
        $primaryDeeper = self::darkenHex($primary, (float) ($rules['primary_deeper_darken'] ?? 0.42));

        return [
            'primary' => $primary,
            'primary-hover' => self::darkenHex($primary, (float) ($rules['primary_hover_darken'] ?? 0.12)),
            'primary-dark' => $primaryDark,
            'primary-deeper' => $primaryDeeper,
            'accent' => $accent,
            'accent-hover' => self::darkenHex($accent, (float) ($rules['accent_hover_darken'] ?? 0.1)),
            'surface' => (string) ($rules['surface'] ?? '#FFFFFF'),
            'background' => self::lightenHex($primary, (float) ($rules['background_lighten'] ?? 0.95)),
            'text' => self::mixHex(
                (string) ($rules['text_base'] ?? '#1A1A1A'),
                $primaryDark,
                (float) ($rules['text_mix_weight'] ?? 0.18)
            ),
            'muted' => self::mixHex(
                (string) ($rules['muted_base'] ?? '#6B7280'),
                $primaryDark,
                (float) ($rules['muted_mix_weight'] ?? 0.22)
            ),
            'on-hero' => self::lightenHex($primary, (float) ($rules['on_hero_lighten'] ?? 0.88)),
            'border' => self::lightenHex($primary, (float) ($rules['border_lighten'] ?? 0.78)),
            'hero-from' => $primaryDark,
            'hero-via' => $primary,
            'hero-to' => $accent,
        ];
    }

    /** @return array<string, string> */
    public static function themeCssVariables(): array
    {
        $defaults = self::colorsFromPreset(self::defaultColorPresetId());

        return self::deriveThemePalette(
            (string) self::get('primary_color', $defaults['primary']),
            (string) self::get('accent_color', $defaults['accent'])
        );
    }

    public static function normalizeHex(string $hex): string
    {
        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return '#'.strtolower($hex);
    }

    /** @return array{0: int, 1: int, 2: int} */
    public static function hexToChannels(string $hex): array
    {
        $hex = ltrim(self::normalizeHex($hex), '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    public static function hexToRgb(string $hex): string
    {
        [$r, $g, $b] = self::hexToChannels($hex);

        return "{$r} {$g} {$b}";
    }

    public static function mixHex(string $hex1, string $hex2, float $weight = 0.5): string
    {
        [$r1, $g1, $b1] = self::hexToChannels($hex1);
        [$r2, $g2, $b2] = self::hexToChannels($hex2);

        $r = (int) round($r1 * (1 - $weight) + $r2 * $weight);
        $g = (int) round($g1 * (1 - $weight) + $g2 * $weight);
        $b = (int) round($b1 * (1 - $weight) + $b2 * $weight);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    public static function lightenHex(string $hex, float $amount): string
    {
        return self::mixHex($hex, '#FFFFFF', $amount);
    }

    public static function darkenHex(string $hex, float $factor = 0.15): string
    {
        [$r, $g, $b] = self::hexToChannels($hex);

        $r = max(0, (int) round($r * (1 - $factor)));
        $g = max(0, (int) round($g * (1 - $factor)));
        $b = max(0, (int) round($b * (1 - $factor)));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /** @return array<string, mixed> */
    public static function forAdmin(): array
    {
        $settings = [];
        foreach (array_keys(self::DEFAULTS) as $key) {
            $settings[$key] = self::get($key);
        }

        $settings['trust_badges'] = self::trustBadges();
        $settings['color_preset'] = self::resolveColorPresetId(
            $settings['primary_color'] ?? null,
            $settings['accent_color'] ?? null
        );
        $settings['zarinpal_merchant_id'] = self::zarinpalMerchantId();
        $settings['zarinpal_sandbox'] = self::zarinpalSandbox() ? '1' : '0';
        $settings['zarinpal_configured'] = self::zarinpalIsConfigured();
        $storedCallbackBase = trim((string) (StoreSetting::query()->where('key', 'zarinpal_callback_base_url')->value('value') ?? ''));
        $settings['zarinpal_callback_base_url'] = $storedCallbackBase;
        $settings['zarinpal_callback_url'] = self::zarinpalCallbackUrl();
        $settings['zarinpal_callback_domain'] = self::zarinpalCallbackDomain();
        $settings['zarinpal_callback_fallback_base'] = rtrim((string) config('app.url', ''), '/');
        $settings['sms_driver'] = self::smsDriver();
        $settings['sms_mode'] = self::smsMode();
        $settings['sms_kavenegar_api_key'] = '';
        $settings['sms_kavenegar_api_key_masked'] = self::smsApiKeyMasked();
        $settings['sms_kavenegar_api_key_set'] = self::smsKavenegarApiKey() !== '';
        $settings['sms_kavenegar_sender'] = self::smsKavenegarSender();
        $settings['sms_meli_auth'] = self::smsMeliAuth();
        $settings['sms_meli_username'] = self::smsMeliUsername();
        $settings['sms_meli_password'] = '';
        $settings['sms_meli_password_masked'] = self::smsMeliPasswordMasked();
        $settings['sms_meli_password_set'] = self::smsMeliPassword() !== '';
        $settings['sms_meli_api_key'] = '';
        $settings['sms_meli_api_key_masked'] = self::smsMeliApiKeyMasked();
        $settings['sms_meli_api_key_set'] = self::smsMeliApiKey() !== '';
        $settings['sms_meli_from'] = self::smsMeliFrom();
        $settings['sms_configured'] = self::smsIsConfigured();
        foreach (array_keys(\App\Services\SmsService::templateCatalog()) as $key) {
            $settings['sms_template_'.$key] = self::smsTemplate($key);
            $settings['sms_meli_body_'.$key] = (string) self::get('sms_meli_body_'.$key, '');
        }
        $settings['mail_mailer'] = self::mailMailer();
        $settings['mail_host'] = self::mailHost();
        $settings['mail_port'] = (string) self::mailPort();
        $settings['mail_username'] = self::mailUsername();
        $settings['mail_password'] = '';
        $settings['mail_password_masked'] = self::mailPasswordMasked();
        $settings['mail_password_set'] = self::mailPassword() !== '';
        $settings['mail_encryption'] = self::mailEncryption();
        $settings['mail_from_address'] = self::mailFromAddress();
        $settings['mail_from_name'] = self::mailFromName();
        $settings['mail_configured'] = self::mailIsConfigured();
        $settings['auth_otp_enabled'] = self::authOtpEnabled() ? '1' : '0';
        $settings['auth_otp_channel'] = self::authOtpChannel();
        $settings['auth_otp_channel_ready'] = self::authOtpChannelReady();
        $settings['auth_otp_sms_ready'] = self::smsIsConfigured() || self::smsDriver() === 'log';
        $settings['auth_otp_mail_ready'] = self::mailMailer() === 'log' || self::mailIsConfigured();

        return $settings;
    }

    public static function interpolate(string $template, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $template = str_replace('{'.$key.'}', (string) $value, $template);
        }

        return $template;
    }

    /** @return list<string> */
    public static function paymentGateways(): array
    {
        $raw = (string) self::get('payment_gateways', '');
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/\R|\|/u', $raw) ?: [])));
    }

    public static function enamadImageUrl(): ?string
    {
        return self::imageUrl('enamad_image');
    }

    /** @return array<string, mixed> */
    public static function forShop(): array
    {
        $freeShippingThreshold = self::int('free_shipping_threshold');

        return [
            'name' => self::get('store_name', config('app.name')),
            'tagline' => self::get('tagline'),
            'currency' => self::get('currency'),
            'logoUrl' => self::logoUrl(),
            'faviconUrl' => self::faviconUrl(),
            'faviconMimeType' => self::faviconMimeType(),
            'metaDescription' => self::get('meta_description'),
            'footerDescription' => self::get('footer_description'),
            'contactEmail' => self::get('contact_email'),
            'contactPhone' => self::get('contact_phone'),
            'contactAddress' => self::get('contact_address'),
            'contactHours' => self::get('contact_hours'),
            'socialInstagram' => self::get('social_instagram'),
            'socialTelegram' => self::get('social_telegram'),
            'socialWhatsapp' => self::get('social_whatsapp'),
            'whatsappUrl' => self::whatsappUrl(),
            'mapsUrl' => self::get('maps_url'),
            'aboutContent' => self::get('about_content'),
            'rulesContent' => self::get('rules_content'),
            'returnDays' => self::int('return_days'),
            'minOrderAmount' => self::int('min_order_amount'),
            'freeShippingThreshold' => $freeShippingThreshold,
            'promoBannerTitle' => self::interpolate(self::get('promo_banner_title'), [
                'threshold' => $freeShippingThreshold > 0 ? format_price($freeShippingThreshold) : '',
            ]),
            'promoBannerText' => self::get('promo_banner_text'),
            'newsletterEnabled' => self::bool('newsletter_enabled'),
            'whatsappFloatEnabled' => self::bool('whatsapp_float_enabled'),
            'trustBadges' => self::trustBadges(),
            'homepageCouponBarEnabled' => self::bool('homepage_coupon_bar_enabled'),
            'homepageFeaturedCouponId' => self::int('homepage_featured_coupon_id'),
            'homepageFreeShippingBarEnabled' => self::bool('homepage_free_shipping_bar_enabled'),
            'homepageFlashSaleEnabled' => self::bool('homepage_flash_sale_enabled'),
            'homepageFlashSaleEndsAt' => self::get('homepage_flash_sale_ends_at'),
            'homepageBrandsEnabled' => self::bool('homepage_brands_enabled'),
            'homepageBestsellersEnabled' => self::bool('homepage_bestsellers_enabled'),
            'homepageDiscountedEnabled' => self::bool('homepage_discounted_enabled'),
            'homepageReviewsEnabled' => self::bool('homepage_reviews_enabled'),
            'homepageFaqEnabled' => self::bool('homepage_faq_enabled'),
            'homepageBlogEnabled' => self::bool('homepage_blog_enabled'),
            'homepageRecentlyViewedEnabled' => self::bool('homepage_recently_viewed_enabled'),
            'homepageRecommendationsEnabled' => self::bool('homepage_recommendations_enabled'),
            'homepageQuickTrackEnabled' => self::bool('homepage_quick_track_enabled'),
            'homepageContactSectionEnabled' => self::bool('homepage_contact_section_enabled'),
            'homepageMapEnabled' => self::bool('homepage_map_enabled'),
            'homepageVideoEnabled' => self::bool('homepage_video_enabled'),
            'homepageVideoUrl' => self::get('homepage_video_url'),
            'homepageAppBannerEnabled' => self::bool('homepage_app_banner_enabled'),
            'homepageAppDownloadUrl' => self::get('homepage_app_download_url'),
            'homepageAppDownloadText' => self::get('homepage_app_download_text'),
            'homepagePaymentTrustEnabled' => self::bool('homepage_payment_trust_enabled'),
            'paymentGateways' => self::paymentGateways(),
            'enamadUrl' => self::get('enamad_url'),
            'enamadImageUrl' => self::enamadImageUrl(),
            'socialInstagramEmbed' => self::get('social_instagram_embed'),
            'homepageWhyUsEnabled' => self::bool('homepage_why_us_enabled'),
            'homepageHowItWorksEnabled' => self::bool('homepage_how_it_works_enabled'),
            'homepageCollectionsEnabled' => self::bool('homepage_collections_enabled'),
            'homepageAboutSnippetEnabled' => self::bool('homepage_about_snippet_enabled'),
            'homepageSizeGuideEnabled' => self::bool('homepage_size_guide_enabled'),
            'homepageStickyNavEnabled' => self::bool('homepage_sticky_nav_enabled'),
            'newsletterIncentiveText' => self::get('newsletter_incentive_text'),
        ];
    }
}
