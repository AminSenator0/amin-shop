<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- Title --}}
    <title>@yield('title', $store['name'])</title>
    
    {{-- Meta Description --}}
    @php
        $pageDescription = trim($__env->yieldContent('meta_description')) ?: ($store['metaDescription'] ?? $store['tagline'] ?? 'فروشگاه آنلاین ' . $store['name']);
    @endphp
    @if($pageDescription)
        <meta name="description" content="{{ $pageDescription }}">
    @endif
    
    {{-- Canonical URL --}}
    @php
        $canonicalUrl = trim($__env->yieldContent('canonical'));
    @endphp
    @if($canonicalUrl)
        <link rel="canonical" href="{{ $canonicalUrl }}">
    @else
        <link rel="canonical" href="{{ url()->current() }}">
    @endif
    
    {{-- Critical Preloads (LCP Optimization) --}}
    @stack('preload')
    
    {{-- Open Graph (Facebook, Telegram, WhatsApp) --}}
    <meta property="og:site_name" content="{{ $store['name'] }}">
    <meta property="og:locale" content="fa_IR">
    @yield('open_graph')
    
    {{-- Twitter Card --}}
    @yield('twitter_card')
    
    {{-- Schema.org JSON-LD --}}
    @yield('schema')
    
    {{-- Pagination prev/next (برای صفحات لیست) --}}
    @yield('pagination_seo')
    
    {{-- Existing components --}}
    <x-favicon />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-theme-variables />
    @stack('head')
</head>
<body x-data="{ mobileMenuOpen: false }" @keydown.escape.window="mobileMenuOpen = false">
    @php
        use App\Support\ShoppingFlow;

        $wishCount = app(\App\Services\WishlistService::class)->count();
        $cartService = app(\App\Services\CartService::class);
        $cartCount = $cartService->count();
        $cartSubtotal = $cartService->subtotal();
        $checkoutStep = ShoppingFlow::currentStep();
        $isShoppingFlow = ShoppingFlow::usesMinimalHeader();
    @endphp

    @if($isShoppingFlow)
        <x-shop.checkout-header :step="$checkoutStep" :cart-count="$cartCount" />
    @else
    <header class="sticky top-0 z-50 overflow-visible border-b border-shop-border/60 bg-shop-surface/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
            <div class="flex min-w-0 items-center gap-2 sm:gap-6 lg:gap-8">
                <button type="button" @click="mobileMenuOpen = true" class="shrink-0 rounded-xl p-2 text-shop-muted hover:bg-shop-background md:hidden" aria-label="باز کردن منو">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                </button>
                <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2.5">
                    <x-shop.store-logo />
                    <span class="truncate text-lg font-black text-shop-text sm:text-xl">{{ $store['name'] }}</span>
                </a>
                <nav class="hidden items-center gap-6 text-sm md:flex">
                    <a href="{{ route('home') }}" class="nav-link">خانه</a>
                    <a href="{{ route('products.index') }}" class="nav-link">محصولات</a>
                    <a href="{{ route('orders.track') }}" class="nav-link">پیگیری سفارش</a>
                    <a href="{{ route('pages.contact') }}" class="nav-link">تماس</a>
                </nav>
            </div>

            {{-- دسکتاپ --}}
            <div class="hidden items-center gap-2 text-sm md:flex">
                <div class="shop-header-group">
                    <a href="{{ auth()->check() ? route('user.wishlist.index') : route('wishlist.index') }}" class="shop-header-group-btn shop-header-group-btn--icon" aria-label="علاقه‌مندی‌ها" data-tooltip="علاقه‌مندی‌ها">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                        @if($wishCount > 0)
                            <span class="shop-header-badge bg-shop-text">{{ $wishCount > 9 ? '9+' : $wishCount }}</span>
                        @endif
                    </a>
                    <span class="shop-header-group-divider" aria-hidden="true"></span>
                    <a href="{{ route('cart.index') }}" class="shop-header-group-btn shop-header-group-btn--icon" aria-label="سبد خرید" data-tooltip="سبد خرید">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5V6a3.75 3.75 0 117.5 0v4.5"/></svg>
                        @if($cartCount > 0)
                            <span class="shop-header-badge bg-shop-accent">{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
                        @endif
                    </a>
                </div>

                @auth
                    <div class="shop-header-group">
                        <a href="{{ route('user.dashboard') }}" class="shop-header-group-btn shop-header-group-btn--icon shop-header-group-btn--account" aria-label="حساب من" data-tooltip="حساب من">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A8.25 8.25 0 004.5 19.5"/></svg>
                        </a>
                        @if(auth()->user()->isAdmin())
                            <span class="shop-header-group-divider" aria-hidden="true"></span>
                            <a href="{{ route('admin.dashboard') }}" class="shop-header-group-btn shop-header-group-btn--icon" aria-label="مدیریت" data-tooltip="مدیریت">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </a>
                        @endif
                        <span class="shop-header-group-divider" aria-hidden="true"></span>
                        <x-logout-form button-class="shop-header-group-btn shop-header-group-btn--icon shop-header-group-btn--logout" tooltip="خروج">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                            <span class="sr-only">خروج</span>
                        </x-logout-form>
                    </div>
                @else
                    <div class="shop-header-group">
                        <a href="{{ route('login') }}" class="shop-header-group-btn shop-header-group-btn--icon" aria-label="ورود" data-tooltip="ورود">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                        </a>
                        <span class="shop-header-group-divider" aria-hidden="true"></span>
                        <a href="{{ route('register') }}" class="shop-header-group-btn shop-header-group-btn--icon shop-header-group-btn--account" aria-label="ثبت‌نام" data-tooltip="ثبت‌نام">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
                        </a>
                    </div>
                @endauth
            </div>

            {{-- موبایل: آیکون سبد و علاقه‌مندی --}}
            <div class="flex shrink-0 items-center gap-1 md:hidden">
                <a href="{{ auth()->check() ? route('user.wishlist.index') : route('wishlist.index') }}" class="relative rounded-xl p-2 text-shop-muted hover:bg-shop-background" aria-label="علاقه‌مندی‌ها">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                    @if($wishCount > 0)
                        <span class="absolute -top-1 -start-1 flex h-4 w-4 items-center justify-center rounded-full bg-shop-text text-[10px] font-bold text-white">{{ $wishCount > 9 ? '9+' : $wishCount }}</span>
                    @endif
                </a>
                <a href="{{ route('cart.index') }}" class="relative rounded-xl p-2 text-shop-muted hover:bg-shop-background" aria-label="سبد خرید">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5V6a3.75 3.75 0 117.5 0v4.5"/></svg>
                    @if($cartCount > 0)
                        <span class="absolute -top-1 -start-1 flex h-4 w-4 items-center justify-center rounded-full bg-shop-accent text-[10px] font-bold text-white">{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </header>

    {{-- منوی موبایل --}}
    @endif

    @if(! $isShoppingFlow)
    <div x-show="mobileMenuOpen" x-cloak class="fixed inset-0 z-[60] md:hidden" @keydown.escape.window="mobileMenuOpen = false">
        <div class="absolute inset-0 bg-black/40" @click="mobileMenuOpen = false"></div>
        <aside class="absolute inset-y-0 right-0 flex w-72 max-w-[85vw] flex-col overflow-y-auto bg-shop-surface shadow-xl"
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-150"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="translate-x-full">
            <div class="flex items-center justify-between border-b border-shop-border/60 px-4 py-4">
                <span class="font-bold text-shop-text">منو</span>
                <button type="button" @click="mobileMenuOpen = false" class="rounded-lg p-1.5 text-shop-muted hover:bg-shop-background" aria-label="بستن منو">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <nav class="flex-1 space-y-1 p-4 text-sm">
                <a href="{{ route('home') }}" @click="mobileMenuOpen = false" class="shop-mobile-nav-link">خانه</a>
                <a href="{{ route('products.index') }}" @click="mobileMenuOpen = false" class="shop-mobile-nav-link">محصولات</a>
                <a href="{{ route('orders.track') }}" @click="mobileMenuOpen = false" class="shop-mobile-nav-link">پیگیری سفارش</a>
                <a href="{{ route('pages.contact') }}" @click="mobileMenuOpen = false" class="shop-mobile-nav-link">تماس با ما</a>
                <a href="{{ route('pages.about') }}" @click="mobileMenuOpen = false" class="shop-mobile-nav-link">درباره ما</a>
                <a href="{{ auth()->check() ? route('user.wishlist.index') : route('wishlist.index') }}" @click="mobileMenuOpen = false" class="shop-mobile-nav-link">
                    علاقه‌مندی‌ها
                    @if($wishCount > 0)
                        <span class="ms-auto rounded-full bg-shop-text px-2 py-0.5 text-[10px] font-bold text-white">{{ $wishCount }}</span>
                    @endif
                </a>
                <a href="{{ route('cart.index') }}" @click="mobileMenuOpen = false" class="shop-mobile-nav-link">
                    سبد خرید
                    @if($cartCount > 0)
                        <span class="ms-auto rounded-full bg-shop-accent px-2 py-0.5 text-[10px] font-bold text-white">{{ $cartCount }}</span>
                    @endif
                </a>
            </nav>
            <div class="border-t border-shop-border/60 p-4 space-y-2">
                @auth
                    <a href="{{ route('user.dashboard') }}" @click="mobileMenuOpen = false" class="shop-mobile-nav-link">حساب من</a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" @click="mobileMenuOpen = false" class="shop-mobile-nav-link">پنل مدیریت</a>
                    @endif
                    <x-logout-form button-class="shop-mobile-nav-link w-full !text-red-600 hover:!bg-red-50" />
                    @else
    <div class="flex items-center gap-2">
        <a href="{{ route('login') }}" @click="mobileMenuOpen = false" class="flex flex-1 items-center justify-center gap-1.5 rounded-xl border-2 border-shop-primary/30 bg-shop-primary/5 px-3 py-2.5 text-sm font-bold text-shop-primary transition hover:bg-shop-primary/10">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
            ورود
        </a>
        <a href="{{ route('register') }}" @click="mobileMenuOpen = false" class="btn-primary flex flex-1 items-center justify-center gap-1.5 !rounded-xl !py-2.5 !text-sm">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
            ثبت‌نام
        </a>
    </div>
@endauth
            </div>
        </aside>
    </div>
    @endif

    @if(session('success'))
        <div class="{{ $isShoppingFlow ? 'shop-toast shop-toast-success' : 'mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8' }}" @if($isShoppingFlow) x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" @endif>
            <div class="{{ $isShoppingFlow ? '' : 'rounded-2xl border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-700' }}">{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="{{ $isShoppingFlow ? 'shop-toast shop-toast-error' : 'mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8' }}" @if($isShoppingFlow) x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" @endif>
            <div class="{{ $isShoppingFlow ? '' : 'rounded-2xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700' }}">{{ session('error') }}</div>
        </div>
    @endif

    <main class="min-h-[60vh] {{ \App\Support\ShoppingFlow::showsCartBar() && $cartCount > 0 ? 'pb-24' : '' }}">
        @yield('content')
    </main>

    @if(\App\Support\ShoppingFlow::showsCartBar() && $cartCount > 0)
        <x-shop.cart-bar :cart-count="$cartCount" :subtotal="$cartSubtotal" />
    @endif

    <x-shop.site-footer />

    <x-shop.whatsapp-float />

    <x-delete-confirm-modal />
    <style>[x-cloak] { display: none !important; }</style>
</body>
</html>
