<x-guest-layout>
    <div class="mb-7">
        <span class="badge mb-3">تأیید ایمیل</span>
        <h2 class="text-xl font-black text-shop-text">ایمیل خود را تأیید کنید</h2>
        <p class="mt-1.5 text-sm leading-7 text-shop-muted">
            ممنون از ثبت‌نام! قبل از شروع، روی لینکی که به ایمیل شما ارسال شده کلیک کنید. اگر ایمیل را دریافت نکردید، می‌توانید دوباره درخواست ارسال کنید.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="auth-alert-success mb-5">
            لینک تأیید جدید به ایمیلی که هنگام ثبت‌نام وارد کردید ارسال شد.
        </div>
    @endif

    <div class="space-y-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn-primary w-full !py-3">
                ارسال مجدد ایمیل تأیید
            </button>
        </form>

        <x-logout-form class="text-center" button-class="text-sm font-medium text-shop-muted transition hover:text-shop-primary" label="خروج از حساب" />
    </div>
</x-guest-layout>
