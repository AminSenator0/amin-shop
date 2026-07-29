@extends('layouts.admin')

@section('header', 'پیام‌های تماس')

@section('content')
@php
    $filterParams = array_filter(['search' => request('search')]);
@endphp

<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <x-admin.stat-card
        label="کل پیام‌ها"
        :value="$stats['total']"
        color="violet"
        :href="route('admin.messages.index', $filterParams)"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>'
    />
    <x-admin.stat-card
        label="خوانده‌نشده"
        :value="$stats['unread']"
        color="amber"
        :href="route('admin.messages.index', array_merge($filterParams, ['unread' => 1]))"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
    />
    <x-admin.stat-card
        label="خوانده‌شده"
        :value="$stats['read']"
        color="emerald"
        :href="route('admin.messages.index', array_merge($filterParams, ['read' => 1]))"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
    />
</div>

<div class="admin-page-header">
    <x-admin.search-form placeholder="جستجو در نام، ایمیل، تلفن، موضوع یا متن...">
        @if(request('unread'))
            <input type="hidden" name="unread" value="1">
        @elseif(request('read'))
            <input type="hidden" name="read" value="1">
        @endif
    </x-admin.search-form>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.messages.index', $filterParams) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ !request('unread') && !request('read') ? 'bg-indigo-600 text-white' : 'bg-zinc-100 text-zinc-700' }}">همه</a>
        <a href="{{ route('admin.messages.index', array_merge($filterParams, ['unread' => 1])) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request('unread') ? 'bg-indigo-600 text-white' : 'bg-zinc-100 text-zinc-700' }}">خوانده‌نشده</a>
        <a href="{{ route('admin.messages.index', array_merge($filterParams, ['read' => 1])) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request('read') ? 'bg-indigo-600 text-white' : 'bg-zinc-100 text-zinc-700' }}">خوانده‌شده</a>
        @if($stats['unread'] > 0)
            <form method="POST" action="{{ route('admin.messages.mark-all-read') }}" class="ms-auto">
                @csrf @method('PATCH')
                <button type="submit" class="admin-btn-secondary text-xs">علامت‌گذاری همه به‌عنوان خوانده‌شده</button>
            </form>
        @endif
    </div>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>فرستنده</th>
                <th>موضوع</th>
                <th>خلاصه پیام</th>
                <th>تاریخ</th>
                <th>پاسخ‌ها</th>
                <th>وضعیت</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($messages as $message)
                <tr class="{{ ! $message->is_read ? 'bg-blue-50/60' : '' }}">
                    <td>
                        <p class="font-bold text-zinc-900">{{ $message->name }}</p>
                        <p class="text-xs text-zinc-500" dir="ltr">{{ $message->email }}</p>
                    </td>
                    <td class="max-w-[12rem] truncate font-medium">{{ $message->subject }}</td>
                    <td class="max-w-xs truncate text-zinc-500">{{ $message->excerpt() }}</td>
                    <td class="text-zinc-500 whitespace-nowrap">{{ format_jalali($message->created_at, 'Y/m/d H:i') }}</td>
                    <td class="text-zinc-500">{{ $message->replies_count }}</td>
                    <td>
                        @if($message->is_read)
                            <span class="admin-badge-success">خوانده شده</span>
                        @else
                            <span class="admin-badge-warning">جدید</span>
                        @endif
                    </td>
                    <td>
                        <x-admin.table-actions>
                            <x-admin.table-action type="view" :href="route('admin.messages.show', $message)" />
                            <x-admin.table-action
                                type="delete"
                                :action="route('admin.messages.destroy', $message)"
                                confirm="آیا از حذف این پیام مطمئن هستید؟ این عمل قابل بازگشت نیست."
                            />
                        </x-admin.table-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-zinc-500">پیامی با این فیلترها یافت نشد.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<x-admin.pagination :paginator="$messages" />
@endsection
