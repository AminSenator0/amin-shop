@extends('layouts.admin')

@section('header', 'داشبورد')

@push('scripts')
    @vite('resources/js/admin-dashboard.js')
@endpush

@section('content')
@php
    $filterParams = array_filter([
        'sales_days' => $filters['sales_days'] ?? 7,
        'visitors_days' => $filters['visitors_days'] ?? 30,
    ]);
    $attentionTotal = collect($attention)->sum('count');
@endphp

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <p id="dashboard-refresh" class="text-xs text-zinc-400" data-refresh-seconds="300">به‌روزرسانی خودکار هر ۵ دقیقه</p>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.products.create') }}" class="admin-btn-primary text-xs">افزودن محصول</a>
        <a href="{{ route('admin.orders.index', ['unread' => 1]) }}" class="admin-btn-secondary text-xs">سفارش‌های جدید</a>
        <a href="{{ route('admin.reviews.index', ['pending' => 1]) }}" class="admin-btn-secondary text-xs">نظرات معلق</a>
        <a href="{{ route('admin.financial.index') }}" class="admin-btn-secondary text-xs">گزارش مالی</a>
        <button type="button" class="admin-btn-secondary text-xs" onclick="document.getElementById('dashboard-export-form')?.classList.toggle('hidden')">خروجی CSV</button>
    </div>
</div>

@if($attentionTotal > 0)
    <div class="admin-alert-warning mb-6 flex flex-wrap items-center gap-2">
        <span class="font-bold shrink-0">نیاز به توجه:</span>
        @foreach($attention as $item)
            @if($item['count'] > 0)
                <a href="{{ $item['href'] }}" class="rounded-lg bg-white/70 px-2.5 py-1 text-xs font-bold text-amber-900 transition hover:bg-white">
                    {{ $item['label'] }} ({{ $item['count'] }})
                </a>
            @endif
        @endforeach
    </div>
@endif

<form id="dashboard-export-form" method="GET" action="{{ route('admin.dashboard.export') }}" class="admin-card mb-6 hidden flex flex-wrap items-end gap-3 p-4">
    <div>
        <label class="admin-field-label text-xs mb-1 block">از تاریخ</label>
        <input type="text" name="date_from" value="{{ format_jalali(today()->subDays(29), 'Y/m/d', false) }}" data-jalali-date class="admin-input text-sm" dir="ltr" autocomplete="off">
    </div>
    <div>
        <label class="admin-field-label text-xs mb-1 block">تا تاریخ</label>
        <input type="text" name="date_to" value="{{ format_jalali(today(), 'Y/m/d', false) }}" data-jalali-date class="admin-input text-sm" dir="ltr" autocomplete="off">
    </div>
    <button type="submit" class="admin-btn-primary text-sm">دانلود CSV سفارشات</button>
</form>

<form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
    <div>
        <label class="admin-field-label text-xs mb-1 block">نمودار فروش</label>
        <select name="sales_days" class="admin-select text-sm" onchange="this.form.submit()">
            @foreach([7 => '۷ روز', 14 => '۱۴ روز', 30 => '۳۰ روز'] as $days => $label)
                <option value="{{ $days }}" @selected(($filters['sales_days'] ?? 7) == $days)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="admin-field-label text-xs mb-1 block">نمودار بازدید</label>
        <select name="visitors_days" class="admin-select text-sm" onchange="this.form.submit()">
            @foreach([7 => '۷ روز', 14 => '۱۴ روز', 30 => '۳۰ روز', 90 => '۹۰ روز'] as $days => $label)
                <option value="{{ $days }}" @selected(($filters['visitors_days'] ?? 30) == $days)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</form>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-6 mb-6">
    <x-admin.stat-card label="سفارش امروز" :value="$stats['orders_today']" color="blue" :href="route('admin.orders.index')"
        :trend="$stats['orders_today_trend']"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>' />
    <x-admin.stat-card label="درآمد امروز" :value="format_price($stats['revenue_today'])" color="emerald"
        :trend="$stats['revenue_today_trend']"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
    <x-admin.stat-card label="درآمد ماه جاری" :value="format_price($stats['revenue_month'])" color="violet"
        :href="route('admin.financial.index')"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>' />
    <x-admin.stat-card label="میانگین سفارش (AOV)" :value="format_price($stats['aov_today'])" color="default"
        subtext="بر اساس سفارشات امروز"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>' />
    <x-admin.stat-card label="نرخ تبدیل" :value="format_number($stats['conversion_rate']).'٪'" color="amber"
        subtext="سفارش امروز ÷ بازدید امروز"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>' />
    <x-admin.stat-card label="بازدید امروز" :value="$stats['visitors_today']" color="default"
        :trend="$stats['visitors_today_trend']"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>' />
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-6 mb-8">
    <x-admin.stat-card label="سفارش خوانده‌نشده" :value="$stats['unread_orders']" color="amber" :href="route('admin.orders.index', ['unread' => 1])"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>' />
    <x-admin.stat-card label="نیاز به اقدام" :value="$stats['needs_action']" color="rose" :href="route('admin.orders.index', ['needs_action' => 1])"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
    <x-admin.stat-card label="در انتظار پرداخت" :value="$stats['pending_payment']" color="amber" :href="route('admin.orders.index', ['payment_status' => 'pending'])"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' />
    <x-admin.stat-card label="نظرات معلق" :value="$stats['pending_reviews']" color="violet" :href="route('admin.reviews.index', ['pending' => 1])"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>' />
    <x-admin.stat-card label="مرجوعی باز" :value="$stats['open_returns']" color="rose" :href="route('admin.returns.index', ['status' => 'pending'])"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>' />
    <x-admin.stat-card label="موجودی کم" :value="$stats['low_stock']" color="rose" :href="route('admin.products.index', ['low_stock' => 1])"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>' />
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-8">
    <x-admin.stat-card label="کل سفارشات" :value="$stats['total_orders']" color="violet" :href="route('admin.orders.index')"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>' />
    <x-admin.stat-card label="محصولات" :value="$stats['total_products']" color="default" :href="route('admin.products.index')"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>' />
    <x-admin.stat-card label="مشتریان" :value="$stats['total_customers']" color="amber" :href="route('admin.users.index')"
        :subtext="'امروز: '.$stats['new_customers_today'].' — هفته: '.$stats['new_customers_week']"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>' />
    <x-admin.stat-card label="خبرنامه" :value="$newsletterStats['total']" color="blue" :href="route('admin.newsletter.index')"
        :subtext="'امروز: '.$newsletterStats['today'].' — هفته: '.$newsletterStats['week']"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>' />
</div>

<div class="grid gap-6 lg:grid-cols-3 mb-8">
    <div class="admin-card lg:col-span-2">
        <div class="admin-card-header">
            <h2 class="admin-card-title">نمودار فروش — {{ $filters['sales_days'] }} روز اخیر</h2>
        </div>
        <div class="p-5">
            <div class="h-72">
                <canvas id="salesChart"
                    data-chart="{{ json_encode([
                        'labels' => $salesChart->pluck('label'),
                        'revenues' => $salesChart->pluck('revenue'),
                        'orders' => $salesChart->pluck('orders'),
                    ]) }}"></canvas>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">پرفروش‌ترین محصولات</h2>
        </div>
        <div class="divide-y divide-zinc-100">
            @forelse($topProducts as $i => $item)
                <div class="flex items-center gap-3 px-5 py-3.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-sm font-black text-indigo-600">{{ $i + 1 }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-zinc-900">{{ $item->product_name }}</p>
                        <p class="text-xs text-zinc-500">{{ $item->total_sold }} فروش — {{ format_price($item->total_revenue) }}</p>
                    </div>
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-zinc-500">هنوز فروشی ثبت نشده است.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2 mb-8">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">وضعیت سفارشات — ۳۰ روز اخیر</h2>
        </div>
        <div class="p-5">
            <div class="h-64">
                <canvas id="orderStatusChart"
                    data-chart="{{ json_encode($orderStatusChart) }}"></canvas>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">وضعیت پرداخت‌ها — ۳۰ روز اخیر</h2>
        </div>
        <div class="p-5">
            <div class="h-64">
                <canvas id="paymentStatusChart"
                    data-chart="{{ json_encode($paymentStatusChart) }}"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2 mb-8">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">فروش ماهانه — ۱۲ ماه اخیر</h2>
        </div>
        <div class="p-5">
            <div class="h-80">
                <canvas id="monthlySalesChart"
                    data-chart="{{ json_encode([
                        'labels' => $monthlySalesChart->pluck('label'),
                        'revenues' => $monthlySalesChart->pluck('revenue'),
                        'orders' => $monthlySalesChart->pluck('orders'),
                    ]) }}"></canvas>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">بازدید روزانه — {{ $filters['visitors_days'] }} روز اخیر</h2>
            <span class="text-xs text-zinc-500">بازدیدکننده یکتا</span>
        </div>
        <div class="p-5">
            <div class="h-80">
                <canvas id="visitorsChart"
                    data-chart="{{ json_encode([
                        'labels' => $visitorsChart->pluck('label'),
                        'visitors' => $visitorsChart->pluck('visitors'),
                    ]) }}"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-3 mb-8">
    <div class="admin-card lg:col-span-2">
        <div class="admin-card-header">
            <h2 class="admin-card-title">آخرین سفارشات</h2>
            <a href="{{ route('admin.orders.index') }}" class="admin-link text-xs">مشاهده همه</a>
        </div>
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>شماره</th>
                        <th>مشتری</th>
                        <th>مبلغ</th>
                        <th>وضعیت</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}" class="admin-link">{{ $order->order_number }}</a></td>
                            <td>{{ $order->user->name }}</td>
                            <td class="font-bold text-zinc-900">{{ format_price($order->total) }}</td>
                            <td class="whitespace-nowrap"><x-admin.status-badge :status="$order->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-zinc-500">هنوز سفارشی ثبت نشده است.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-6">
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">سفارش‌های نیازمند اقدام</h2>
                <a href="{{ route('admin.orders.index', ['needs_action' => 1]) }}" class="admin-link text-xs">همه</a>
            </div>
            <div class="divide-y divide-zinc-100">
                @forelse($actionOrders as $order)
                    <a href="{{ route('admin.orders.show', $order) }}" class="flex flex-col gap-0.5 px-5 py-3.5 transition hover:bg-zinc-50">
                        <p class="truncate text-sm font-bold text-zinc-900">{{ $order->order_number }}</p>
                        <p class="text-xs text-zinc-500">{{ $order->user->name }} — {{ format_price($order->total) }}</p>
                    </a>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-zinc-500">سفارشی نیازمند اقدام نیست.</p>
                @endforelse
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">در انتظار پرداخت</h2>
                <a href="{{ route('admin.orders.index', ['payment_status' => 'pending']) }}" class="admin-link text-xs">همه</a>
            </div>
            <div class="divide-y divide-zinc-100">
                @forelse($pendingPaymentOrders as $order)
                    <a href="{{ route('admin.orders.show', $order) }}" class="flex flex-col gap-0.5 px-5 py-3.5 transition hover:bg-zinc-50">
                        <p class="truncate text-sm font-bold text-zinc-900">{{ $order->order_number }}</p>
                        <p class="text-xs text-zinc-500">{{ $order->user->name }} — {{ format_price($order->total) }}</p>
                    </a>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-zinc-500">سفارش در انتظار پرداخت نیست.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-3 mb-8">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">پیام‌های جدید</h2>
            <a href="{{ route('admin.messages.index', ['unread' => 1]) }}" class="admin-link text-xs">مشاهده همه</a>
        </div>
        <div class="divide-y divide-zinc-100">
            @forelse($unreadMessages as $msg)
                <a href="{{ route('admin.messages.show', $msg) }}" class="flex flex-col gap-0.5 px-5 py-3.5 transition hover:bg-zinc-50">
                    <p class="truncate text-sm font-bold text-zinc-900">{{ $msg->subject }}</p>
                    <p class="text-xs text-zinc-500">{{ $msg->name }} — {{ format_jalali($msg->created_at, 'Y/m/d H:i') }}</p>
                </a>
            @empty
                <p class="px-5 py-8 text-center text-sm text-zinc-500">پیام خوانده‌نشده‌ای وجود ندارد.</p>
            @endforelse
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">نظرات در انتظار تأیید</h2>
            <a href="{{ route('admin.reviews.index', ['pending' => 1]) }}" class="admin-link text-xs">همه</a>
        </div>
        <div class="divide-y divide-zinc-100">
            @forelse($pendingReviews as $review)
                <div class="px-5 py-3.5">
                    <p class="truncate text-sm font-bold text-zinc-900">{{ $review->product?->name }}</p>
                    <p class="text-xs text-zinc-500">{{ $review->user?->name }} — {{ $review->rating }}/۵</p>
                    <div class="mt-2 flex gap-2">
                        <form method="POST" action="{{ route('admin.reviews.approve', $review) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="admin-btn-primary text-[11px] py-1 px-2">تأیید</button>
                        </form>
                        <form method="POST" action="{{ route('admin.reviews.reject', $review) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="admin-btn-secondary text-[11px] py-1 px-2">رد</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-zinc-500">نظر معلقی وجود ندارد.</p>
            @endforelse
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">مرجوعی‌های باز</h2>
            <a href="{{ route('admin.returns.index') }}" class="admin-link text-xs">همه</a>
        </div>
        <div class="divide-y divide-zinc-100">
            @forelse($openReturns as $return)
                <div class="px-5 py-3.5">
                    <p class="truncate text-sm font-bold text-zinc-900">سفارش {{ $return->order?->order_number }}</p>
                    <p class="text-xs text-zinc-500">{{ $return->order?->user?->name }} — <x-admin.status-badge :status="$return->status" /></p>
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-zinc-500">مرجوعی بازی وجود ندارد.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2 mb-8">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">هشدار موجودی کم</h2>
            <a href="{{ route('admin.products.index', ['low_stock' => 1]) }}" class="admin-link text-xs">مشاهده همه</a>
        </div>
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>محصول</th>
                        <th>موجودی</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lowStockProducts as $product)
                        <tr>
                            <td><a href="{{ route('admin.products.edit', $product) }}" class="admin-link">{{ $product->name }}</a></td>
                            <td><span class="admin-badge-danger">{{ $product->stock }} عدد</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="py-8 text-center text-zinc-500">همه محصولات موجودی کافی دارند.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">عملکرد کوپن‌ها</h2>
            <a href="{{ route('admin.coupons.index') }}" class="admin-link text-xs">مدیریت کوپن</a>
        </div>
        <div class="grid grid-cols-2 gap-3 border-b border-zinc-100 px-5 py-4 text-center">
            <div>
                <p class="text-lg font-black text-zinc-900">{{ $couponStats['active'] }}</p>
                <p class="text-xs text-zinc-500">فعال</p>
            </div>
            <div>
                <p class="text-lg font-black text-zinc-900">{{ $couponStats['total_uses'] }}</p>
                <p class="text-xs text-zinc-500">کل استفاده</p>
            </div>
            <div>
                <p class="text-lg font-black text-zinc-900">{{ $couponStats['expired'] }}</p>
                <p class="text-xs text-zinc-500">منقضی</p>
            </div>
            <div>
                <p class="text-lg font-black text-zinc-900">{{ $couponStats['total'] }}</p>
                <p class="text-xs text-zinc-500">کل کوپن‌ها</p>
            </div>
        </div>
        <div class="divide-y divide-zinc-100">
            @forelse($topCoupons as $coupon)
                <div class="flex items-center justify-between gap-3 px-5 py-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold text-zinc-900" dir="ltr">{{ $coupon->code }}</p>
                        <p class="text-xs text-zinc-500">{{ $coupon->statusLabel() }}</p>
                    </div>
                    <span class="shrink-0 text-sm font-black text-indigo-600">{{ $coupon->used_count }} بار</span>
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-zinc-500">کوپنی ثبت نشده است.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2 mb-8">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">مشکلات کاتالوگ</h2>
            <a href="{{ route('admin.products.index') }}" class="admin-link text-xs">مدیریت محصولات</a>
        </div>
        <div class="divide-y divide-zinc-100">
            <div class="flex items-center justify-between px-5 py-3.5">
                <span class="text-sm text-zinc-700">بدون تصویر</span>
                <span class="admin-badge-warning">{{ $catalogIssues['without_image'] }}</span>
            </div>
            <div class="flex items-center justify-between px-5 py-3.5">
                <span class="text-sm text-zinc-700">بدون دسته‌بندی</span>
                <span class="admin-badge-warning">{{ $catalogIssues['without_category'] }}</span>
            </div>
            <div class="flex items-center justify-between px-5 py-3.5">
                <span class="text-sm text-zinc-700">غیرفعال</span>
                <span class="admin-badge-neutral">{{ $catalogIssues['inactive'] }}</span>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">فعالیت‌های اخیر</h2>
        </div>
        <div class="divide-y divide-zinc-100 max-h-80 overflow-y-auto">
            @forelse($recentActivity as $activity)
                <a href="{{ $activity['url'] }}" class="flex flex-col gap-0.5 px-5 py-3.5 transition hover:bg-zinc-50">
                    <p class="truncate text-sm font-bold text-zinc-900">{{ $activity['title'] }}</p>
                    <p class="text-xs text-zinc-500">{{ $activity['subtitle'] }} — {{ format_jalali($activity['at'], 'Y/m/d H:i') }}</p>
                </a>
            @empty
                <p class="px-5 py-8 text-center text-sm text-zinc-500">فعالیتی ثبت نشده است.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
