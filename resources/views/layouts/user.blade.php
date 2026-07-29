<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'حساب کاربری') — {{ $store['name'] }}</title>
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-theme-variables />
</head>
<body class="font-sans antialiased bg-shop-background text-shop-text" style="font-family: Vazirmatn, sans-serif;" x-data="{ sidebarOpen: false }">
    <x-user.site-header />

    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-50 lg:hidden" @keydown.escape.window="sidebarOpen = false">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="sidebarOpen = false"></div>
        <aside class="absolute inset-y-0 right-0 flex w-80 max-w-[88vw] flex-col overflow-hidden bg-shop-background p-5 shadow-2xl">
            <div class="mb-5 flex shrink-0 items-center justify-between">
                <span class="font-black text-shop-text">منوی حساب کاربری</span>
                <button type="button" @click="sidebarOpen = false" class="rounded-xl p-2 text-shop-muted hover:bg-shop-surface" aria-label="بستن منو">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain">
                <x-user.profile-card />
                <x-user.sidebar />
            </div>
        </aside>
    </div>

    <div class="user-shell">
        <aside class="user-sidebar-sticky">
            <div class="user-sidebar-wrap">
                <x-user.profile-card />
                <x-user.sidebar />
            </div>
        </aside>
        <main class="user-main">
            @if(session('success'))
                <div class="user-flash-success" role="status">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="user-flash-error" role="alert">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @yield('content')
        </main>
    </div>

    <x-shop.site-footer :show-account-links="true" class="!mt-8" />

    <x-shop.whatsapp-float />
    <x-delete-confirm-modal />
    <style>[x-cloak] { display: none !important; }</style>
</body>
</html>
