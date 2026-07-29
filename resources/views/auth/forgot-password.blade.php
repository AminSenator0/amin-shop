<x-guest-layout>
    <div class="mb-7">
        <span class="badge mb-3">بازیابی رمز</span>
        <h2 class="text-xl font-black text-shop-text">فراموشی رمز عبور</h2>
        <p class="mt-1.5 text-sm text-shop-muted">ایمیل خود را وارد کنید تا لینک بازیابی رمز برایتان ارسال شود.</p>
    </div>

    <x-auth-session-status class="auth-alert-success" :status="session('status')" />

    @if ($errors->any())
        <div class="auth-alert-error mb-5">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="auth-label">ایمیل</label>
            <div class="relative">
                <span class="auth-input-icon">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@example.com" class="auth-input" />
            </div>
        </div>

        <button type="submit" class="btn-primary w-full !py-3">
            ارسال لینک بازیابی
        </button>

        <p class="text-center text-sm text-shop-muted">
            <a href="{{ route('login') }}" class="font-bold text-shop-primary hover:text-shop-accent">بازگشت به ورود</a>
        </p>
    </form>
</x-guest-layout>
