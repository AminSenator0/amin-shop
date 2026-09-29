@extends('layouts.admin')

@section('title', 'نمودار بازدیدها')
@section('header', 'آمار بازدیدها')

@push('scripts')
    @vite('resources/js/admin-analytics-visits.js')
@endpush

@section('content')
    <div dir="rtl" class="space-y-6">

        {{-- ─── سربرگ صفحه ─────────────────────────────────── --}}
        <div class="relative overflow-hidden rounded-2xl border border-zinc-200/80 bg-white p-5 shadow-sm sm:p-6">
            <div class="pointer-events-none absolute -left-12 -top-16 h-48 w-48 rounded-full bg-indigo-100/50 blur-3xl"></div>

            <div class="relative flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50 px-3 py-1.5">
                        <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                        <span class="text-xs font-bold text-indigo-700">
                            مرکز تحلیل و آمار
                        </span>
                    </div>

                    <h1 class="text-xl font-black tracking-tight text-zinc-900 sm:text-2xl">
                        آمار بازدیدها
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-500">
                        نمایی از بازدید صفحات، کاربران یکتا و رفتار مرور کاربران در فروشگاه شما.
                    </p>
                </div>

                <div class="flex w-fit items-center gap-2 rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3">
                    <svg class="h-5 w-5 text-indigo-500"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 3v18h18M7 14l4-4 4 4 6-7"/>
                    </svg>

                    <div>
                        <p class="text-xs font-bold text-zinc-700">
                            گزارش بازدیدها
                        </p>
                        <p class="mt-0.5 text-[11px] text-zinc-400">
                            اطلاعات ۱۵ روز اخیر
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── تب‌های آمار ─────────────────────────────────── --}}
        @include('admin.analytics._tabs')


        {{-- ─── کارت‌های خلاصه ─────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <x-admin.stat-card
                label="بازدید صفحه امروز"
                :value="$summary['page_views_today']"
                color="emerald"
                subtext="مجموع بازدید همه صفحات از ابتدای امروز"
                icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>'
            />

            <x-admin.stat-card
                label="بازدید یکتای امروز"
                :value="$summary['uniques_today']"
                color="blue"
                subtext="کاربران یکتا بر اساس IP و مرورگر"
                icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>'
            />

            <x-admin.stat-card
                label="میانگین بازدید صفحه روزانه"
                :value="$summary['avg_page_views']"
                color="violet"
                subtext="میانگین روزانه در ۱۵ روز اخیر"
                icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25c-.621 0-1.125-.504-1.125-1.125V4.125z" /></svg>'
            />

            <x-admin.stat-card
                label="میانگین بازدید یکتا روزانه"
                :value="$summary['avg_uniques']"
                color="amber"
                subtext="میانگین روزانه در ۱۵ روز اخیر"
                icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>'
            />

        </div>


        {{-- ─── نمودارهای خطی ──────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">

            {{-- Page views --}}
            <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm transition-shadow duration-300 hover:shadow-md">

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-100 px-5 py-5 sm:px-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <svg class="h-5 w-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 3v18h18M7 14l4-4 4 4 6-7"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-sm font-extrabold text-zinc-800">
                                بازدید صفحه
                            </h2>
                            <p class="mt-1 text-xs text-zinc-500">
                                روند بازدید همه صفحات
                            </p>
                        </div>
                    </div>

                    <span class="rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                        ۱۵ روز اخیر
                    </span>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="h-64 sm:h-72">
                        <canvas
                            id="pageViewsChart"
                            role="img"
                            aria-label="نمودار بازدید صفحه در ۱۵ روز اخیر"
                            data-chart="{{ json_encode([
                                'labels' => $pageViews->pluck('label'),
                                'values' => $pageViews->pluck('value'),
                            ]) }}">
                        </canvas>
                    </div>
                </div>

                <div class="border-t border-zinc-100 bg-zinc-50/70 px-5 py-3">
                    <p class="flex items-center gap-2 text-xs text-zinc-500">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        مجموع بازدیدهای ثبت‌شده در هر روز
                    </p>
                </div>
            </div>


            {{-- Unique visitors --}}
            <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm transition-shadow duration-300 hover:shadow-md">

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-100 px-5 py-5 sm:px-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <svg class="h-5 w-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2m16 0v-2a4 4 0 00-3-3.87M14 3.13a4 4 0 010 7.75M14 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-sm font-extrabold text-zinc-800">
                                بازدیدکنندگان یکتا
                            </h2>
                            <p class="mt-1 text-xs text-zinc-500">
                                روند کاربران غیرتکراری
                            </p>
                        </div>
                    </div>

                    <span class="rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700">
                        ۱۵ روز اخیر
                    </span>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="h-64 sm:h-72">
                        <canvas
                            id="uniqueVisitorsChart"
                            role="img"
                            aria-label="نمودار بازدیدکنندگان یکتا در ۱۵ روز اخیر"
                            data-chart="{{ json_encode([
                                'labels' => $uniqueVisitors->pluck('label'),
                                'values' => $uniqueVisitors->pluck('value'),
                            ]) }}">
                        </canvas>
                    </div>
                </div>

                <div class="border-t border-zinc-100 bg-zinc-50/70 px-5 py-3">
                    <p class="flex items-center gap-2 text-xs text-zinc-500">
                        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                        کاربران یکتا بر اساس معیار ثبت‌شده در سیستم
                    </p>
                </div>
            </div>

        </div>


        {{-- ─── نمودارهای توزیعی ───────────────────────────── --}}
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">

            {{-- Operating systems --}}
            <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm transition-shadow duration-300 hover:shadow-md">

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-100 px-5 py-5 sm:px-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                            <svg class="h-5 w-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <rect x="3" y="4" width="18" height="13" rx="2"/>
                                <path stroke-linecap="round" d="M8 21h8m-4-4v4"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-sm font-extrabold text-zinc-800">
                                سیستم‌عامل کاربران
                            </h2>
                            <p class="mt-1 text-xs text-zinc-500">
                                تفکیک کاربران بر اساس سیستم‌عامل
                            </p>
                        </div>
                    </div>

                    <span class="rounded-full border border-violet-100 bg-violet-50 px-3 py-1.5 text-xs font-bold text-violet-700">
                        ۱۵ روز اخیر
                    </span>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="h-72 sm:h-80">
                        <canvas
                            id="osChart"
                            role="img"
                            aria-label="نمودار سیستم‌عامل کاربران"
                            data-chart="{{ json_encode(
                                $osStats->map(fn ($row) => [
                                    'label' => $row['label'],
                                    'count' => $row['value'],
                                ])->values()
                            ) }}">
                        </canvas>
                    </div>
                </div>

                <div class="border-t border-zinc-100 bg-zinc-50/70 px-5 py-3">
                    <p class="flex items-center gap-2 text-xs text-zinc-500">
                        <span class="h-2 w-2 rounded-full bg-violet-500"></span>
                        سهم سیستم‌عامل‌ها از آمار ثبت‌شده
                    </p>
                </div>
            </div>


            {{-- Browsers --}}
            <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm transition-shadow duration-300 hover:shadow-md">

                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-100 px-5 py-5 sm:px-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                            <svg class="h-5 w-5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="1.8">
                                <circle cx="12" cy="12" r="9"/>
                                <circle cx="12" cy="12" r="3"/>
                                <path stroke-linecap="round" d="M12 3v6m9 3h-6M12 21v-6M3 12h6"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-sm font-extrabold text-zinc-800">
                                مرورگر کاربران
                            </h2>
                            <p class="mt-1 text-xs text-zinc-500">
                                سهم مرورگرهای مورد استفاده کاربران
                            </p>
                        </div>
                    </div>

                    <span class="rounded-full border border-sky-100 bg-sky-50 px-3 py-1.5 text-xs font-bold text-sky-700">
                        ۱۵ روز اخیر
                    </span>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="relative mx-auto h-72 w-full max-w-md sm:h-80">
                        <canvas
                            id="browserChart"
                            role="img"
                            aria-label="نمودار مرورگر کاربران"
                            data-chart="{{ json_encode(
                                $browserStats->map(fn ($row) => [
                                    'label' => $row['label'],
                                    'count' => $row['value'],
                                ])->values()
                            ) }}">
                        </canvas>
                    </div>
                </div>

                <div class="border-t border-zinc-100 bg-zinc-50/70 px-5 py-3">
                    <p class="flex items-center gap-2 text-xs text-zinc-500">
                        <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                        توزیع مرورگرهای شناسایی‌شده در بازه گزارش
                    </p>
                </div>
            </div>

        </div>

    </div>
@endsection