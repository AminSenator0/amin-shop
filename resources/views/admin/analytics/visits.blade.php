@extends('layouts.admin')

@section('title', 'نمودار بازدیدها')
@section('header', 'آمار بازدیدها')

@push('scripts')
    @vite('resources/js/admin-analytics-visits.js')
@endpush

@section('content')
    @include('admin.analytics._tabs')

    {{-- ─── کارت‌های خلاصه ─────────────────────────────── --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
        <x-admin.stat-card label="بازدید صفحه امروز" :value="$summary['page_views_today']" color="emerald"
            subtext="مجموع بازدید همه صفحات از ابتدای امروز"
            icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>' />
        <x-admin.stat-card label="بازدید یکتای امروز" :value="$summary['uniques_today']" color="blue"
            subtext="کاربران یکتا (بر اساس IP و مرورگر)"
            icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>' />
        <x-admin.stat-card label="میانگین بازدید صفحه روزانه" :value="$summary['avg_page_views']" color="violet"
            subtext="۱۵ روز اخیر"
            icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>' />
        <x-admin.stat-card label="میانگین بازدید یکتا روزانه" :value="$summary['avg_uniques']" color="amber"
            subtext="۱۵ روز اخیر"
            icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>' />
    </div>

    {{-- ─── نمودارهای خطی ──────────────────────────────── --}}
    <div class="grid gap-6 lg:grid-cols-2 mb-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">بازدید صفحه — ۱۵ روز اخیر</h2>
                <span class="text-xs text-zinc-500">مجموع بازدید همه صفحات</span>
            </div>
            <div class="p-5">
                <div class="h-72">
                    <canvas id="pageViewsChart"
                        data-chart="{{ json_encode([
                            'labels' => $pageViews->pluck('label'),
                            'values' => $pageViews->pluck('value'),
                        ]) }}"></canvas>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">بازدید یکتا — ۱۵ روز اخیر</h2>
                <span class="text-xs text-zinc-500">کاربران غیرتکراری</span>
            </div>
            <div class="p-5">
                <div class="h-72">
                    <canvas id="uniqueVisitorsChart"
                        data-chart="{{ json_encode([
                            'labels' => $uniqueVisitors->pluck('label'),
                            'values' => $uniqueVisitors->pluck('value'),
                        ]) }}"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── نمودارهای دایره‌ای ───────────────────────────── --}}
    <div class="grid gap-6 lg:grid-cols-2 mb-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">سیستم‌عامل کاربران</h2>
                <span class="text-xs text-zinc-500">۱۵ روز اخیر</span>
            </div>
            <div class="p-5">
                <div class="h-72">
                    <canvas id="osChart"
                        data-chart="{{ json_encode($osStats->map(fn ($row) => ['label' => $row['label'], 'count' => $row['value']])->values()) }}"></canvas>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">مرورگر کاربران</h2>
                <span class="text-xs text-zinc-500">۱۵ روز اخیر</span>
            </div>
            <div class="p-5">
                <div class="h-72">
                    <canvas id="browserChart"
                        data-chart="{{ json_encode($browserStats->map(fn ($row) => ['label' => $row['label'], 'count' => $row['value']])->values()) }}"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection