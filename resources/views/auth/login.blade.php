<x-guest-layout>
    @php
        $otpEnabled = $otpEnabled ?? false;
        $otpChannel = $otpChannel ?? 'mobile';
        $otpSent = (bool) session('otp_sent') && session('otp_purpose') === 'login';
        $defaultMethod = $otpSent ? 'otp' : 'password';
        $otpDebugCode = \App\Services\OtpService::viewDebugCode($otpChannel);
    @endphp

    <div class="mb-7">
        <span class="badge mb-3">ورود</span>
        <h2 class="text-xl font-black text-shop-text">خوش آمدید</h2>
        <p class="mt-1.5 text-sm text-shop-muted">برای ادامه، وارد حساب کاربری خود شوید.</p>
    </div>

    <x-auth-session-status class="auth-alert-success" :status="session('status')" />

    @if ($errors->any())
        <div class="auth-alert-error mb-5">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div
        x-data="{
            method: @js($defaultMethod),
            otpSent: @js((bool) $otpSent),
            cooldown: @js($otpSent ? 60 : 0),
            tick() {
                if (this.cooldown <= 0) return;
                setTimeout(() => { this.cooldown--; this.tick(); }, 1000);
            }
        }"
        x-init="if (cooldown > 0) tick()"
        class="space-y-5"
    >
        @if($otpEnabled)
            <div class="auth-method-switch" role="tablist" aria-label="روش ورود">
                <button type="button" role="tab" class="auth-method-switch__btn" :class="method === 'password' && 'is-active'" :aria-selected="method === 'password'" @click="method = 'password'">رمز عبور</button>
                <button type="button" role="tab" class="auth-method-switch__btn" :class="method === 'otp' && 'is-active'" :aria-selected="method === 'otp'" @click="method = 'otp'">کد یکبارمصرف</button>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5" @if($otpEnabled) x-show="method === 'password'" x-cloak @endif>
            @csrf

            <div>
                <label for="email" class="auth-label">ایمیل</label>
                <div class="relative">
                    <span class="auth-input-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                    </span>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="name@example.com"
                        class="auth-input"
                    />
                </div>
            </div>

            
<div>
    <label for="password" class="auth-label">رمز عبور</label>

    <div class="relative">
        <span class="auth-input-icon">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </span>

        <input
            id="password"
            type="password"
            name="password"
            required
            autocomplete="current-password"
            placeholder="••••••••"
            class="auth-input !pe-12"
        />

        <button
            type="button"
            onclick="togglePasswordVisibility()"
            class="absolute inset-y-0 left-3 flex items-center text-shop-muted hover:text-shop-primary transition-colors"
            aria-label="نمایش رمز عبور"
        >
            <svg id="password-eye-show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6 9.75-6 9.75 6 9.75 6-3.75 6-9.75 6-9.75-6-9.75-6z" />
                <circle cx="12" cy="12" r="2.5" />
            </svg>

            <svg id="password-eye-hide" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.58 10.58a2 2 0 002.84 2.84M9.88 5.09A9.83 9.83 0 0112 4.5c6 0 9.75 6 9.75 6a17.6 17.6 0 01-3.08 3.82M6.23 6.23C3.75 7.9 2.25 10.5 2.25 10.5s3.75 6 9.75 6c1.02 0 1.96-.17 2.82-.44" />
            </svg>
        </button>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const password = document.getElementById('password');
        const showIcon = document.getElementById('password-eye-show');
        const hideIcon = document.getElementById('password-eye-hide');

        if (password.type === 'password') {
            password.type = 'text';
            showIcon.classList.add('hidden');
            hideIcon.classList.remove('hidden');
        } else {
            password.type = 'password';
            hideIcon.classList.add('hidden');
            showIcon.classList.remove('hidden');
        }
    }
</script>



            <div class="flex items-center justify-between gap-3">
                <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2.5">
                    <input id="remember_me" type="checkbox" name="remember" class="auth-checkbox">
                    <span class="text-sm text-shop-muted">مرا به خاطر بسپار</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm font-bold text-shop-primary hover:text-shop-accent" href="{{ route('password.request') }}">
                        فراموشی رمز؟
                    </a>
                @endif
            </div>

            <button type="submit" class="btn-primary w-full !py-3">
                ورود به حساب
            </button>
        </form>

        @if($otpEnabled)
            <div class="space-y-5" x-show="method === 'otp'" @if($defaultMethod === 'otp') style="display: block" @else x-cloak @endif>
                <form method="POST" action="{{ route('login.otp.send') }}" class="space-y-5" x-show="!otpSent" @if($otpSent) style="display: none" @endif>
                    @csrf
                    @if($otpChannel === 'mobile')
                        <div>
                            <label for="otp_login_phone" class="auth-label">شماره موبایل</label>
                            <input id="otp_login_phone" type="text" name="phone" value="{{ old('phone', session('otp_destination')) }}" required data-phone-input autocomplete="tel" placeholder="09121000000" class="auth-input !ps-4" dir="ltr" />
                            @if(\App\Support\StoreSettings::smsDriver() === 'log')
                                <p class="mt-1.5 text-xs text-amber-800">حالت تست — شماره دمو: <span dir="ltr" class="font-mono font-bold">09121000000</span></p>
                            @endif
                        </div>
                    @else
                        <div>
                            <label for="otp_login_email" class="auth-label">ایمیل</label>
                            <input id="otp_login_email" type="email" name="email" value="{{ old('email', session('otp_destination')) }}" required autocomplete="username" placeholder="customer1@shop.test" class="auth-input !ps-4" dir="ltr" />
                            @if(\App\Support\StoreSettings::mailMailer() === 'log')
                                <p class="mt-1.5 text-xs text-amber-800">حالت تست — ایمیل دمو: <span dir="ltr" class="font-mono font-bold">customer1@shop.test</span></p>
                            @endif
                        </div>
                    @endif
                    <button type="submit" class="btn-primary w-full !py-3">ارسال کد تایید</button>
                </form>

                <div class="space-y-5" x-show="otpSent" @if($otpSent) style="display: block" @else x-cloak @endif>
                    <form id="otp-login-verify" method="POST" action="{{ route('login.otp.verify') }}" class="space-y-5">
                        @csrf
                        @if($otpChannel === 'mobile')
                            <input type="hidden" name="phone" value="{{ old('phone', session('otp_destination')) }}">
                        @else
                            <input type="hidden" name="email" value="{{ old('email', session('otp_destination')) }}">
                        @endif

                        <p class="text-sm text-shop-muted">
                            کد به
                            <strong dir="ltr">{{ session('otp_destination_masked') }}</strong>
                            ارسال شد.
                        </p>

                        <x-auth-otp-debug :channel="$otpChannel" />

                        <div>
                            <label for="otp_login_code" class="auth-label">کد ۶ رقمی</label>
                            <input id="otp_login_code" type="text" name="code" inputmode="numeric" maxlength="6" required autofocus autocomplete="one-time-code" placeholder="------" value="{{ old('code', $otpDebugCode ?? '') }}" class="auth-input !ps-4 text-center tracking-[0.4em] font-mono text-lg" dir="ltr" />
                        </div>

                        <label for="remember_otp" class="inline-flex cursor-pointer items-center gap-2.5">
                            <input id="remember_otp" type="checkbox" name="remember" class="auth-checkbox">
                            <span class="text-sm text-shop-muted">مرا به خاطر بسپار</span>
                        </label>

                        <button type="submit" class="btn-primary w-full !py-3">تایید و ورود</button>
                    </form>

                    <form id="otp-login-resend" method="POST" action="{{ route('login.otp.send') }}">
                        @csrf
                        @if($otpChannel === 'mobile')
                            <input type="hidden" name="phone" value="{{ session('otp_destination') }}">
                        @else
                            <input type="hidden" name="email" value="{{ session('otp_destination') }}">
                        @endif
                    </form>

                    <div class="flex items-center justify-between gap-3 text-sm">
                        <button type="button" class="font-bold text-shop-primary" @click="otpSent = false">تغییر {{ $otpChannel === 'mobile' ? 'شماره' : 'ایمیل' }}</button>
                        <button type="submit" form="otp-login-resend" class="font-bold text-shop-muted disabled:opacity-40" :disabled="cooldown > 0">
                            <span x-show="cooldown > 0">ارسال مجدد (<span x-text="cooldown"></span>)</span>
                            <span x-show="cooldown <= 0" x-cloak>ارسال مجدد کد</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if (Route::has('register'))
            <div class="auth-divider">
                <span>یا</span>
            </div>
            <p class="text-center text-sm text-shop-muted">
                حساب ندارید؟
                <a href="{{ route('register') }}" class="font-bold text-shop-primary hover:text-shop-accent">ثبت‌نام رایگان</a>
            </p>
        @endif

        @if (Route::has('admin.login'))
            <p class="text-center text-sm text-shop-muted">
                مدیر هستید؟
                <a href="{{ route('admin.login') }}" class="font-bold text-shop-primary hover:text-shop-accent">ورود به پنل مدیریت</a>
            </p>
        @endif
    </div>
</x-guest-layout>
