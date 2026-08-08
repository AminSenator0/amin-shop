<aside class="admin-sidebar transition-transform duration-300 ease-out"
       :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
       :aria-hidden="window.innerWidth >= 1024 ? false : !sidebarOpen"
       @click.outside="if (window.innerWidth < 1024 && ! $event.target.closest('.admin-header-menu-btn')) sidebarOpen = false">
    <div class="admin-sidebar-pattern" aria-hidden="true"></div>
    <div class="admin-sidebar-glow" aria-hidden="true"></div>
    <div class="admin-sidebar-glow admin-sidebar-glow-secondary" aria-hidden="true"></div>

    <div class="admin-sidebar-brand">
        <div class="admin-sidebar-logo">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-bold text-white">پنل مدیریت</p>
            <p class="truncate text-[11px] text-white/45">{{ \App\Support\StoreSettings::get('store_name', config('app.name')) }}</p>
        </div>
        <button type="button" @click="sidebarOpen = false" class="admin-sidebar-close lg:hidden" aria-label="بستن منو">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    <div class="admin-sidebar-search">
        <svg class="admin-sidebar-search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input type="search" id="admin-nav-search" class="admin-sidebar-search-input" placeholder="جستجو در منو..." autocomplete="off" aria-label="جستجو در منو">
    </div>



    <nav class="admin-nav-scroll" id="admin-nav" aria-label="منوی مدیریت">
        <p class="admin-nav-group-label">اصلی</p>
        <x-admin.nav-item href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')"
            icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>'>
            داشبورد
        </x-admin.nav-item>
        <x-admin.nav-item href="{{ route('admin.orders.index') }}" :active="request()->routeIs('admin.orders.*')" :badge="$unreadOrdersCount > 0 ? $unreadOrdersCount : null"
            icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>'>
            سفارشات
        </x-admin.nav-item>
        <x-admin.nav-item href="{{ route('admin.c2c.index') }}" :active="request()->routeIs('admin.c2c.*')"
            icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m3 0h3m-9.75 0H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>'>
            کارت به کارت
        </x-admin.nav-item>
        <x-admin.nav-item href="{{ route('admin.returns.index') }}" :active="request()->routeIs('admin.returns.*')" :badge="($pendingReturnsCount ?? 0) > 0 ? $pendingReturnsCount : null"
            icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>'>
            مرجوعی‌ها
        </x-admin.nav-item>

        <p class="admin-nav-group-label">مالی</p>
        <x-admin.nav-item href="{{ route('admin.financial.index') }}" :active="request()->routeIs('admin.financial.*')"
            icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>'>
            گزارش مالی
        </x-admin.nav-item>

        <p class="admin-nav-group-label">کاتالوگ</p>
        <x-admin.nav-item href="{{ route('admin.products.index') }}" :active="request()->routeIs('admin.products.*')"
            icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>'>
            محصولات
        </x-admin.nav-item>
        <x-admin.nav-item href="{{ route('admin.categories.index') }}" :active="request()->routeIs('admin.categories.*')"
            icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" /></svg>'>
            دسته‌بندی‌ها
        </x-admin.nav-item>
        <x-admin.nav-item href="{{ route('admin.brands.index') }}" :active="request()->routeIs('admin.brands.*')"
            icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>'>
            برندها
        </x-admin.nav-item>
        <x-admin.nav-item href="{{ route('admin.reviews.index') }}" :active="request()->routeIs('admin.reviews.*')" :badge="($pendingReviewsCount ?? 0) > 0 ? $pendingReviewsCount : null"
            icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>'>
            نظرات
        </x-admin.nav-item>

        <p class="admin-nav-group-label">بازاریابی</p>
        <x-admin.nav-item href="{{ route('admin.coupons.index') }}" :active="request()->routeIs('admin.coupons.*')"
            icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" /></svg>'>
            کدهای تخفیف
        </x-admin.nav-item>
        <x-admin.nav-item href="{{ route('admin.sliders.index') }}" :active="request()->routeIs('admin.sliders.*')"
            icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>'>
            اسلایدرها
        </x-admin.nav-item>
        <x-admin.nav-item href="{{ route('admin.banners.index') }}" :active="request()->routeIs('admin.banners.*')"
            icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" /></svg>'>
            بنرها
        </x-admin.nav-item>

        <p class="admin-nav-group-label">صفحه اصلی</p>
        <x-admin.nav-item href="{{ route('admin.homepage.index') }}" :active="request()->routeIs('admin.homepage.*')"
            icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>'>
            مدیریت صفحه اصلی
        </x-admin.nav-item>
        <x-admin.nav-item href="{{ route('admin.faqs.index') }}" :active="request()->routeIs('admin.faqs.*')"
            icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg>'>
            سوالات متداول
        </x-admin.nav-item>
        <x-admin.nav-item href="{{ route('admin.blog-posts.index') }}" :active="request()->routeIs('admin.blog-posts.*')"
            icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>'>
            مقالات
        </x-admin.nav-item>
        <x-admin.nav-item href="{{ route('admin.newsletter.index') }}" :active="request()->routeIs('admin.newsletter.*')"
            icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>'>
            اعضای خبرنامه
        </x-admin.nav-item>

        <p class="admin-nav-group-label">مشتریان</p>
        <x-admin.nav-item href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.*')"
            icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>'>
            کاربران
        </x-admin.nav-item>
        <x-admin.nav-item href="{{ route('admin.messages.index') }}" :active="request()->routeIs('admin.messages.*')"
            :badge="$unreadMessagesCount > 0 ? $unreadMessagesCount : null"
            icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>'>
            پیام‌ها
        </x-admin.nav-item>

        <p class="admin-nav-group-label mt-6">لاگینگ و امنیت</p>

<x-admin.nav-item
    href="{{ route('admin.audit-logs.dashboard') }}"
    :active="request()->routeIs('admin.audit-logs.dashboard')"
    icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 16v-5m5 5V7m5 9V4" />
    </svg>'
>
    داشبورد لاگ
</x-admin.nav-item>


<x-admin.nav-item
    href="{{ route('admin.audit-logs.index') }}"
    :active="request()->routeIs('admin.audit-logs.index')"
    icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
    </svg>'
>
    لاگ‌ها
</x-admin.nav-item>


<x-admin.nav-item
    href="{{ route('admin.audit-logs.alerts') }}"
    :active="request()->routeIs('admin.audit-logs.alerts')"
    :badge="($unresolvedAlertsCount ?? 0) > 0 ? $unresolvedAlertsCount : null"
    icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
    </svg>'
>
    هشدارها
</x-admin.nav-item>


<x-admin.nav-item
    href="{{ route('admin.audit-logs.blocked-ips') }}"
    :active="request()->routeIs('admin.audit-logs.blocked-ips')"
    icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
    </svg>'
>
    IP های مسدود
</x-admin.nav-item>
        <p class="admin-nav-group-label">تنظیمات</p>
        <x-admin.nav-item href="{{ route('admin.shipping.index') }}" :active="request()->routeIs('admin.shipping.*')"
            icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" /></svg>'>
            روش‌های ارسال
        </x-admin.nav-item>
        <x-admin.nav-item href="{{ route('admin.settings.edit') }}" :active="request()->routeIs('admin.settings.*')"
            icon='<svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>'>
            تنظیمات فروشگاه
        </x-admin.nav-item>
    </nav>

    <div class="admin-sidebar-footer">
        <div class="admin-sidebar-user">
            <div class="admin-sidebar-user-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                <p class="text-[11px] text-white/40">مدیر سیستم</p>
            </div>
            <span class="admin-sidebar-status" title="آنلاین"></span>
        </div>
        <div class="admin-sidebar-footer-actions">
            <a href="{{ route('home') }}" class="admin-sidebar-action">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                <span>فروشگاه</span>
            </a>
            <x-logout-form class="contents" button-class="admin-sidebar-action admin-sidebar-action-danger">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                <span>خروج</span>
            </x-logout-form>
        </div>
    </div>
</aside>
