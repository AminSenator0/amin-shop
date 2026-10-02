<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSettingsRequest;
use App\Models\StoreSetting;
use App\Services\FileUploadService;
use App\Support\StoreSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StoreSettingsController extends Controller
{
    public function __construct(private FileUploadService $uploader) {}

    public function edit(): View|RedirectResponse
    {
        if (request('tab') === 'homepage') {
            return redirect()->route('admin.homepage.index')->withFragment('settings');
        }

        return view('admin.settings.edit', [
            'settings' => StoreSettings::forAdmin(),
        ]);
    }

    public function update(StoreSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $validated) {
            $stringGroups = [
                'appearance' => [
                    'store_name', 'tagline', 'currency', 'primary_color', 'accent_color',
                    'meta_description', 'footer_description',
                ],
                'contact' => [
                    'contact_email', 'contact_phone', 'contact_address', 'contact_hours',
                    'social_instagram', 'social_telegram', 'social_whatsapp', 'maps_url',
                ],
                'content' => ['about_content', 'rules_content'],
                'business' => [
                    'return_days', 'min_order_amount', 'free_shipping_threshold',
                    'promo_banner_title', 'promo_banner_text', 'maintenance_message',
                ],
            ];

            foreach ($stringGroups as $group => $keys) {
                foreach ($keys as $key) {
                    StoreSetting::set($key, $validated[$key] ?? '', $group);
                }
            }

            foreach ([
                'auto_approve_reviews', 'newsletter_enabled', 'whatsapp_float_enabled', 'maintenance_mode',
            ] as $key) {
                StoreSetting::set($key, $request->boolean($key) ? '1' : '0', 'business', 'boolean');
            }

            StoreSetting::set('zarinpal_merchant_id', trim((string) ($validated['zarinpal_merchant_id'] ?? '')), 'payment', 'string');
            StoreSetting::set('zarinpal_sandbox', $request->boolean('zarinpal_sandbox') ? '1' : '0', 'payment', 'boolean');

            $callbackBase = trim((string) ($validated['zarinpal_callback_base_url'] ?? ''));
            StoreSetting::set(
                'zarinpal_callback_base_url',
                $callbackBase === '' ? '' : (StoreSettings::tryNormalizeCallbackBaseUrl($callbackBase) ?? ''),
                'payment',
                'string'
            );

                        // کارت‌های کارت‌به‌کارت: نرمالایز + ذخیره JSON
            $c2cCards = [];
            foreach (($validated['c2c_cards'] ?? []) as $card) {
                $number = preg_replace('/\D+/', '', (string) ($card['number'] ?? ''));
                if ($number === '' || strlen($number) < 16) {
                    continue;
                }
                $c2cCards[] = [
                    'number' => $number,
                    'owner'  => trim((string) ($card['owner'] ?? '')),
                    'bank'   => trim((string) ($card['bank'] ?? '')),
                ];
            }
            StoreSetting::set(
                'c2c_cards',
                json_encode(array_values($c2cCards), JSON_UNESCAPED_UNICODE),
                'payment',
                'json'
            );
            
            $smsDriver = $validated['sms_driver'] ?? 'log';
            StoreSetting::set('sms_driver', $smsDriver, 'sms', 'string');
            StoreSetting::set('sms_mode', $validated['sms_mode'] ?? 'simple', 'sms', 'string');

            // با خروج از حالت تست، کدهای plaintext کش‌شده پاک شوند
            if ($smsDriver !== \App\Services\SmsService::DRIVER_LOG) {
                \App\Services\SmsService::clearRecentLogMessages();
            }
            StoreSetting::set('sms_kavenegar_sender', trim((string) ($validated['sms_kavenegar_sender'] ?? '')), 'sms', 'string');
            StoreSetting::set('sms_meli_auth', $validated['sms_meli_auth'] ?? 'credentials', 'sms', 'string');
            StoreSetting::set('sms_meli_username', trim((string) ($validated['sms_meli_username'] ?? '')), 'sms', 'string');
            StoreSetting::set('sms_meli_from', trim((string) ($validated['sms_meli_from'] ?? '')), 'sms', 'string');

            $apiKeyInput = trim((string) ($validated['sms_kavenegar_api_key'] ?? ''));
            if ($apiKeyInput !== '') {
                StoreSetting::set('sms_kavenegar_api_key', $apiKeyInput, 'sms', 'string');
            } elseif ($request->boolean('sms_clear_api_key')) {
                StoreSetting::set('sms_kavenegar_api_key', '', 'sms', 'string');
            }

            $meliPassword = trim((string) ($validated['sms_meli_password'] ?? ''));
            if ($meliPassword !== '') {
                StoreSetting::set('sms_meli_password', $meliPassword, 'sms', 'string');
            } elseif ($request->boolean('sms_clear_meli_password')) {
                StoreSetting::set('sms_meli_password', '', 'sms', 'string');
            }

            $meliApiKey = trim((string) ($validated['sms_meli_api_key'] ?? ''));
            if ($meliApiKey !== '') {
                StoreSetting::set('sms_meli_api_key', $meliApiKey, 'sms', 'string');
            } elseif ($request->boolean('sms_clear_meli_api_key')) {
                StoreSetting::set('sms_meli_api_key', '', 'sms', 'string');
            }

            foreach (array_keys(\App\Services\SmsService::templateCatalog()) as $key) {
                $kaveField = 'sms_template_'.$key;
                $name = preg_replace('/[^a-zA-Z0-9]/', '', (string) ($validated[$kaveField] ?? '')) ?? '';
                StoreSetting::set($kaveField, $name, 'sms', 'string');

                $meliField = 'sms_meli_body_'.$key;
                $bodyId = preg_replace('/\D+/', '', (string) ($validated[$meliField] ?? '')) ?? '';
                StoreSetting::set($meliField, $bodyId, 'sms', 'string');
            }

            StoreSetting::set('mail_mailer', $validated['mail_mailer'] ?? 'log', 'mail', 'string');
            StoreSetting::set('mail_host', trim((string) ($validated['mail_host'] ?? '')), 'mail', 'string');
            StoreSetting::set('mail_port', (string) ((int) ($validated['mail_port'] ?? 587)), 'mail', 'string');
            StoreSetting::set('mail_username', trim((string) ($validated['mail_username'] ?? '')), 'mail', 'string');
            StoreSetting::set('mail_encryption', $validated['mail_encryption'] ?? 'tls', 'mail', 'string');
            StoreSetting::set('mail_from_address', trim((string) ($validated['mail_from_address'] ?? '')), 'mail', 'string');
            StoreSetting::set('mail_from_name', trim((string) ($validated['mail_from_name'] ?? '')), 'mail', 'string');

            $mailPassword = trim((string) ($validated['mail_password'] ?? ''));
            if ($mailPassword !== '') {
                StoreSetting::set('mail_password', $mailPassword, 'mail', 'string');
            } elseif ($request->boolean('mail_clear_password')) {
                StoreSetting::set('mail_password', '', 'mail', 'string');
            }

            StoreSetting::set('auth_otp_enabled', ! empty($validated['auth_otp_enabled']) ? '1' : '0', 'auth', 'boolean');
            StoreSetting::set('auth_otp_channel', $validated['auth_otp_channel'] ?? 'mobile', 'auth', 'string');

            if (isset($validated['trust_badges'])) {
                StoreSetting::set('trust_badges', json_encode($validated['trust_badges'], JSON_UNESCAPED_UNICODE), 'business', 'json');
            }

            $this->handleImageUpload($request, 'logo', 'remove_logo', 'store_logo', 'appearance');
            $this->handleImageUpload($request, 'favicon', 'remove_favicon', 'store_favicon', 'appearance');
        });

        StoreSettings::publishPublicFavicon();
        StoreSettings::applyMailConfig();

        $activeTab = in_array($request->input('active_tab'), ['identity', 'appearance', 'contact', 'content', 'business', 'payment', 'sms', 'mail', 'auth'], true)
            ? $request->input('active_tab')
            : 'identity';

        return redirect()
            ->route('admin.settings.edit', ['tab' => $activeTab])
            ->with('success', 'تنظیمات فروشگاه ذخیره شد.');
    }

    private function handleImageUpload(StoreSettingsRequest $request, string $field, string $removeField, string $preset, string $group = 'appearance'): void
    {
        if ($request->hasFile($field)) {
            $old = StoreSetting::get($field);
            StoreSetting::set(
                $field,
                $this->uploader->replace($request->file($field), $old ?: null, $preset),
                $group,
                'image'
            );
        } elseif ($request->boolean($removeField)) {
            $old = StoreSetting::get($field);
            if ($old) {
                $this->uploader->delete($old);
            }
            StoreSetting::set($field, '', $group, 'image');
        }
    }
}
