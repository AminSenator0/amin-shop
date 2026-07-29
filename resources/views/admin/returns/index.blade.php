@extends('layouts.admin')

@section('header', 'مرجوعی‌ها')

@section('content')
@php
    $filterParams = array_filter([
        'search' => request('search'),
    ]);
@endphp

<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-admin.stat-card
        label="کل درخواست‌ها"
        :value="$stats['total']"
        color="violet"
        :href="route('admin.returns.index', $filterParams)"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>'
    />
    <x-admin.stat-card
        label="در انتظار بررسی"
        :value="$stats['pending']"
        color="amber"
        :href="route('admin.returns.index', array_merge($filterParams, ['status' => 'pending']))"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
    />
    <x-admin.stat-card
        label="تأیید شده"
        :value="$stats['approved']"
        color="blue"
        :href="route('admin.returns.index', array_merge($filterParams, ['status' => 'approved']))"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
    />
    <x-admin.stat-card
        label="بازگشت وجه"
        :value="$stats['refunded']"
        color="emerald"
        :href="route('admin.returns.index', array_merge($filterParams, ['status' => 'refunded']))"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
    />
</div>

<div class="admin-page-header">
    <x-admin.search-form placeholder="جستجوی سفارش، مشتری یا دلیل...">
        @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
    </x-admin.search-form>
    <form method="GET" class="admin-status-filter">
        @if(request('search'))
            <input type="hidden" name="search" value="{{ request('search') }}">
        @endif
        <select name="status" class="admin-select" data-auto-submit>
            <option value="">همه وضعیت‌ها</option>
            @foreach(\App\Enums\ReturnStatus::cases() as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        @if(request('status') || request('search'))
            <a href="{{ route('admin.returns.index') }}" class="admin-btn-secondary text-sm whitespace-nowrap">حذف فیلتر</a>
        @endif
    </form>
</div>

<div class="space-y-3">
    @forelse($returns as $return)
        <article class="admin-card !overflow-visible transition-shadow hover:shadow-md {{ $return->status === \App\Enums\ReturnStatus::Pending ? 'ring-1 ring-amber-200' : '' }}">
            <div class="flex flex-col gap-4 p-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0 flex-1 space-y-2">
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                        <a href="{{ route('admin.orders.show', $return->order) }}" class="font-bold text-zinc-900 transition hover:text-indigo-600">
                            سفارش {{ $return->order->order_number }}
                        </a>
                        <x-admin.status-badge :status="$return->status" />
                        <span class="text-xs text-zinc-500">{{ format_jalali($return->created_at) }}</span>
                    </div>
                    <p class="text-sm text-zinc-600">
                        <span class="font-semibold text-zinc-800">{{ $return->order->user->name }}</span>
                        — {{ format_price($return->refund_amount ?? $return->order->total) }}
                    </p>
                    <p class="text-sm text-zinc-700">{{ $return->reason }}</p>
                    @if($return->admin_note)
                        <p class="text-xs text-zinc-500 rounded-lg bg-zinc-50 px-3 py-2">یادداشت مدیر: {{ $return->admin_note }}</p>
                    @endif
                </div>

                <div class="flex shrink-0 flex-col gap-2 sm:flex-row lg:flex-col lg:items-end">
                    <x-admin.table-actions>
                        <x-admin.table-action type="view" :href="route('admin.orders.show', $return->order)" title="مشاهده سفارش" />
                    </x-admin.table-actions>

                    @if($return->status === \App\Enums\ReturnStatus::Pending)
                        <form method="POST" action="{{ route('admin.returns.status', $return) }}" class="w-full sm:w-56 space-y-2 rounded-xl border border-amber-200 bg-amber-50/50 p-3">
                            @csrf @method('PATCH')
                            <p class="text-xs font-bold text-amber-800">اقدام سریع</p>
                            <select name="status" class="admin-select w-full text-xs" required>
                                <option value="approved">تأیید</option>
                                <option value="rejected">رد</option>
                                <option value="refunded">بازگشت وجه</option>
                            </select>
                            <input type="text" name="admin_note" placeholder="یادداشت (اختیاری)..." class="admin-input w-full text-xs">
                            <button type="submit" class="admin-btn-primary w-full text-xs py-2">ثبت تصمیم</button>
                        </form>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <div class="admin-card flex flex-col items-center justify-center py-14 text-center">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
            </div>
            <p class="font-bold text-zinc-700">درخواست مرجوعی یافت نشد</p>
            <p class="mt-1 text-sm text-zinc-500">فیلترها را تغییر دهید یا از صفحه سفارش درخواست جدید ثبت کنید.</p>
        </div>
    @endforelse
</div>

<x-admin.pagination :paginator="$returns" class="mt-6" />
@endsection
