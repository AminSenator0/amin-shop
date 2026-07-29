<?php

namespace App\Services;

use App\Models\Order;
use App\Support\StoreSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public const DRIVER_LOG = 'log';

    public const DRIVER_KAVENEGAR = 'kavenegar';

    public const DRIVER_MELIPAYAMAK = 'melipayamak';

    public const MODE_SIMPLE = 'simple';

    public const MODE_LOOKUP = 'lookup';

    public const LOG_RECENT_CACHE_KEY = 'sms.log.recent';

    public const LOG_RECENT_LIMIT = 20;

    /**
     * @return array<string, array{
     *   label: string,
     *   description: string,
     *   kavenegar_sample: string,
     *   kavenegar_tokens: list<string>,
     *   meli_sample: string,
     *   meli_tokens: list<string>,
     *   meli_vars: list<string>
     * }>
     */
    public static function templateCatalog(): array
    {
        return [
            'order_processing' => [
                'label' => 'آماده‌سازی سفارش',
                'description' => 'وقتی وضعیت سفارش به «در حال آماده‌سازی» تغییر کند',
                'kavenegar_sample' => 'سفارش %token از فروشگاه %token10 در حال آماده‌سازی است.',
                'kavenegar_tokens' => ['شماره سفارش = token', 'نام فروشگاه = token10'],
                'meli_sample' => 'سفارش {1} از فروشگاه {0} در حال آماده‌سازی است.',
                'meli_tokens' => ['نام فروشگاه = {0}', 'شماره سفارش = {1}'],
                'meli_vars' => ['token10', 'token'],
            ],
            'order_shipped' => [
                'label' => 'ارسال سفارش',
                'description' => 'وقتی سفارش ارسال شود یا کد رهگیری ثبت شود',
                'kavenegar_sample' => 'سفارش %token از فروشگاه %token10 ارسال شد. کد رهگیری %token2 می‌باشد.',
                'kavenegar_tokens' => ['شماره سفارش = token', 'کد رهگیری = token2', 'نام فروشگاه = token10'],
                'meli_sample' => 'سفارش {1} از فروشگاه {0} ارسال شد. کد رهگیری {2} می‌باشد.',
                'meli_tokens' => ['نام فروشگاه = {0}', 'شماره سفارش = {1}', 'کد رهگیری = {2}'],
                'meli_vars' => ['token10', 'token', 'token2'],
            ],
            'order_delivered' => [
                'label' => 'تحویل سفارش',
                'description' => 'وقتی وضعیت سفارش به «تحویل‌شده» تغییر کند',
                'kavenegar_sample' => 'سفارش %token از فروشگاه %token10 تحویل داده شد. از خرید شما سپاسگزاریم.',
                'kavenegar_tokens' => ['شماره سفارش = token', 'نام فروشگاه = token10'],
                'meli_sample' => 'سفارش {1} از فروشگاه {0} تحویل داده شد. از خرید شما سپاسگزاریم.',
                'meli_tokens' => ['نام فروشگاه = {0}', 'شماره سفارش = {1}'],
                'meli_vars' => ['token10', 'token'],
            ],
            'message_reply_notice' => [
                'label' => 'اطلاع پاسخ تیکت',
                'description' => 'وقتی ادمین با کانال «پنل + پیامک اطلاع‌رسانی» پاسخ دهد',
                'kavenegar_sample' => 'فروشگاه %token10 اطلاع می‌دهد: پاسخ جدیدی برای پیام شما ثبت شده. لطفاً به پنل کاربری مراجعه کنید.',
                'kavenegar_tokens' => ['شناسه پیام = token', 'نام فروشگاه = token10'],
                'meli_sample' => 'فروشگاه {0} اطلاع می‌دهد: پاسخ جدیدی برای پیام شما ثبت شده. لطفاً به پنل کاربری مراجعه کنید.',
                'meli_tokens' => ['نام فروشگاه = {0}'],
                'meli_vars' => ['token10'],
            ],
            'message_reply_body' => [
                'label' => 'متن پاسخ پیامکی',
                'description' => 'وقتی ادمین با کانال «پاسخ با پیامک» متن را مستقیم بفرستد',
                'kavenegar_sample' => 'پاسخ فروشگاه %token10: %token20 با سپاس.',
                'kavenegar_tokens' => ['شناسه پیام = token', 'نام فروشگاه = token10', 'متن پاسخ = token20'],
                'meli_sample' => 'پاسخ فروشگاه {0}: {1} با سپاس.',
                'meli_tokens' => ['نام فروشگاه = {0}', 'متن پاسخ = {1}'],
                'meli_vars' => ['token10', 'token20'],
            ],
            'auth_otp' => [
                'label' => 'کد ورود / ثبت‌نام (OTP)',
                'description' => 'ارسال کد یکبارمصرف برای ورود یا ثبت‌نام مشتری',
                'kavenegar_sample' => 'کد تایید شما %token می‌باشد.',
                'kavenegar_tokens' => ['کد OTP = token'],
                'meli_sample' => 'کد تایید شما {0} می‌باشد.',
                'meli_tokens' => ['کد OTP = {0}'],
                'meli_vars' => ['token'],
            ],
        ];
    }

    public function isConfigured(): bool
    {
        return StoreSettings::smsIsConfigured();
    }

    public function driver(): string
    {
        return StoreSettings::smsDriver();
    }

    public function mode(): string
    {
        return StoreSettings::smsMode();
    }

    public function send(string $phone, string $message): bool
    {
        $phone = $this->normalizeReceptor($phone);

        if ($phone === null || trim($message) === '') {
            return false;
        }

        return match ($this->driver()) {
            self::DRIVER_LOG => $this->sendViaLog($phone, $message),
            self::DRIVER_KAVENEGAR => $this->sendViaKavenegarSimple($phone, $message),
            self::DRIVER_MELIPAYAMAK => $this->sendViaMelipayamakSimple($phone, $message),
            default => $this->unknownDriver(),
        };
    }

    public function notifyOrderProcessing(string $phone, Order $order, string $storeName): bool
    {
        $text = "{$storeName}: سفارش {$order->order_number} در حال آماده‌سازی است.";

        return $this->dispatch('order_processing', $phone, $text, [
            'token' => $order->order_number,
            'token10' => $storeName,
        ]);
    }

    public function notifyOrderShipped(string $phone, Order $order, string $storeName): bool
    {
        $tracking = $order->tracking_code ?: '-';
        $text = "{$storeName}: سفارش {$order->order_number} ارسال شد.";
        if ($order->tracking_code) {
            $text .= " کد رهگیری: {$order->tracking_code}";
        }

        return $this->dispatch('order_shipped', $phone, $text, [
            'token' => $order->order_number,
            'token2' => $tracking,
            'token10' => $storeName,
        ]);
    }

    public function notifyOrderDelivered(string $phone, Order $order, string $storeName): bool
    {
        $text = "{$storeName}: سفارش {$order->order_number} تحویل داده شد. از خرید شما سپاسگزاریم.";

        return $this->dispatch('order_delivered', $phone, $text, [
            'token' => $order->order_number,
            'token10' => $storeName,
        ]);
    }

    public function notifyMessageReply(string $phone, string $storeName, int|string $messageId): bool
    {
        $text = "{$storeName}: پاسخ جدیدی برای پیام شما ثبت شده. لطفاً به پنل کاربری خود مراجعه کنید.";

        return $this->dispatch('message_reply_notice', $phone, $text, [
            'token' => (string) $messageId,
            'token10' => $storeName,
        ]);
    }

    public function notifyMessageReplyBody(string $phone, string $storeName, int|string $messageId, string $body): bool
    {
        $body = trim($body);
        $text = "{$storeName}:\n{$body}";

        return $this->dispatch('message_reply_body', $phone, $text, [
            'token' => (string) $messageId,
            'token10' => $storeName,
            'token20' => $body,
        ]);
    }

    public function sendOtp(string $phone, string $code, string $storeName): bool
    {
        $text = "{$storeName}: کد تایید شما {$code} است.";

        return $this->dispatch('auth_otp', $phone, $text, [
            'token' => $code,
        ]);
    }

    /**
     * @param  array<string, string>  $tokens
     */
    private function dispatch(string $templateKey, string $phone, string $fallbackMessage, array $tokens): bool
    {
        $phone = $this->normalizeReceptor($phone);

        if ($phone === null) {
            return false;
        }

        if ($this->driver() === self::DRIVER_LOG) {
            return $this->sendViaLog($phone, $fallbackMessage);
        }

        if (! $this->isConfigured()) {
            Log::error('SMS provider is not configured', ['driver' => $this->driver()]);

            return false;
        }

        if ($this->mode() === self::MODE_LOOKUP) {
            if ($this->driver() === self::DRIVER_KAVENEGAR) {
                $template = StoreSettings::smsTemplate($templateKey);
                if ($template !== '') {
                    return $this->sendViaKavenegarLookup($phone, $template, $tokens);
                }
            }

            if ($this->driver() === self::DRIVER_MELIPAYAMAK) {
                $bodyId = StoreSettings::smsMeliBodyId($templateKey);
                if ($bodyId > 0) {
                    return $this->sendViaMelipayamakPattern($phone, $templateKey, $bodyId, $tokens);
                }
            }

            Log::warning('SMS pattern/template missing; falling back to simple send', [
                'driver' => $this->driver(),
                'template_key' => $templateKey,
            ]);
        }

        return match ($this->driver()) {
            self::DRIVER_KAVENEGAR => $this->sendViaKavenegarSimple($phone, $fallbackMessage),
            self::DRIVER_MELIPAYAMAK => $this->sendViaMelipayamakSimple($phone, $fallbackMessage),
            default => $this->unknownDriver(),
        };
    }

    private function sendViaLog(string $phone, string $message): bool
    {
        Log::info('SMS sent', ['phone' => $phone, 'message' => $message]);
        $this->rememberLogMessage($phone, $message);

        return true;
    }

    /**
     * @return list<array{phone: string, message: string, code: ?string, sent_at: string}>
     */
    public static function recentLogMessages(): array
    {
        $recent = Cache::get(self::LOG_RECENT_CACHE_KEY, []);

        return is_array($recent) ? array_values($recent) : [];
    }

    public static function clearRecentLogMessages(): void
    {
        Cache::forget(self::LOG_RECENT_CACHE_KEY);
    }

    private function rememberLogMessage(string $phone, string $message): void
    {
        $code = null;
        if (preg_match('/\b(\d{6})\b/u', $message, $matches)) {
            $code = $matches[1];
        }

        $entry = [
            'phone' => $phone,
            'message' => $message,
            'code' => $code,
            'sent_at' => now()->toIso8601String(),
        ];

        $recent = self::recentLogMessages();
        array_unshift($recent, $entry);
        $recent = array_slice($recent, 0, self::LOG_RECENT_LIMIT);

        Cache::put(self::LOG_RECENT_CACHE_KEY, $recent, now()->addDays(7));
    }

    private function sendViaKavenegarSimple(string $phone, string $message): bool
    {
        $apiKey = StoreSettings::smsKavenegarApiKey();
        $sender = StoreSettings::smsKavenegarSender();

        if ($apiKey === '' || $sender === '') {
            Log::error('Kavenegar credentials/sender are not configured');

            return false;
        }

        $response = Http::acceptJson()
            ->timeout(30)
            ->get("https://api.kavenegar.com/v1/{$apiKey}/sms/send.json", [
                'receptor' => $phone,
                'sender' => $sender,
                'message' => $message,
            ]);

        return $this->handleKavenegarResponse($response, $phone, 'sms/send');
    }

    /**
     * @param  array<string, string>  $tokens
     */
    private function sendViaKavenegarLookup(string $phone, string $template, array $tokens): bool
    {
        $apiKey = StoreSettings::smsKavenegarApiKey();

        $payload = [
            'receptor' => $phone,
            'template' => $template,
            'token' => $this->sanitizeKavenegarToken($tokens['token'] ?? '0', allowSpaces: false),
        ];

        foreach (['token2', 'token3'] as $key) {
            if (! empty($tokens[$key])) {
                $payload[$key] = $this->sanitizeKavenegarToken($tokens[$key], allowSpaces: false);
            }
        }

        foreach (['token10', 'token20'] as $key) {
            if (! empty($tokens[$key])) {
                $payload[$key] = $this->sanitizeKavenegarToken($tokens[$key], allowSpaces: true);
            }
        }

        $response = Http::acceptJson()
            ->timeout(30)
            ->get("https://api.kavenegar.com/v1/{$apiKey}/verify/lookup.json", $payload);

        return $this->handleKavenegarResponse($response, $phone, 'verify/lookup');
    }

    private function sendViaMelipayamakSimple(string $phone, string $message): bool
    {
        $from = StoreSettings::smsMeliFrom();

        if ($from === '') {
            Log::error('Melipayamak sender line is required for simple SMS send');

            return false;
        }

        if (StoreSettings::smsMeliAuth() === 'api_key') {
            return $this->sendViaMelipayamakConsoleSimple($phone, $from, $message);
        }

        $username = StoreSettings::smsMeliUsername();
        $password = StoreSettings::smsMeliPassword();

        if ($username === '' || $password === '') {
            Log::error('Melipayamak credentials are not configured');

            return false;
        }

        // مستندات رسمی REST: POST https://rest.payamak-panel.com/api/SendSMS/SendSMS
        // طبق مستندات پنل: می‌توان API Key را به‌جای password فرستاد
        $response = Http::asForm()
            ->acceptJson()
            ->timeout(30)
            ->post('https://rest.payamak-panel.com/api/SendSMS/SendSMS', [
                'username' => $username,
                'password' => $password,
                'to' => $phone,
                'from' => $from,
                'text' => $message,
                'isFlash' => 'false',
            ]);

        return $this->handleMelipayamakResponse($response, $phone, 'SendSMS');
    }

    /**
     * @param  array<string, string>  $tokens
     */
    private function sendViaMelipayamakPattern(string $phone, string $templateKey, int $bodyId, array $tokens): bool
    {
        $catalog = self::templateCatalog()[$templateKey] ?? null;
        $varKeys = $catalog['meli_vars'] ?? array_keys($tokens);

        $parts = [];
        foreach ($varKeys as $key) {
            $parts[] = $this->sanitizeMeliVar($tokens[$key] ?? '');
        }

        if (StoreSettings::smsMeliAuth() === 'api_key') {
            return $this->sendViaMelipayamakConsoleShared($phone, $bodyId, $parts);
        }

        $username = StoreSettings::smsMeliUsername();
        $password = StoreSettings::smsMeliPassword();

        if ($username === '' || $password === '') {
            Log::error('Melipayamak credentials are not configured');

            return false;
        }

        // مستندات رسمی REST: POST .../BaseServiceNumber — text = مقادیر متغیرها با ;
        $response = Http::asForm()
            ->acceptJson()
            ->timeout(30)
            ->post('https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber', [
                'username' => $username,
                'password' => $password,
                'to' => $phone,
                'text' => implode(';', $parts),
                'bodyId' => $bodyId,
            ]);

        return $this->handleMelipayamakResponse($response, $phone, 'BaseServiceNumber');
    }

    private function sendViaMelipayamakConsoleSimple(string $phone, string $from, string $message): bool
    {
        $apiKey = StoreSettings::smsMeliApiKey();

        if ($apiKey === '') {
            Log::error('Melipayamak console API key is not configured');

            return false;
        }

        // کنسول RESTFul: POST https://console.melipayamak.com/api/send/simple/{apiKey}
        $response = Http::acceptJson()
            ->asJson()
            ->timeout(30)
            ->post('https://console.melipayamak.com/api/send/simple/'.$apiKey, [
                'from' => $from,
                'to' => $phone,
                'text' => $message,
            ]);

        return $this->handleMelipayamakConsoleResponse($response, $phone, 'send/simple');
    }

    /**
     * @param  list<string>  $args
     */
    private function sendViaMelipayamakConsoleShared(string $phone, int $bodyId, array $args): bool
    {
        $apiKey = StoreSettings::smsMeliApiKey();

        if ($apiKey === '') {
            Log::error('Melipayamak console API key is not configured');

            return false;
        }

        // کنسول RESTFul: POST https://console.melipayamak.com/api/send/shared/{apiKey}
        $response = Http::acceptJson()
            ->asJson()
            ->timeout(30)
            ->post('https://console.melipayamak.com/api/send/shared/'.$apiKey, [
                'to' => $phone,
                'bodyId' => $bodyId,
                'args' => $args,
            ]);

        return $this->handleMelipayamakConsoleResponse($response, $phone, 'send/shared');
    }

    private function handleKavenegarResponse(\Illuminate\Http\Client\Response $response, string $phone, string $endpoint): bool
    {
        $status = (int) data_get($response->json(), 'return.status', $response->status());

        if ($response->successful() && $status === 200) {
            return true;
        }

        Log::error('Kavenegar SMS failed', [
            'endpoint' => $endpoint,
            'phone' => $phone,
            'http_status' => $response->status(),
            'api_status' => $status,
            'message' => data_get($response->json(), 'return.message'),
        ]);

        return false;
    }

    private function handleMelipayamakResponse(\Illuminate\Http\Client\Response $response, string $phone, string $endpoint): bool
    {
        $json = $response->json();
        if (! is_array($json)) {
            $decoded = json_decode($response->body(), true);
            $json = is_array($decoded) ? $decoded : [];
        }

        $retStatus = data_get($json, 'RetStatus');
        $value = data_get($json, 'Value');

        // طبق SDK رسمی: Value = RecId موفق یا کد خطا — RetStatus=1 یعنی Ok
        $ok = $response->successful() && (
            (int) $retStatus === 1
            || (is_numeric($value) && (float) $value > 1000)
        );

        if ($ok) {
            return true;
        }

        Log::error('Melipayamak SMS failed', [
            'endpoint' => $endpoint,
            'phone' => $phone,
            'http_status' => $response->status(),
            'ret_status' => $retStatus,
            'value' => $value,
            'str_status' => data_get($json, 'StrRetStatus'),
        ]);

        return false;
    }

    private function handleMelipayamakConsoleResponse(\Illuminate\Http\Client\Response $response, string $phone, string $endpoint): bool
    {
        $json = $response->json();
        if (! is_array($json)) {
            $decoded = json_decode($response->body(), true);
            $json = is_array($decoded) ? $decoded : [];
        }

        $recId = data_get($json, 'recId');
        $status = data_get($json, 'status');

        // پاسخ کنسول: recId عددی بزرگ = موفق؛ status در صورت خطا پر می‌شود
        $ok = $response->successful() && is_numeric($recId) && (float) $recId > 1000;

        if ($ok) {
            return true;
        }

        Log::error('Melipayamak console SMS failed', [
            'endpoint' => $endpoint,
            'phone' => $phone,
            'http_status' => $response->status(),
            'rec_id' => $recId,
            'status' => $status,
        ]);

        return false;
    }

    private function sanitizeKavenegarToken(string $value, bool $allowSpaces): string
    {
        $value = trim(str_replace(["\r", "\n", "\t", '_'], [' ', ' ', ' ', '-'], $value));

        if ($allowSpaces) {
            $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
            $parts = explode(' ', $value);
            if (count($parts) > 6) {
                $value = implode(' ', array_slice($parts, 0, 6));
            }
        } else {
            $value = preg_replace('/\s+/u', '-', $value) ?? $value;
        }

        return mb_substr($value, 0, 100);
    }

    private function sanitizeMeliVar(string $value): string
    {
        // در text پترن، ; جداکننده است — نباید داخل مقدار باشد
        $value = trim(str_replace([';', "\r", "\n", "\t"], ['،', ' ', ' ', ' '], $value));
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return mb_substr($value, 0, 200);
    }

    private function normalizeReceptor(string $phone): ?string
    {
        $phone = normalize_mobile($phone);

        if ($phone === null || ! preg_match('/^09[0-9]{9}$/', $phone)) {
            return null;
        }

        return $phone;
    }

    private function unknownDriver(): bool
    {
        Log::warning('Unknown SMS driver', ['driver' => $this->driver()]);

        return false;
    }
}
