<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $adminPageTitle = trim($__env->yieldContent('title')) ?: (trim($__env->yieldContent('header')) ?: 'داشبورد'); @endphp
    <title>{{ $adminPageTitle }} — {{ config('app.name') }}</title>
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/css/form-enhancements.css', 'resources/js/app.js', 'resources/js/admin.js'])
    <x-theme-variables />
</head>
<body class="admin-body font-sans antialiased" style="font-family: Vazirmatn, sans-serif;" x-data="{ sidebarOpen: false, userMenuOpen: false }" @keydown.escape.window="sidebarOpen = false; userMenuOpen = false">
    <div class="flex min-h-screen">

        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="admin-overlay"
             x-cloak></div>

        <x-admin.sidebar />
        {{-- Keeps layout width while sidebar is position:fixed --}}
        <div class="hidden w-[17.5rem] shrink-0 lg:block" aria-hidden="true"></div>

        {{-- Main content --}}
        <div class="flex min-w-0 w-full flex-1 flex-col">
            <header class="admin-header">
                <div class="admin-header-start">
                    <button type="button" @click="sidebarOpen = true" class="admin-header-menu-btn shrink-0 lg:hidden" aria-label="باز کردن منو" :aria-expanded="sidebarOpen">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                    <div class="admin-header-titles">
                        <x-admin.breadcrumb :current="trim($__env->yieldContent('header')) ?: 'داشبورد'" />
                        <h1 class="admin-header-title">@yield('header', 'داشبورد')</h1>
                    </div>
                </div>

                <div class="admin-header-end">
                    <a href="{{ route('home') }}" class="admin-header-icon-btn hidden sm:inline-flex" title="مشاهده فروشگاه">
                        <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                    </a>

                    <div class="admin-header-divider hidden sm:block" aria-hidden="true"></div>

                    <div class="relative" @click.outside="userMenuOpen = false">
                        <button type="button"
                                @click="userMenuOpen = !userMenuOpen"
                                class="admin-user-menu"
                                :class="userMenuOpen && 'admin-user-menu-open'"
                                aria-haspopup="true"
                                :aria-expanded="userMenuOpen">
                            <div class="admin-user-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
                            <div class="hidden sm:block text-start">
                                <p class="text-sm font-semibold text-zinc-900 leading-tight">{{ auth()->user()->name }}</p>
                                <p class="text-[11px] text-zinc-500">مدیر سیستم</p>
                            </div>
                            <svg class="admin-user-chevron hidden sm:block" :class="userMenuOpen && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                        </button>

                        <div x-show="userMenuOpen"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="admin-user-dropdown"
                             x-cloak>
                            <div class="admin-user-dropdown-header">
                                <p class="text-sm font-semibold text-zinc-900">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-zinc-500 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="admin-user-dropdown-divider"></div>
                            <a href="{{ route('home') }}" class="admin-user-dropdown-item">
                                <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                مشاهده فروشگاه
                            </a>
                            <a href="{{ route('admin.settings.edit') }}" class="admin-user-dropdown-item">
                                <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                تنظیمات
                            </a>
                            <div class="admin-user-dropdown-divider"></div>
                            <x-logout-form button-class="admin-user-dropdown-item admin-user-dropdown-item-danger w-full">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                خروج از حساب
                            </x-logout-form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="admin-main">
                @if(session('success'))
                    <x-admin.flash-alert type="success">{{ session('success') }}</x-admin.flash-alert>
                @endif
                @if(session('error'))
                    <x-admin.flash-alert type="error">{{ session('error') }}</x-admin.flash-alert>
                @endif
                @if($unreadOrdersCount > 0 && ! request()->routeIs('admin.orders.*'))
                    <x-admin.unread-orders-alert />
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <x-delete-confirm-modal />

    @stack('scripts')
    <style>[x-cloak] { display: none !important; }</style>
</body>
</html>
