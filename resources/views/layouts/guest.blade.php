<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ورود') — {{ $store['name'] }}</title>
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-theme-variables />
</head>
<body class="auth-body min-h-screen font-sans antialiased">

    <div class="auth-bg min-h-screen">
        <div class="relative flex min-h-screen flex-col lg:flex-row">
            {{-- پنل معرفی --}}
            <div class="relative hidden overflow-hidden lg:flex lg:w-[44%] lg:flex-col lg:justify-between hero-gradient">
                <div class="auth-panel-pattern absolute inset-0 opacity-50"></div>
                <div class="relative z-10 flex flex-1 flex-col justify-center px-12 py-16">
                    <a href="{{ route('home') }}" class="hero-panel-logo mb-10">
                        <x-shop.store-logo size="lg" variant="hero" />
                    </a>
                    <h1 class="text-3xl font-black leading-tight text-white">
                        {{ $store['name'] }}
                        <span class="mt-2 block text-lg font-medium text-shop-on-hero">{{ $store['tagline'] }}</span>
                    </h1>
                    <p class="mt-5 max-w-sm text-sm leading-7 text-shop-on-hero">
                        با ورود به حساب کاربری، سفارش‌های خود را پیگیری کنید، آدرس‌ها را مدیریت کنید و خرید بعدی را راحت‌تر انجام دهید.
                    </p>
                    <ul class="mt-10 space-y-4">
                        <li class="flex items-center gap-3 text-sm font-medium text-white">
                            <span class="hero-panel-icon">
                                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </span>
                            پیگیری لحظه‌ای وضعیت سفارش
                        </li>
                        <li class="flex items-center gap-3 text-sm font-medium text-white">
                            <span class="hero-panel-icon">
                                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </span>
                            ذخیره آدرس و علاقه‌مندی‌ها
                        </li>
                        <li class="flex items-center gap-3 text-sm font-medium text-white">
                            <span class="hero-panel-icon">
                                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </span>
                            تاریخچه خرید و فاکتورها
                        </li>
                    </ul>
                </div>
                <div class="relative z-10 border-t border-white/30 px-12 py-6">
                    <a href="{{ route('home') }}" class="hero-panel-link link-back">
                        بازگشت به فروشگاه
                        <svg class="icon-arrow-back" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </a>
                </div>
            </div>

            {{-- فرم --}}
            <div class="flex flex-1 items-center justify-center px-4 py-10 sm:px-8">
                <div class="w-full max-w-[420px]">
                    <div class="mb-8 text-center lg:hidden">
                        <a href="{{ route('home') }}" class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-shop-primary text-white shadow-lg shadow-shop-primary/25">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                        </a>
                        <h1 class="text-2xl font-black text-shop-text">{{ $store['name'] }}</h1>
                    </div>

                    <div class="auth-card">
                        {{ $slot }}
                    </div>

                    <div class="mt-6 flex flex-col items-center gap-3 border-t border-shop-border/30 pt-5 text-center text-sm">
                        <a href="{{ route('home') }}" class="link-back text-shop-muted transition hover:text-shop-primary lg:hidden">
                            بازگشت به فروشگاه
                            <svg class="icon-arrow-back" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </a>
                        <!-- @if (Route::has('admin.login'))
                            <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-1.5 text-shop-muted transition hover:text-shop-primary">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                </svg>
                                ورود مدیران
                            </a>
                        @endif -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-delete-confirm-modal />
    <style>[x-cloak] { display: none !important; }</style>
</body>
</html>
