@extends('layouts.admin')

@section('header', 'اعضای خبرنامه')

@section('content')
@php
    $filterParams = array_filter(['search' => request('search')]);
@endphp

<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <x-admin.stat-card
        label="کل اعضا"
        :value="$stats['total']"
        color="violet"
        :href="route('admin.newsletter.index', $filterParams)"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>'
    />
    <x-admin.stat-card
        label="عضویت امروز"
        :value="$stats['today']"
        color="emerald"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>'
    />
    <x-admin.stat-card
        label="این ماه"
        :value="$stats['this_month']"
        color="blue"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>'
    />
</div>

<div class="admin-page-header">
    <x-admin.search-form placeholder="جستجوی ایمیل..." />
    <a href="{{ route('admin.newsletter.export', $filterParams) }}" class="admin-btn-secondary text-sm">خروجی CSV</a>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ایمیل</th>
                <th>تاریخ عضویت</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($subscribers as $subscriber)
                <tr>
                    <td>
                        <a href="mailto:{{ $subscriber->email }}" class="font-mono text-sm admin-link" dir="ltr">{{ $subscriber->email }}</a>
                    </td>
                    <td class="text-zinc-500">{{ format_jalali($subscriber->created_at, 'Y/m/d H:i') }}</td>
                    <td>
                        <x-admin.table-actions>
                            <x-admin.table-action type="delete" :action="route('admin.newsletter.destroy', $subscriber)" />
                        </x-admin.table-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">
                        <div class="flex flex-col items-center justify-center py-14 text-center">
                            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                            </div>
                            <p class="font-bold text-zinc-700">هنوز کسی در خبرنامه عضو نشده است</p>
                            <p class="mt-1 text-sm text-zinc-500">با فعال‌سازی فرم خبرنامه در سایت، اعضا اینجا نمایش داده می‌شوند.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<x-admin.pagination :paginator="$subscribers" />
@endsection
