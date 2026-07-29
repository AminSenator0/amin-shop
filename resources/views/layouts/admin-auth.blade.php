<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ورود مدیر') — {{ config('app.name') }}</title>
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-theme-variables />
</head>
<body class="admin-auth-body font-sans antialiased" style="font-family: Vazirmatn, sans-serif;">
    <div class="admin-auth-bg min-h-screen">
        <div class="relative flex min-h-screen flex-col lg:flex-row">
            {{-- پنل معرفی — سمت راست در RTL --}}
            <div class="relative hidden overflow-hidden hero-gradient lg:flex lg:w-[44%] lg:flex-col lg:justify-between">
                <div class="admin-auth-panel-pattern absolute inset-0 opacity-40"></div>
                <div class="relative z-10 flex flex-1 flex-col justify-center px-12 py-16">
                    <div class="mb-10 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/20">
                        <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold leading-tight text-white">
                        پنل مدیریت
                        <span class="mt-1 block text-lg font-medium text-shop-on-hero">{{ \App\Support\StoreSettings::get('store_name', config('app.name')) }}</span>
                    </h1>
                    <p class="mt-5 max-w-sm text-sm leading-7 text-shop-on-hero">
                        مدیریت محصولات، سفارشات، کاربران و تنظیمات فروشگاه از یک نقطهٔ متمرکز.
                    </p>
                    <ul class="mt-10 space-y-4">
                        <li class="flex items-center gap-3 text-sm font-medium text-white">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15 ring-1 ring-white/20">
                                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </span>
                            داشبورد و گزارش‌های فروش
                        </li>
                        <li class="flex items-center gap-3 text-sm font-medium text-white">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15 ring-1 ring-white/20">
                                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </span>
                            مدیریت موجودی و سفارشات
                        </li>
                        <li class="flex items-center gap-3 text-sm font-medium text-white">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15 ring-1 ring-white/20">
                                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </span>
                            تنظیمات و محتوای فروشگاه
                        </li>
                    </ul>
                </div>
                <div class="relative z-10 border-t border-white/30 px-12 py-6">
                    <p class="text-xs text-shop-on-hero">فقط برای مدیران سیستم — دسترسی محدود</p>
                </div>
            </div>

            {{-- فرم ورود --}}
            <div class="flex flex-1 items-center justify-center px-4 py-12 sm:px-8">
                <div class="w-full max-w-[420px]">
                    <div class="mb-8 lg:hidden">
                        <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-shop-primary text-white">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900">ورود به پنل مدیریت</h1>
                    </div>

                    <div class="admin-auth-card rounded-2xl border border-gray-200/80 bg-white p-8 shadow-xl shadow-gray-900/5">
                        {{ $slot }}
                    </div>

                    <div class="mt-6 flex flex-col items-center gap-3 text-center sm:flex-row sm:justify-between">
                        <a href="{{ route('home') }}" class="link-back text-sm text-gray-500 transition hover:text-gray-900">
                            بازگشت به فروشگاه
                            <svg class="icon-arrow-back" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </a>
                        <a href="{{ route('login') }}" class="text-sm text-gray-500 transition hover:text-gray-900">
                            ورود مشتریان
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-delete-confirm-modal />
    <style>[x-cloak] { display: none !important; }</style>
</body>
</html>
