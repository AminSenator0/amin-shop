<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\IranMobile;
use App\Services\OtpService;
use App\Support\AuthRedirect;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OtpAuthController extends Controller
{
    public function __construct(private OtpService $otp) {}

    public function sendLogin(Request $request): RedirectResponse
    {
        $this->otp->ensureEnabled();
        $channel = $this->otp->channel();
        $destination = $this->validatedDestination($request, $channel);
        $testMode = $this->otp->shouldExposeDebugCode($channel);

        $user = $this->otp->findUserForLogin($destination);

        if (! $user) {
            // در حالت تست مخفی‌کاری نکن — کاربر باید بفهمد چرا کدی نمی‌بیند
            if ($testMode) {
                $hint = $channel === OtpService::CHANNEL_MOBILE
                    ? 'حسابی با این شماره نیست. برای دمو: 09121000000'
                    : 'حسابی با این ایمیل نیست. برای دمو: customer1@shop.test';

                return back()
                    ->withInput()
                    ->withErrors(['otp' => 'حالت تست: '.$hint]);
            }

            // حالت واقعی: پاسخ خنثی (ضد enumeration)
            usleep(200000);

            $this->persistOtpChallenge([
                'otp_sent' => true,
                'otp_purpose' => OtpService::PURPOSE_LOGIN,
                'otp_destination' => $destination,
                'otp_destination_masked' => $this->otp->maskDestination($channel, $destination),
                'otp_debug_code' => null,
            ]);

            return back()->with(
                'status',
                'اگر حسابی با این مشخصات وجود داشته باشد، کد تایید ارسال شد.'
            );
        }

        $sent = $this->otp->send(OtpService::PURPOSE_LOGIN, $destination);
        $debugCode = $this->safeDebugCode($channel, $sent['debug_code'] ?? null);

        $this->persistOtpChallenge([
            'otp_sent' => true,
            'otp_purpose' => OtpService::PURPOSE_LOGIN,
            'otp_destination' => $destination,
            'otp_destination_masked' => $this->otp->maskDestination($channel, $destination),
            'otp_debug_code' => $debugCode,
        ]);

        $status = $testMode && $debugCode
            ? "حالت تست — کد تایید: {$debugCode}"
            : 'اگر حسابی با این مشخصات وجود داشته باشد، کد تایید ارسال شد.';

        return back()->with('status', $status);
    }

    public function verifyLogin(Request $request): RedirectResponse
    {
        $this->otp->ensureEnabled();
        $channel = $this->otp->channel();
        $destination = $this->validatedDestination($request, $channel);

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [], ['code' => 'کد تایید']);

        $this->otp->verify(OtpService::PURPOSE_LOGIN, $destination, (string) $request->input('code'));

        $user = $this->otp->findUserForLogin($destination);

        if (! $user) {
            throw ValidationException::withMessages([
                'code' => 'کد نامعتبر است یا حسابی یافت نشد.',
            ]);
        }

        $this->clearOtpChallenge();

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return AuthRedirect::afterLogin($user);
    }

    public function sendRegister(Request $request): RedirectResponse
    {
        $this->otp->ensureEnabled();
        $channel = $this->otp->channel();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
        ];
        $attributes = ['name' => 'نام و نام خانوادگی'];

        if ($channel === OtpService::CHANNEL_MOBILE) {
            $rules['phone'] = ['required', new IranMobile, Rule::unique(User::class, 'phone')];
            $rules['email'] = ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')];
            $attributes['phone'] = 'شماره موبایل';
            $attributes['email'] = 'ایمیل';
        } else {
            $rules['email'] = ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')];
            $rules['phone'] = ['nullable', new IranMobile, Rule::unique(User::class, 'phone')];
            $attributes['email'] = 'ایمیل';
            $attributes['phone'] = 'شماره موبایل';
        }

        $validated = $request->validate($rules, [], $attributes);

        if (! empty($validated['phone'])) {
            $validated['phone'] = normalize_mobile($validated['phone']);
        }

        $validated['email'] = strtolower($validated['email']);

        $destination = $channel === OtpService::CHANNEL_MOBILE
            ? $validated['phone']
            : $validated['email'];

        $sent = $this->otp->send(OtpService::PURPOSE_REGISTER, $destination, [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        $this->persistOtpChallenge([
            'otp_sent' => true,
            'otp_purpose' => OtpService::PURPOSE_REGISTER,
            'otp_destination' => $destination,
            'otp_destination_masked' => $this->otp->maskDestination($channel, $destination),
            'otp_name' => $validated['name'],
            'otp_email' => $validated['email'],
            'otp_phone' => $validated['phone'] ?? '',
            'otp_debug_code' => $this->safeDebugCode($channel, $sent['debug_code'] ?? null),
        ]);

        return back()->with('status', 'کد تایید ارسال شد.');
    }

    public function verifyRegister(Request $request): RedirectResponse
    {
        $this->otp->ensureEnabled();
        $channel = $this->otp->channel();
        $destination = $this->validatedDestination($request, $channel);

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [], ['code' => 'کد تایید']);

        $user = DB::transaction(function () use ($request, $channel, $destination) {
            $result = $this->otp->verify(
                OtpService::PURPOSE_REGISTER,
                $destination,
                (string) $request->input('code')
            );

            $payload = $result['payload'];
            $name = trim((string) ($payload['name'] ?? ''));
            $email = strtolower(trim((string) ($payload['email'] ?? '')));
            $phone = $payload['phone'] ?? null;

            if ($name === '' || $email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages([
                    'code' => 'اطلاعات ثبت‌نام ناقص است. دوباره از ابتدا تلاش کنید.',
                ]);
            }

            if (User::query()->where('email', $email)->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'این ایمیل قبلاً ثبت شده است.',
                ]);
            }

            if ($phone && User::query()->where('phone', $phone)->exists()) {
                throw ValidationException::withMessages([
                    'phone' => 'این شماره موبایل قبلاً ثبت شده است.',
                ]);
            }

            return User::create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => Hash::make(Str::password(32)),
                'email_verified_at' => now(),
            ]);
        });

        $this->clearOtpChallenge();

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('user.dashboard');
    }

    private function validatedDestination(Request $request, string $channel): string
    {
        if ($channel === OtpService::CHANNEL_MOBILE) {
            $request->validate([
                'phone' => ['required', new IranMobile],
            ], [], ['phone' => 'شماره موبایل']);

            return $this->otp->normalizeDestination($channel, (string) $request->input('phone'));
        }

        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ], [], ['email' => 'ایمیل']);

        return $this->otp->normalizeDestination($channel, (string) $request->input('email'));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function persistOtpChallenge(array $data): void
    {
        $this->clearOtpChallenge();

        session($data);
    }

    private function clearOtpChallenge(): void
    {
        session()->forget([
            'otp_sent',
            'otp_purpose',
            'otp_destination',
            'otp_destination_masked',
            'otp_debug_code',
            'otp_name',
            'otp_email',
            'otp_phone',
        ]);
    }

    private function safeDebugCode(string $channel, mixed $code): ?string
    {
        if (! $this->otp->shouldExposeDebugCode($channel)) {
            return null;
        }

        if (! is_string($code) || ! preg_match('/^\d{'.OtpService::CODE_LENGTH.'}$/', $code)) {
            return null;
        }

        return $code;
    }
}
