<?php

namespace App\Http\Requests\Admin;

use App\Rules\IranMobile;
use App\Rules\IranPhone;
use App\Support\StoreSettings;
use App\Support\UploadRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:500'],
            'currency' => ['required', 'string', 'max:50'],
            'color_preset' => ['required', 'string', Rule::in(array_keys(StoreSettings::colorPresets()))],
            'primary_color' => ['required', 'string', 'max:20'],
            'accent_color' => ['required', 'string', 'max:20'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'footer_description' => ['nullable', 'string', 'max:1000'],
            'logo' => UploadRules::file('store_logo'),
            'remove_logo' => ['sometimes', 'boolean'],
            'favicon' => UploadRules::file('store_favicon'),
            'remove_favicon' => ['sometimes', 'boolean'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50', new IranPhone],
            'contact_address' => ['nullable', 'string', 'max:500'],
            'contact_hours' => ['nullable', 'string', 'max:200'],
            'social_instagram' => ['nullable', 'url', 'max:255'],
            'social_telegram' => ['nullable', 'url', 'max:255'],
            'social_whatsapp' => ['nullable', 'string', 'max:20', new IranMobile],
            'maps_url' => ['nullable', 'url', 'max:500'],
            'about_content' => ['nullable', 'string', 'max:10000'],
            'rules_content' => ['nullable', 'string', 'max:10000'],
            'return_days' => ['required', 'integer', 'min:0', 'max:90'],
            'min_order_amount' => ['nullable', 'integer', 'min:0'],
            'free_shipping_threshold' => ['nullable', 'integer', 'min:0'],
            'promo_banner_title' => ['nullable', 'string', 'max:255'],
            'promo_banner_text' => ['nullable', 'string', 'max:500'],
            'auto_approve_reviews' => ['sometimes', 'boolean'],
            'newsletter_enabled' => ['sometimes', 'boolean'],
            'whatsapp_float_enabled' => ['sometimes', 'boolean'],
            'maintenance_mode' => ['sometimes', 'boolean'],
            'maintenance_message' => ['nullable', 'string', 'max:500'],
            'trust_badges' => ['nullable', 'array', 'max:4'],
            'trust_badges.*.title' => ['required_with:trust_badges', 'string', 'max:100'],
            'trust_badges.*.desc' => ['nullable', 'string', 'max:200'],
            'trust_badges.*.icon' => ['nullable', 'string', 'max:2000'],
            'c2c_cards' => ['nullable', 'array', 'max:10'],
            'c2c_cards.*.number' => ['required', 'string', 'max:25', 'regex:/^[\d\s-]+$/'],
            'c2c_cards.*.owner' => ['required', 'string', 'max:100'],
            'c2c_cards.*.bank' => ['nullable', 'string', 'max:50'],
            'zarinpal_merchant_id' => ['nullable', 'string', 'max:36'],
            'zarinpal_sandbox' => ['sometimes', 'boolean'],
            'zarinpal_callback_base_url' => ['nullable', 'string', 'max:255', function (string $attribute, mixed $value, \Closure $fail) {
                $value = trim((string) $value);
                if ($value === '') {
                    return;
                }

                if (StoreSettings::tryNormalizeCallbackBaseUrl($value) === null) {
                    $fail('آدرس دامنهٔ callback معتبر نیست. مثال: https://shop.example.com');
                }
            }],
            'sms_driver' => ['nullable', 'string', Rule::in(['log', 'kavenegar', 'melipayamak'])],
            'sms_mode' => ['nullable', 'string', Rule::in(['simple', 'lookup'])],
            'sms_kavenegar_api_key' => ['nullable', 'string', 'max:200'],
            'sms_clear_api_key' => ['sometimes', 'boolean'],
            'sms_kavenegar_sender' => ['nullable', 'string', 'max:30'],
            'sms_meli_auth' => ['nullable', 'string', Rule::in(['credentials', 'api_key'])],
            'sms_meli_username' => ['nullable', 'string', 'max:100'],
            'sms_meli_password' => ['nullable', 'string', 'max:200'],
            'sms_clear_meli_password' => ['sometimes', 'boolean'],
            'sms_meli_api_key' => ['nullable', 'string', 'max:300'],
            'sms_clear_meli_api_key' => ['sometimes', 'boolean'],
            'sms_meli_from' => ['nullable', 'string', 'max:30'],
            'sms_template_order_processing' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9]*$/'],
            'sms_template_order_shipped' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9]*$/'],
            'sms_template_order_delivered' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9]*$/'],
            'sms_template_message_reply_notice' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9]*$/'],
            'sms_template_message_reply_body' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9]*$/'],
            'sms_template_auth_otp' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z0-9]*$/'],
            'sms_meli_body_order_processing' => ['nullable', 'string', 'max:20', 'regex:/^\d*$/'],
            'sms_meli_body_order_shipped' => ['nullable', 'string', 'max:20', 'regex:/^\d*$/'],
            'sms_meli_body_order_delivered' => ['nullable', 'string', 'max:20', 'regex:/^\d*$/'],
            'sms_meli_body_message_reply_notice' => ['nullable', 'string', 'max:20', 'regex:/^\d*$/'],
            'sms_meli_body_message_reply_body' => ['nullable', 'string', 'max:20', 'regex:/^\d*$/'],
            'sms_meli_body_auth_otp' => ['nullable', 'string', 'max:20', 'regex:/^\d*$/'],
            'mail_mailer' => ['nullable', 'string', Rule::in(['log', 'smtp'])],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_clear_password' => ['sometimes', 'boolean'],
            'mail_encryption' => ['nullable', 'string', Rule::in(['tls', 'ssl', 'none'])],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            'auth_otp_enabled' => ['sometimes', 'boolean'],
            'auth_otp_channel' => ['nullable', 'string', Rule::in(['mobile', 'email'])],
            'active_tab' => ['nullable', 'string', 'in:identity,appearance,contact,content,business,payment,sms,mail,auth'],
        ];
    }

    public function messages(): array
    {
        return [
            'zarinpal_merchant_id.max' => 'مرچنت‌کد زرین‌پال حداکثر ۳۶ کاراکتر است.',
            'zarinpal_callback_base_url.max' => 'آدرس دامنهٔ callback حداکثر ۲۵۵ کاراکتر است.',
            'sms_template_order_processing.regex' => 'نام الگوی کاوه‌نگار فقط حروف و عدد انگلیسی باشد.',
            'sms_template_order_shipped.regex' => 'نام الگوی کاوه‌نگار فقط حروف و عدد انگلیسی باشد.',
            'sms_template_order_delivered.regex' => 'نام الگوی کاوه‌نگار فقط حروف و عدد انگلیسی باشد.',
            'sms_template_message_reply_notice.regex' => 'نام الگوی کاوه‌نگار فقط حروف و عدد انگلیسی باشد.',
            'sms_template_message_reply_body.regex' => 'نام الگوی کاوه‌نگار فقط حروف و عدد انگلیسی باشد.',
            'sms_template_auth_otp.regex' => 'نام الگوی کاوه‌نگار فقط حروف و عدد انگلیسی باشد.',
            'sms_meli_body_order_processing.regex' => 'کد متن ملی‌پیامک فقط عدد است (bodyId).',
            'sms_meli_body_order_shipped.regex' => 'کد متن ملی‌پیامک فقط عدد است (bodyId).',
            'sms_meli_body_order_delivered.regex' => 'کد متن ملی‌پیامک فقط عدد است (bodyId).',
            'sms_meli_body_message_reply_notice.regex' => 'کد متن ملی‌پیامک فقط عدد است (bodyId).',
            'sms_meli_body_message_reply_body.regex' => 'کد متن ملی‌پیامک فقط عدد است (bodyId).',
            'sms_meli_body_auth_otp.regex' => 'کد متن ملی‌پیامک فقط عدد است (bodyId).',
        ];
    }

    protected function prepareForValidation(): void
    {
        // ردیف‌های خالی کارت‌به‌کارت را حذف کن (قبل از validation)
        $cards = $this->input('c2c_cards');
        if (is_array($cards)) {
            $cards = array_values(array_filter($cards, function ($card) {
                return is_array($card) && trim((string) ($card['number'] ?? '')) !== '';
            }));
            $this->merge(['c2c_cards' => $cards]);
        }

        $presetId = $this->input('color_preset', StoreSettings::resolveColorPresetId());
        if (! StoreSettings::isValidColorPresetId($presetId)) {
            $presetId = StoreSettings::defaultColorPresetId();
        }

        $colors = StoreSettings::colorsFromPreset($presetId);

        $this->merge([
            'color_preset' => $presetId,
            'primary_color' => $colors['primary'],
            'accent_color' => $colors['accent'],
            'min_order_amount' => (int) normalize_numeric_string($this->input('min_order_amount', '0')),
            'free_shipping_threshold' => (int) normalize_numeric_string($this->input('free_shipping_threshold', '0')),
            'return_days' => (int) normalize_numeric_string($this->input('return_days', '0')),
            'contact_phone' => normalize_phone($this->input('contact_phone')),
            'social_whatsapp' => normalize_mobile($this->input('social_whatsapp')),
            'zarinpal_merchant_id' => trim((string) $this->input('zarinpal_merchant_id', '')),
            'zarinpal_sandbox' => $this->boolean('zarinpal_sandbox'),
            'zarinpal_callback_base_url' => trim((string) $this->input('zarinpal_callback_base_url', '')),
            'sms_driver' => $this->filled('sms_driver') ? $this->input('sms_driver') : StoreSettings::smsDriver(),
            'sms_mode' => $this->filled('sms_mode') ? $this->input('sms_mode') : StoreSettings::smsMode(),
            'sms_kavenegar_api_key' => trim((string) $this->input('sms_kavenegar_api_key', '')),
            'sms_kavenegar_sender' => $this->has('sms_kavenegar_sender')
                ? trim((string) $this->input('sms_kavenegar_sender', ''))
                : StoreSettings::smsKavenegarSender(),
            'sms_clear_api_key' => $this->boolean('sms_clear_api_key'),
            'sms_meli_auth' => $this->filled('sms_meli_auth')
                ? $this->input('sms_meli_auth')
                : StoreSettings::smsMeliAuth(),
            'sms_meli_username' => $this->has('sms_meli_username')
                ? trim((string) $this->input('sms_meli_username', ''))
                : StoreSettings::smsMeliUsername(),
            'sms_meli_password' => trim((string) $this->input('sms_meli_password', '')),
            'sms_clear_meli_password' => $this->boolean('sms_clear_meli_password'),
            'sms_meli_api_key' => trim((string) $this->input('sms_meli_api_key', '')),
            'sms_clear_meli_api_key' => $this->boolean('sms_clear_meli_api_key'),
            'sms_meli_from' => $this->has('sms_meli_from')
                ? trim((string) $this->input('sms_meli_from', ''))
                : StoreSettings::smsMeliFrom(),
            'mail_mailer' => $this->filled('mail_mailer') ? $this->input('mail_mailer') : StoreSettings::mailMailer(),
            'mail_host' => $this->has('mail_host')
                ? trim((string) $this->input('mail_host', ''))
                : StoreSettings::mailHost(),
            'mail_port' => $this->filled('mail_port')
                ? (int) normalize_numeric_string($this->input('mail_port'))
                : StoreSettings::mailPort(),
            'mail_username' => $this->has('mail_username')
                ? trim((string) $this->input('mail_username', ''))
                : StoreSettings::mailUsername(),
            'mail_password' => trim((string) $this->input('mail_password', '')),
            'mail_clear_password' => $this->boolean('mail_clear_password'),
            'mail_encryption' => $this->filled('mail_encryption') ? $this->input('mail_encryption') : StoreSettings::mailEncryption(),
            'mail_from_address' => $this->has('mail_from_address')
                ? trim((string) $this->input('mail_from_address', ''))
                : StoreSettings::mailFromAddress(),
            'mail_from_name' => $this->has('mail_from_name')
                ? trim((string) $this->input('mail_from_name', ''))
                : StoreSettings::mailFromName(),
            'auth_otp_enabled' => $this->has('auth_otp_enabled')
                ? $this->boolean('auth_otp_enabled')
                : StoreSettings::authOtpEnabled(),
            'auth_otp_channel' => $this->filled('auth_otp_channel')
                ? $this->input('auth_otp_channel')
                : StoreSettings::authOtpChannel(),
        ]);

        foreach (array_keys(\App\Services\SmsService::templateCatalog()) as $key) {
            $kaveField = 'sms_template_'.$key;
            if (! $this->has($kaveField)) {
                $this->merge([$kaveField => StoreSettings::smsTemplate($key)]);
            }

            $meliField = 'sms_meli_body_'.$key;
            if (! $this->has($meliField)) {
                $this->merge([$meliField => (string) StoreSettings::get($meliField, '')]);
            }
        }
    }
}