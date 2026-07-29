@extends('layouts.admin')

@section('header', 'سفارشات')

@section('content')
@php
    $filterParams = array_filter([
        'search' => request('search'),
        'status' => request('status'),
        'payment_status' => request('payment_status'),
        'date_from' => request('date_from'),
        'date_to' => request('date_to'),
    ]);
@endphp

<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-admin.stat-card
        label="کل سفارشات"
        :value="$stats['total']"
        color="violet"
        :href="route('admin.orders.index', $filterParams)"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>'
    />
    <x-admin.stat-card
        label="سفارشات جدید"
        :value="$stats['unread']"
        color="amber"
        :href="route('admin.orders.index', array_merge($filterParams, ['unread' => 1]))"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>'
    />
    <x-admin.stat-card
        label="نیاز به اقدام"
        :value="$stats['needs_action']"
        color="rose"
        :href="route('admin.orders.index', array_merge($filterParams, ['needs_action' => 1]))"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
    />
    <x-admin.stat-card
        label="سفارش امروز"
        :value="$stats['today']"
        color="blue"
        :href="route('admin.orders.index', array_merge($filterParams, ['date_from' => today()->toDateString(), 'date_to' => today()->toDateString()]))"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>'
    />
</div>

<div class="admin-page-header">
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <a href="{{ route('admin.orders.create') }}" class="admin-btn-primary text-sm">ثبت سفارش دستی</a>
        <a href="{{ route('admin.orders.export', request()->query()) }}" class="admin-btn-secondary text-sm">خروجی CSV</a>
        @if($stats['unread'] > 0)
            <form method="POST" action="{{ route('admin.orders.mark-all-read') }}" class="ms-auto">
                @csrf @method('PATCH')
                <button type="submit" class="admin-btn-secondary text-sm">علامت‌گذاری همه به‌عنوان خوانده‌شده</button>
            </form>
        @endif
    </div>
    <x-admin.search-form placeholder="جستجو در شماره سفارش، رهگیری، نام، ایمیل یا موبایل...">
        @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        @if(request('payment_status'))
            <input type="hidden" name="payment_status" value="{{ request('payment_status') }}">
        @endif
        @if(request('needs_action'))
            <input type="hidden" name="needs_action" value="1">
        @endif
        @if(request('unread'))
            <input type="hidden" name="unread" value="1">
        @endif
        @if(request('date_from'))
            <input type="hidden" name="date_from" value="{{ request('date_from') }}">
        @endif
        @if(request('date_to'))
            <input type="hidden" name="date_to" value="{{ request('date_to') }}">
        @endif
    </x-admin.search-form>
    <form method="GET" class="admin-status-filter flex flex-wrap items-center gap-2">
        @if(request('search'))
            <input type="hidden" name="search" value="{{ request('search') }}">
        @endif
        @if(request('needs_action'))
            <input type="hidden" name="needs_action" value="1">
        @endif
        @if(request('unread'))
            <input type="hidden" name="unread" value="1">
        @endif
        @if(request('date_from'))
            <input type="hidden" name="date_from" value="{{ request('date_from') }}">
        @endif
        @if(request('date_to'))
            <input type="hidden" name="date_to" value="{{ request('date_to') }}">
        @endif
        <select name="status" class="admin-select" data-auto-submit>
            <option value="">همه وضعیت‌ها</option>
            @foreach(\App\Enums\OrderStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <select name="payment_status" class="admin-select" data-auto-submit>
            <option value="">همه پرداخت‌ها</option>
            @foreach(\App\Enums\PaymentStatus::cases() as $paymentStatus)
                <option value="{{ $paymentStatus->value }}" @selected(request('payment_status') === $paymentStatus->value)>{{ $paymentStatus->label() }}</option>
            @endforeach
        </select>
        @if(request('unread'))
            <a href="{{ route('admin.orders.index', $filterParams) }}" class="admin-btn-secondary text-sm whitespace-nowrap">همه سفارشات</a>
        @else
            <a href="{{ route('admin.orders.index', array_merge($filterParams, ['unread' => 1])) }}" class="admin-btn-primary text-sm whitespace-nowrap relative">
                سفارشات جدید
                @if($stats['unread'] > 0)
                    <span class="absolute -top-1.5 -start-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">{{ $stats['unread'] > 99 ? '99+' : $stats['unread'] }}</span>
                @endif
            </a>
        @endif
    </form>
</div>

<form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
    @if(request('search'))
        <input type="hidden" name="search" value="{{ request('search') }}">
    @endif
    @if(request('status'))
        <input type="hidden" name="status" value="{{ request('status') }}">
    @endif
    @if(request('payment_status'))
        <input type="hidden" name="payment_status" value="{{ request('payment_status') }}">
    @endif
    @if(request('needs_action'))
        <input type="hidden" name="needs_action" value="1">
    @endif
    @if(request('unread'))
        <input type="hidden" name="unread" value="1">
    @endif
    <div>
        <label class="admin-field-label text-xs mb-1 block">از تاریخ</label>
        <input type="text" name="date_from" value="{{ request('date_from') ? format_jalali(request('date_from'), 'Y/m/d', false) : '' }}" data-jalali-date class="admin-input text-sm" dir="ltr" placeholder="۱۴۰۴/۰۱/۰۱" autocomplete="off">
    </div>
    <div>
        <label class="admin-field-label text-xs mb-1 block">تا تاریخ</label>
        <input type="text" name="date_to" value="{{ request('date_to') ? format_jalali(request('date_to'), 'Y/m/d', false) : '' }}" data-jalali-date class="admin-input text-sm" dir="ltr" placeholder="۱۴۰۴/۱۲/۲۹" autocomplete="off">
    </div>
    <button type="submit" class="admin-btn-secondary text-sm">اعمال بازه</button>
    @if(request('date_from') || request('date_to'))
        <a href="{{ route('admin.orders.index', array_filter(array_merge($filterParams, request()->only(['needs_action'])))) }}" class="admin-link text-sm">حذف بازه</a>
    @endif
</form>

@if(request('needs_action'))
    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm text-amber-800">
        نمایش سفارش‌های پرداخت‌شده که هنوز ارسال نشده‌اند.
        <a href="{{ route('admin.orders.index', $filterParams) }}" class="admin-link text-amber-900 font-bold mr-2">نمایش همه</a>
    </div>
@endif

@if(request('unread'))
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm text-amber-800">
        <span class="font-semibold">فقط سفارشات جدید نمایش داده می‌شود.</span>
        <a href="{{ route('admin.orders.index', $filterParams) }}" class="admin-link text-amber-900 font-bold">نمایش همه سفارشات</a>
    </div>
@endif

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>شماره</th>
                <th>مشتری</th>
                <th>مبلغ</th>
                <th>وضعیت</th>
                <th>پرداخت</th>
                <th>تاریخ</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr @class(['bg-amber-50/40' => $order->isUnreadByAdmin()])>
                    <td class="font-bold text-zinc-900">
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('admin.orders.show', $order) }}" class="admin-link">{{ $order->order_number }}</a>
                            @if($order->isUnreadByAdmin())
                                <span class="admin-badge-warning text-[10px] px-2 py-0.5">جدید</span>
                            @endif
                        </div>
                        @if($order->tracking_code)
                            <p class="text-[11px] font-normal text-zinc-400 mt-0.5" dir="ltr">{{ $order->tracking_code }}</p>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.users.show', $order->user) }}" class="group flex items-center gap-2.5 min-w-0" title="مشاهده اطلاعات مشتری">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-sm font-bold text-indigo-600 transition group-hover:bg-indigo-100">
                                {{ mb_substr($order->user->name, 0, 1) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-zinc-900 truncate transition group-hover:text-indigo-600">{{ $order->user->name }}</p>
                                <p class="text-[11px] text-zinc-400 truncate" dir="ltr">{{ $order->user->email }}</p>
                                @if($order->user->phone)
                                    <p class="text-[11px] text-zinc-400" dir="ltr">{{ $order->user->phone }}</p>
                                @endif
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-zinc-300 transition group-hover:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </a>
                    </td>
                    <td class="font-bold">{{ format_price($order->total) }}</td>
                    <td class="whitespace-nowrap"><x-admin.status-badge :status="$order->status" /></td>
                    <td class="whitespace-nowrap"><x-admin.status-badge :status="$order->payment_status" /></td>
                    <td class="text-zinc-500">{{ format_jalali($order->created_at) }}</td>
                    <td>
                        <x-admin.table-actions>
                            <x-admin.table-action type="view" :href="route('admin.orders.show', $order)" title="جزئیات سفارش" />
                            <x-admin.table-action type="user" :href="route('admin.users.show', $order->user)" title="پروفایل مشتری" />
                        </x-admin.table-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-zinc-500">سفارشی یافت نشد.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<x-admin.pagination :paginator="$orders" class="mt-6 rounded-2xl border border-zinc-200/80 bg-white px-4 py-4 shadow-sm sm:px-6" />
@endsection
