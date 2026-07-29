@extends('layouts.admin')

@section('header', 'کاربران')

@section('content')
@php
    $filterParams = array_filter([
        'search' => request('search'),
        'role' => request('role'),
    ]);
@endphp

<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <x-admin.stat-card
        label="کل کاربران"
        :value="$stats['total']"
        color="violet"
        :href="route('admin.users.index', array_filter(['search' => request('search')]))"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>'
    />
    <x-admin.stat-card
        label="مدیران"
        :value="$stats['admins']"
        color="blue"
        :href="route('admin.users.index', array_merge($filterParams, ['role' => 'admin']))"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
    />
    <x-admin.stat-card
        label="مشتریان"
        :value="$stats['customers']"
        color="emerald"
        :href="route('admin.users.index', array_merge($filterParams, ['role' => 'customer']))"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0" /></svg>'
    />
</div>

<div class="admin-page-header">
    <x-admin.search-form placeholder="جستجوی نام یا ایمیل...">
        @if(request('role'))
            <input type="hidden" name="role" value="{{ request('role') }}">
        @endif
    </x-admin.search-form>
    <div class="flex flex-wrap items-center gap-2">
        <form method="GET" class="admin-status-filter">
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            <select name="role" class="admin-select" data-auto-submit>
                <option value="">همه نقش‌ها</option>
                @foreach(\App\Enums\UserRole::cases() as $role)
                    <option value="{{ $role->value }}" @selected(request('role') === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('admin.users.export', $filterParams) }}" class="admin-btn-secondary text-sm">خروجی CSV</a>
        <a href="{{ route('admin.users.create') }}" class="admin-btn-primary text-sm">کاربر جدید</a>
    </div>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>کاربر</th>
                <th>تلفن</th>
                <th>نقش</th>
                <th>سفارشات</th>
                <th>تاریخ عضویت</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-sm font-bold text-indigo-600">{{ mb_substr($user->name, 0, 1) }}</span>
                            <div class="min-w-0">
                                <a href="{{ route('admin.users.show', $user) }}" class="admin-link font-bold truncate block">{{ $user->name }}</a>
                                <p class="text-xs text-zinc-500 truncate" dir="ltr">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="text-zinc-500" dir="ltr">{{ $user->phone ?? '—' }}</td>
                    <td>
                        @if($user->role === \App\Enums\UserRole::Admin)
                            <span class="admin-badge-indigo">{{ $user->role->label() }}</span>
                        @else
                            <span class="admin-badge-neutral">{{ $user->role->label() }}</span>
                        @endif
                    </td>
                    <td class="font-bold text-zinc-900">{{ $user->orders_count }}</td>
                    <td class="text-zinc-500">{{ format_jalali($user->created_at) }}</td>
                    <td>
                        <x-admin.table-actions>
                            <x-admin.table-action type="view" :href="route('admin.users.show', $user)" title="جزئیات" />
                        </x-admin.table-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="flex flex-col items-center justify-center py-14 text-center">
                            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </div>
                            <p class="font-bold text-zinc-700">کاربری یافت نشد</p>
                            <p class="mt-1 text-sm text-zinc-500">عبارت جستجو را تغییر دهید یا فیلترها را پاک کنید.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<x-admin.pagination :paginator="$users" class="mt-6 rounded-2xl border border-zinc-200/80 bg-white px-4 py-4 shadow-sm sm:px-6" />
@endsection
