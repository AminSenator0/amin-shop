@extends('layouts.admin')

@section('title', 'آمار محصولات')
@section('header', 'آمار محصولات')

@section('content')
    @include('admin.analytics._tabs')

    {{-- ─── فیلتر بازه زمانی ─────────────────────────────── --}}
    @php
        $periods = [
            'all' => 'همه',
            'today' => 'امروز',
            'week' => 'هفته',
            'month' => 'ماه',
        ];
    @endphp

    <div class="admin-card mb-8">
        <div class="flex flex-wrap items-center gap-2 p-4">
            <span class="ml-1 text-sm font-medium text-zinc-500">مرتب‌سازی بر اساس:</span>
            @foreach($periods as $key => $label)
                <a href="{{ route('admin.analytics.products', ['period' => $key]) }}"
                   class="rounded-xl px-4 py-2 text-sm font-semibold transition
                   {{ $period === $key
                       ? 'bg-[var(--surface-primary)] text-white shadow-md'
                       : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 hover:text-zinc-900' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- ─── ۱۰ محصول پرفروش ──────────────────────────────── --}}
    <div class="admin-card mb-8">
        <div class="admin-card-header">
            <h2 class="admin-card-title">۱۰ محصول پرفروش</h2>
            <span class="text-xs text-zinc-500">
                @if($period === 'today') فروش‌های امروز
                @elseif($period === 'week') فروش‌های ۷ روز اخیر
                @elseif($period === 'month') فروش‌های ۳۰ روز اخیر
                @else تمام فروش‌ها (به‌جز سفارش‌های لغوشده) @endif
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="w-14 text-center">رتبه</th>
                        <th class="w-20">تصویر</th>
                        <th>عنوان</th>
                        <th class="w-32 text-center">تعداد فروش</th>
                        <th class="w-44">آخرین بروزرسانی</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topSelling as $index => $row)
                        @php
                            $rank = $index + 1;
                            $rankClass = match ($rank) {
                                1 => 'bg-amber-400 text-amber-950',
                                2 => 'bg-zinc-300 text-zinc-800',
                                3 => 'bg-orange-300 text-orange-950',
                                default => 'bg-zinc-100 text-zinc-500',
                            };
                        @endphp
                        <tr>
                            <td class="text-center">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold {{ $rankClass }}">
                                    {{ $rank }}
                                </span>
                            </td>
                            <td>
                                <img src="{{ $row['image'] }}" alt="{{ $row['name'] }}"
                                     class="h-12 w-12 rounded-xl object-cover ring-1 ring-zinc-200"
                                     loading="lazy">
                            </td>
                            <td>
                                @if($row['url'])
                                    <a href="{{ $row['url'] }}" target="_blank" class="font-semibold text-zinc-800 hover:text-[var(--surface-primary)] transition">
                                        {{ $row['name'] }}
                                    </a>
                                @else
                                    <span class="font-semibold text-zinc-800">{{ $row['name'] }}</span>
                                    <span class="mr-1 rounded bg-zinc-100 px-1.5 py-0.5 text-[10px] text-zinc-500">حذف‌شده</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">
                                    {{ number_format($row['total_sold']) }} فروش
                                </span>
                            </td>
                            <td class="text-xs text-zinc-500">{{ format_jalali($row['last_activity'], 'Y/m/d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-sm text-zinc-400">
                                در این بازه زمانی فروشی ثبت نشده است.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ─── ۱۰ محصول پربازدید ────────────────────────────── --}}
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">۱۰ محصول پربازدید</h2>
            <span class="text-xs text-zinc-500">بر اساس بازدید کاربران از صفحه محصول</span>
        </div>

        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="w-14 text-center">رتبه</th>
                        <th class="w-20">تصویر</th>
                        <th>عنوان</th>
                        <th class="w-32 text-center">بازدید</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topViewed as $index => $product)
                        @php
                            $rank = $index + 1;
                            $rankClass = match ($rank) {
                                1 => 'bg-amber-400 text-amber-950',
                                2 => 'bg-zinc-300 text-zinc-800',
                                3 => 'bg-orange-300 text-orange-950',
                                default => 'bg-zinc-100 text-zinc-500',
                            };
                            $image = $product->image
                                ? asset('storage/'.$product->image)
                                : asset('images/store-logo.svg');
                        @endphp
                        <tr>
                            <td class="text-center">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold {{ $rankClass }}">
                                    {{ $rank }}
                                </span>
                            </td>
                            <td>
                                <img src="{{ $image }}" alt="{{ $product->name }}"
                                     class="h-12 w-12 rounded-xl object-cover ring-1 ring-zinc-200"
                                     loading="lazy">
                            </td>
                            <td>
                                <a href="{{ route('products.show', $product->slug) }}" target="_blank"
                                   class="font-semibold text-zinc-800 hover:text-[var(--surface-primary)] transition">
                                    {{ $product->name }}
                                </a>
                            </td>
                            <td class="text-center">
                                <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700 ring-1 ring-indigo-200">
                                    {{ number_format($product->views) }} بازدید
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-sm text-zinc-400">
                                هنوز بازدیدی برای محصولات ثبت نشده است.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection