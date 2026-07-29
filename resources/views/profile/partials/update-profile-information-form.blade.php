<section>
    <header>
        <h2 class="text-lg font-bold text-shop-text">اطلاعات حساب</h2>
        <p class="mt-1 text-sm text-shop-muted">نام، ایمیل و شماره موبایل خود را به‌روزرسانی کنید.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="نام" />
            <x-text-input id="name" name="name" type="text" class="input-shop mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="ایمیل" />
            <x-text-input id="email" name="email" type="email" class="input-shop mt-1 block w-full" dir="ltr" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-shop-text">
                        ایمیل شما تأیید نشده است.
                        <button form="send-verification" class="text-sm text-shop-primary hover:underline">ارسال مجدد لینک تأیید</button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-emerald-600">لینک تأیید جدید به ایمیل شما ارسال شد.</p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="phone" value="شماره موبایل" />
            <x-text-input id="phone" name="phone" type="text" class="input-shop mt-1 block w-full" dir="ltr" data-phone-input :value="old('phone', $user->phone)" autocomplete="tel" placeholder="09121234567" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>ذخیره</x-primary-button>
            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-emerald-600">ذخیره شد.</p>
            @endif
        </div>
    </form>
</section>
