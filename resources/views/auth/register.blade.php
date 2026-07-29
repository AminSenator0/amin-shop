<x-guest-layout>
    @php
        $otpEnabled = $otpEnabled ?? false;
        $otpChannel = $otpChannel ?? 'mobile';
        $otpSent = (bool) session('otp_sent') && session('otp_purpose') === 'register';
        $defaultMethod = $otpSent ? 'otp' : 'password';
        $otpDebugCode = \App\Services\OtpService::viewDebugCode($otpChannel);
    @endphp

    <div class="mb-7">
        <span class="badge mb-3">ثبت‌نام</span>
        <h2 class="text-xl font-black text-shop-text">ایجاد حساب کاربری</h2>
        <p class="mt-1.5 text-sm text-shop-muted">برای خرید و پیگیری سفارش، حساب جدید بسازید.</p>
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
            <div class="auth-method-switch" role="tablist" aria-label="روش ثبت‌نام">
                <button type="button" role="tab" class="auth-method-switch__btn" :class="method === 'password' && 'is-active'" :aria-selected="method === 'password'" @click="method = 'password'">رمز عبور</button>
                <button type="button" role="tab" class="auth-method-switch__btn" :class="method === 'otp' && 'is-active'" :aria-selected="method === 'otp'" @click="method = 'otp'">کد یکبارمصرف</button>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-4" @if($otpEnabled) x-show="method === 'password'" x-cloak @endif>
            @csrf

            <div>
                <label for="name" class="auth-label">نام و نام خانوادگی</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="auth-input !ps-4" />
            </div>

            <div>
                <label for="email" class="auth-label">ایمیل</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="name@example.com" class="auth-input !ps-4" />
            </div>

            <div>
                <label for="phone" class="auth-label">شماره موبایل <span class="font-normal text-shop-muted">(اختیاری)</span></label>
                <input id="phone" type="text" name="phone" value="{{ old('phone') }}" data-phone-input autocomplete="tel" placeholder="09121234567" class="auth-input !ps-4" dir="ltr" />
                @error('phone')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password" class="auth-label">رمز عبور</label>
                <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" class="auth-input !ps-4" />
            </div>

            <div>
                <label for="password_confirmation" class="auth-label">تکرار رمز عبور</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" class="auth-input !ps-4" />
            </div>

            <button type="submit" class="btn-primary w-full !py-3 !mt-2">
                ایجاد حساب
            </button>
        </form>

        @if($otpEnabled)
            <div class="space-y-5" x-show="method === 'otp'" @if($defaultMethod === 'otp') style="display: block" @else x-cloak @endif>
                <form method="POST" action="{{ route('register.otp.send') }}" class="space-y-4" x-show="!otpSent" @if($otpSent) style="display: none" @endif>
                    @csrf

                    <div>
                        <label for="otp_reg_name" class="auth-label">نام و نام خانوادگی</label>
                        <input id="otp_reg_name" type="text" name="name" value="{{ old('name', session('otp_name')) }}" required autocomplete="name" class="auth-input !ps-4" />
                    </div>

                    @if($otpChannel === 'mobile')
                        <div>
                            <label for="otp_reg_phone" class="auth-label">شماره موبایل</label>
                            <input id="otp_reg_phone" type="text" name="phone" value="{{ old('phone', session('otp_phone', session('otp_destination'))) }}" required data-phone-input autocomplete="tel" placeholder="09121234567" class="auth-input !ps-4" dir="ltr" />
                        </div>
                        <div>
                            <label for="otp_reg_email" class="auth-label">ایمیل</label>
                            <input id="otp_reg_email" type="email" name="email" value="{{ old('email', session('otp_email')) }}" required autocomplete="email" placeholder="name@example.com" class="auth-input !ps-4" dir="ltr" />
                            <p class="mt-1 text-xs text-shop-muted">کد تایید به موبایل ارسال می‌شود؛ ایمیل برای حساب لازم است.</p>
                        </div>
                    @else
                        <div>
                            <label for="otp_reg_email" class="auth-label">ایمیل</label>
                            <input id="otp_reg_email" type="email" name="email" value="{{ old('email', session('otp_email', session('otp_destination'))) }}" required autocomplete="email" placeholder="name@example.com" class="auth-input !ps-4" dir="ltr" />
                            <p class="mt-1 text-xs text-shop-muted">کد تایید به این ایمیل ارسال می‌شود.</p>
                        </div>
                        <div>
                            <label for="otp_reg_phone" class="auth-label">شماره موبایل <span class="font-normal text-shop-muted">(اختیاری)</span></label>
                            <input id="otp_reg_phone" type="text" name="phone" value="{{ old('phone', session('otp_phone')) }}" data-phone-input autocomplete="tel" placeholder="09121234567" class="auth-input !ps-4" dir="ltr" />
                        </div>
                    @endif

                    <button type="submit" class="btn-primary w-full !py-3 !mt-2">ارسال کد تایید</button>
                </form>

                <div class="space-y-5" x-show="otpSent" @if($otpSent) style="display: block" @else x-cloak @endif>
                    <form id="otp-register-verify" method="POST" action="{{ route('register.otp.verify') }}" class="space-y-4">
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
                            <label for="otp_reg_code" class="auth-label">کد ۶ رقمی</label>
                            <input id="otp_reg_code" type="text" name="code" inputmode="numeric" maxlength="6" required autofocus autocomplete="one-time-code" placeholder="------" value="{{ old('code', $otpDebugCode ?? '') }}" class="auth-input !ps-4 text-center tracking-[0.4em] font-mono text-lg" dir="ltr" />
                        </div>

                        <button type="submit" class="btn-primary w-full !py-3">تایید و ایجاد حساب</button>
                    </form>

                    <form id="otp-register-resend" method="POST" action="{{ route('register.otp.send') }}">
                        @csrf
                        <input type="hidden" name="name" value="{{ session('otp_name') }}">
                        <input type="hidden" name="email" value="{{ session('otp_email') }}">
                        <input type="hidden" name="phone" value="{{ session('otp_phone') }}">
                    </form>

                    <div class="flex items-center justify-between gap-3 text-sm">
                        <button type="button" class="font-bold text-shop-primary" @click="otpSent = false">ویرایش اطلاعات</button>
                        <button type="submit" form="otp-register-resend" class="font-bold text-shop-muted disabled:opacity-40" :disabled="cooldown > 0">
                            <span x-show="cooldown > 0">ارسال مجدد (<span x-text="cooldown"></span>)</span>
                            <span x-show="cooldown <= 0" x-cloak>ارسال مجدد کد</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <p class="text-center text-sm text-shop-muted">
            قبلاً ثبت‌نام کرده‌اید؟
            <a href="{{ route('login') }}" class="font-bold text-shop-primary hover:text-shop-accent">ورود</a>
        </p>
    </div>
</x-guest-layout>
