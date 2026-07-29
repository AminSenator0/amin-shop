<x-guest-layout>
    <div class="mb-7">
        <span class="badge mb-3">رمز جدید</span>
        <h2 class="text-xl font-black text-shop-text">تنظیم رمز عبور جدید</h2>
        <p class="mt-1.5 text-sm text-shop-muted">رمز عبور جدید خود را وارد کنید.</p>
    </div>

    @if ($errors->any())
        <div class="auth-alert-error mb-5">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="auth-label">ایمیل</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" class="auth-input !ps-4" />
        </div>

        <div>
            <label for="password" class="auth-label">رمز عبور جدید</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="••••••••" class="auth-input !ps-4" />
        </div>

        <div>
            <label for="password_confirmation" class="auth-label">تکرار رمز عبور</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" class="auth-input !ps-4" />
        </div>

        <button type="submit" class="btn-primary w-full !py-3">ذخیره رمز جدید</button>
    </form>
</x-guest-layout>
