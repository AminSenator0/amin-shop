<?php

namespace App\Services;

use App\Mail\AuthOtpMail;
use App\Models\AuthOtp;
use App\Models\User;
use App\Support\StoreSettings;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class OtpService
{
    public const PURPOSE_LOGIN = 'login';

    public const PURPOSE_REGISTER = 'register';

    public const CHANNEL_MOBILE = 'mobile';

    public const CHANNEL_EMAIL = 'email';

    public const CODE_LENGTH = 6;

    public const TTL_MINUTES = 5;

    public const MAX_ATTEMPTS = 5;

    public const SEND_DECAY_SECONDS = 60;

    public function __construct(private SmsService $sms) {}

    public function ensureEnabled(): void
    {
        if (! StoreSettings::authOtpEnabled()) {
            throw ValidationException::withMessages([
                'otp' => 'ورود با کد یکبارمصرف فعلاً غیرفعال است.',
            ]);
        }
    }

    public function channel(): string
    {
        return StoreSettings::authOtpChannel();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{destination: string, channel: string, expires_in: int, debug_code: ?string}
     */
    public function send(string $purpose, string $destination, array $payload = [], ?string $throttleKey = null): array
    {
        $this->ensureEnabled();

        $channel = $this->channel();
        $destination = $this->normalizeDestination($channel, $destination);
        $throttleKey = $throttleKey ?: $this->sendThrottleKey($purpose, $channel, $destination);

        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'otp' => "لطفاً {$seconds} ثانیه دیگر دوباره تلاش کنید.",
            ]);
        }

        $code = $this->generateCode();

        DB::transaction(function () use ($channel, $destination, $purpose, $payload, $code) {
            AuthOtp::query()
                ->where('channel', $channel)
                ->where('destination', $destination)
                ->where('purpose', $purpose)
                ->whereNull('consumed_at')
                ->delete();

            AuthOtp::query()->create([
                'channel' => $channel,
                'destination' => $destination,
                'purpose' => $purpose,
                'code_hash' => Hash::make($code),
                'payload' => $payload ?: null,
                'attempts' => 0,
                'expires_at' => now()->addMinutes(self::TTL_MINUTES),
            ]);
        });

        $this->deliver($channel, $destination, $code, $purpose);

        RateLimiter::hit($throttleKey, self::SEND_DECAY_SECONDS);

        $result = [
            'destination' => $this->maskDestination($channel, $destination),
            'channel' => $channel,
            'expires_in' => self::TTL_MINUTES * 60,
            'debug_code' => null,
        ];

        // فقط در حالت تست واقعی (log) — هرگز plaintext را بیرون از این شرط برنگردان
        if ($this->shouldExposeDebugCode($channel)) {
            $result['debug_code'] = $code;
        }

        return $result;
    }

    /**
     * آیا کانال فعلی واقعاً در حالت تست/لاگ است؟
     * فقط همین شرط اجازهٔ نمایش کد را می‌دهد.
     */
    public function shouldExposeDebugCode(?string $channel = null): bool
    {
        $channel ??= $this->channel();

        return match ($channel) {
            self::CHANNEL_MOBILE => StoreSettings::smsDriver() === SmsService::DRIVER_LOG,
            self::CHANNEL_EMAIL => StoreSettings::mailMailer() === 'log',
            default => false,
        };
    }

    /**
     * کد دیباگ امن برای UI: فقط اگر هنوز حالت تست فعال باشد.
     * در غیر این صورت session را پاک می‌کند تا شیطنت/استale بی‌اثر شود.
     */
    public static function viewDebugCode(?string $channel = null): ?string
    {
        $code = session('otp_debug_code');

        if (! is_string($code) || ! preg_match('/^\d{'.self::CODE_LENGTH.'}$/', $code)) {
            session()->forget('otp_debug_code');

            return null;
        }

        $channel ??= StoreSettings::authOtpChannel();
        $allowed = match ($channel) {
            self::CHANNEL_MOBILE => StoreSettings::smsDriver() === SmsService::DRIVER_LOG,
            self::CHANNEL_EMAIL => StoreSettings::mailMailer() === 'log',
            default => false,
        };

        if (! $allowed) {
            session()->forget('otp_debug_code');

            return null;
        }

        return $code;
    }

    /**
     * @return array{otp: AuthOtp, payload: array<string, mixed>}
     */
    public function verify(string $purpose, string $destination, string $code): array
    {
        $this->ensureEnabled();

        $channel = $this->channel();
        $destination = $this->normalizeDestination($channel, $destination);
        $code = trim($code);

        if (! preg_match('/^\d{'.self::CODE_LENGTH.'}$/', $code)) {
            throw ValidationException::withMessages([
                'code' => 'کد واردشده معتبر نیست.',
            ]);
        }

        $otp = AuthOtp::query()
            ->where('channel', $channel)
            ->where('destination', $destination)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if (! $otp || $otp->isExpired()) {
            throw ValidationException::withMessages([
                'code' => 'کد منقضی شده یا یافت نشد. دوباره درخواست کنید.',
            ]);
        }

        if ($otp->attempts >= self::MAX_ATTEMPTS) {
            $otp->update(['consumed_at' => now()]);

            throw ValidationException::withMessages([
                'code' => 'تعداد تلاش‌ها بیش از حد مجاز است. دوباره کد بگیرید.',
            ]);
        }

        if (! Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempts');

            throw ValidationException::withMessages([
                'code' => 'کد واردشده نادرست است.',
            ]);
        }

        $otp->update(['consumed_at' => now()]);

        return [
            'otp' => $otp,
            'payload' => is_array($otp->payload) ? $otp->payload : [],
        ];
    }

    public function findUserForLogin(string $destination): ?User
    {
        $channel = $this->channel();
        $destination = $this->normalizeDestination($channel, $destination);

        $user = match ($channel) {
            self::CHANNEL_MOBILE => User::query()->where('phone', $destination)->first(),
            self::CHANNEL_EMAIL => User::query()->where('email', $destination)->first(),
            default => null,
        };

        if ($user?->isAdmin()) {
            return null;
        }

        return $user;
    }

    public function normalizeDestination(string $channel, string $destination): string
    {
        $destination = trim($destination);

        if ($channel === self::CHANNEL_MOBILE) {
            $normalized = normalize_mobile($destination);

            if (! $normalized) {
                throw ValidationException::withMessages([
                    'phone' => 'شماره موبایل معتبر نیست.',
                ]);
            }

            return $normalized;
        }

        $email = strtolower($destination);

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'email' => 'ایمیل معتبر نیست.',
            ]);
        }

        return $email;
    }

    public function maskDestination(string $channel, string $destination): string
    {
        if ($channel === self::CHANNEL_MOBILE) {
            if (strlen($destination) < 7) {
                return $destination;
            }

            return substr($destination, 0, 4).'***'.substr($destination, -2);
        }

        [$local, $domain] = array_pad(explode('@', $destination, 2), 2, '');
        $visible = substr($local, 0, 2);

        return $visible.'***@'.$domain;
    }

    public function sendThrottleKey(string $purpose, string $channel, string $destination): string
    {
        return 'otp-send:'.$purpose.':'.$channel.':'.$destination;
    }

    private function generateCode(): string
    {
        $testCode = config('auth.otp_test_code');

        if (is_string($testCode) && preg_match('/^\d{'.self::CODE_LENGTH.'}$/', $testCode)) {
            return $testCode;
        }

        return str_pad((string) random_int(0, 999999), self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }

    private function deliver(string $channel, string $destination, string $code, string $purpose): void
    {
        $storeName = (string) StoreSettings::get('store_name', config('app.name'));

        if ($channel === self::CHANNEL_MOBILE) {
            $sent = $this->sms->sendOtp($destination, $code, $storeName);

            if (! $sent) {
                Log::error('Failed to send auth OTP via SMS', ['phone' => $destination]);

                throw ValidationException::withMessages([
                    'otp' => 'ارسال پیامک با خطا مواجه شد. بعداً دوباره تلاش کنید.',
                ]);
            }

            return;
        }

        try {
            Mail::to($destination)->send(new AuthOtpMail($code, $purpose));
        } catch (\Throwable $e) {
            Log::error('Failed to send auth OTP via email', [
                'email' => $destination,
                'message' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'otp' => 'ارسال ایمیل با خطا مواجه شد. بعداً دوباره تلاش کنید.',
            ]);
        }
    }
}
