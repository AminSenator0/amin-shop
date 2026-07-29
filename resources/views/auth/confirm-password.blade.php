<x-guest-layout>
    <div class="mb-7">
        <span class="badge mb-3">امنیت</span>
        <h2 class="text-xl font-black text-shop-text">تأیید رمز عبور</h2>
        <p class="mt-1.5 text-sm text-shop-muted">این بخش امن است. لطفاً قبل از ادامه، رمز عبور خود را وارد کنید.</p>
    </div>

    @if ($errors->any())
        <div class="auth-alert-error mb-5">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

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
                    autofocus
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="auth-input"
                />
            </div>
        </div>

        <button type="submit" class="btn-primary w-full !py-3">
            تأیید و ادامه
        </button>
    </form>
</x-guest-layout>
