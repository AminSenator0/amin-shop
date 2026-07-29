<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use App\Support\StoreSettings;
use Illuminate\Database\Seeder;

class StoreSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            'appearance' => ['store_name', 'tagline', 'currency', 'primary_color', 'accent_color', 'meta_description', 'footer_description', 'logo', 'favicon'],
            'contact' => ['contact_email', 'contact_phone', 'contact_address', 'contact_hours', 'social_instagram', 'social_telegram', 'social_whatsapp', 'maps_url'],
            'content' => ['about_content', 'rules_content'],
            'business' => [
                'return_days', 'min_order_amount', 'free_shipping_threshold',
                'promo_banner_title', 'promo_banner_text',
                'auto_approve_reviews', 'newsletter_enabled', 'whatsapp_float_enabled',
                'maintenance_mode', 'maintenance_message', 'trust_badges',
            ],
            'homepage' => [
                'homepage_coupon_bar_enabled', 'homepage_featured_coupon_id', 'homepage_free_shipping_bar_enabled',
                'homepage_flash_sale_enabled', 'homepage_flash_sale_ends_at',
                'homepage_brands_enabled', 'homepage_bestsellers_enabled', 'homepage_discounted_enabled',
                'homepage_reviews_enabled', 'homepage_faq_enabled', 'homepage_blog_enabled',
                'homepage_recently_viewed_enabled', 'homepage_recommendations_enabled',
                'homepage_quick_track_enabled', 'homepage_contact_section_enabled', 'homepage_map_enabled',
                'homepage_video_enabled', 'homepage_video_url', 'homepage_app_banner_enabled',
                'homepage_app_download_url', 'homepage_app_download_text', 'homepage_payment_trust_enabled',
                'payment_gateways', 'enamad_url', 'enamad_image', 'social_instagram_embed', 'homepage_why_us_enabled',
                'homepage_how_it_works_enabled', 'homepage_collections_enabled', 'homepage_about_snippet_enabled',
                'homepage_size_guide_enabled', 'homepage_sticky_nav_enabled', 'newsletter_incentive_text',
            ],
            'payment' => [
                'zarinpal_merchant_id', 'zarinpal_sandbox', 'zarinpal_callback_base_url',
            ],
            'sms' => [
                'sms_driver', 'sms_mode', 'sms_kavenegar_api_key', 'sms_kavenegar_sender',
                'sms_template_order_processing', 'sms_template_order_shipped', 'sms_template_order_delivered',
                'sms_template_message_reply_notice', 'sms_template_message_reply_body', 'sms_template_auth_otp',
                'sms_meli_auth', 'sms_meli_username', 'sms_meli_password', 'sms_meli_api_key', 'sms_meli_from',
                'sms_meli_body_order_processing', 'sms_meli_body_order_shipped', 'sms_meli_body_order_delivered',
                'sms_meli_body_message_reply_notice', 'sms_meli_body_message_reply_body', 'sms_meli_body_auth_otp',
            ],
            'mail' => [
                'mail_mailer', 'mail_host', 'mail_port', 'mail_username', 'mail_password',
                'mail_encryption', 'mail_from_address', 'mail_from_name',
            ],
            'auth' => [
                'auth_otp_enabled', 'auth_otp_channel',
            ],
        ];

        $overrides = [
            'primary_color' => '#16827D',
            'accent_color' => '#2EC4B6',
        ];

        foreach ($groups as $group => $keys) {
            foreach ($keys as $key) {
                if (StoreSetting::where('key', $key)->exists()) {
                    continue;
                }

                $value = $overrides[$key] ?? StoreSettings::DEFAULTS[$key] ?? '';
                $type = in_array($key, ['logo', 'favicon'], true) ? 'image' : 'string';
                if (in_array($key, ['auto_approve_reviews', 'newsletter_enabled', 'whatsapp_float_enabled', 'maintenance_mode', 'zarinpal_sandbox', 'auth_otp_enabled'], true)
                    || str_starts_with($key, 'homepage_') && str_ends_with($key, '_enabled')) {
                    $type = 'boolean';
                }
                if ($key === 'enamad_image') {
                    $type = 'image';
                }
                if ($key === 'trust_badges') {
                    $value = json_encode(StoreSettings::trustBadges(), JSON_UNESCAPED_UNICODE);
                    $type = 'json';
                }

                StoreSetting::set($key, $value, $group, $type);
            }
        }
    }
}
