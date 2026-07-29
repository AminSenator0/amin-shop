<header class="sticky top-0 z-50 border-b border-shop-border/60 bg-shop-surface/95 backdrop-blur-md">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-3 px-5 sm:px-6 lg:px-8">
        <div class="flex min-w-0 items-center gap-2 sm:gap-6 lg:gap-8">
            <button type="button" @click="sidebarOpen = true" class="shrink-0 rounded-xl p-2 text-shop-muted hover:bg-shop-background lg:hidden" aria-label="باز کردن منوی حساب">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            </button>
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2.5">
                <x-shop.store-logo />
                <span class="truncate text-lg font-black text-shop-text sm:text-xl">{{ $store['name'] }}</span>
            </a>
            <nav class="hidden items-center gap-6 text-sm lg:flex">
                <a href="{{ route('home') }}" class="nav-link">خانه</a>
                <a href="{{ route('products.index') }}" class="nav-link">محصولات</a>
                <a href="{{ route('orders.track') }}" class="nav-link">پیگیری سفارش</a>
                <a href="{{ route('pages.contact') }}" class="nav-link">تماس</a>
            </nav>
        </div>

        <div class="hidden items-center gap-3 text-sm md:flex">
            <span class="rounded-xl bg-shop-primary/10 px-3 py-2 font-bold text-shop-primary ring-1 ring-shop-primary/20">
                حساب من
            </span>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="rounded-xl px-3 py-2 nav-link">مدیریت</a>
            @endif
        </div>
    </div>

    <div class="border-t border-shop-border/40 bg-shop-primary/[0.04]">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-3 text-xs sm:px-6 sm:text-sm lg:px-8">
            <div class="flex min-w-0 items-center gap-2.5 text-shop-muted">
                <svg class="h-4 w-4 text-shop-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/></svg>
                <span>پنل حساب کاربری</span>
                <span class="text-shop-border">|</span>
                <span class="font-bold text-shop-text">{{ auth()->user()->name }}</span>
            </div>
            <a href="{{ route('home') }}" class="font-bold text-shop-primary hover:text-shop-accent transition-colors">
                بازگشت به {{ $store['name'] }}
            </a>
        </div>
    </div>
</header>
