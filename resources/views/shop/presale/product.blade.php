
@extends('layouts.shop')

@section('title', ($presaleProduct->product->name ?? 'محصول پیش‌فروش') . ' | پیش‌فروش ' . verta($presaleCampaign->starts_at)->format('%B'))

@section('content')

@php
    $product = $presaleProduct->product;
    $firstImg = $product->images->first();
    $image = $firstImg ? $firstImg->url() : asset('images/placeholder-product.jpg');
    $variant = $presaleProduct->customFieldsLabel();
    $endsAt = $presaleCampaign->ends_at
        ? $presaleCampaign->ends_at->toIso8601String()
        : null;
@endphp

<div class="min-h-screen bg-slate-50/70" dir="rtl">
    <div class="max-w-7xl mx-auto px-4 py-5 sm:px-6 sm:py-8 lg:px-8">

        {{-- Breadcrumb --}}
        <nav aria-label="مسیر صفحه" class="mb-6">
            <ol class="flex flex-wrap items-center gap-2 text-xs sm:text-sm text-slate-500">
                <li>
                    <a href="{{ route('home') }}"
                       class="transition-colors hover:text-amber-600">
                        صفحه اصلی
                    </a>
                </li>

                <li class="text-slate-300">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/>
                    </svg>
                </li>

                <li>
                    <a href="{{ route('presale.campaign', $presaleCampaign) }}"
                       class="transition-colors hover:text-amber-600">
                        پیش‌فروش {{ verta($presaleCampaign->starts_at)->format('%B') }}
                    </a>
                </li>

                <li class="text-slate-300">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/>
                    </svg>
                </li>

                <li class="font-semibold text-slate-800 truncate max-w-[180px] sm:max-w-xs">
                    {{ $product->name }}
                </li>
            </ol>
        </nav>

        {{-- Product Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-10 xl:gap-14 items-start">

            {{-- Gallery --}}
            <section class="min-w-0">
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white p-2 sm:p-3">

                    {{-- Main Image --}}
                    <div class="relative flex aspect-square max-h-[600px] items-center justify-center overflow-hidden rounded-2xl bg-slate-50">

                        <img
                            src="{{ $image }}"
                            alt="{{ $product->name }}"
                            id="pp-main-img"
                            fetchpriority="high"
                            class="w-full h-full object-contain p-5 sm:p-8 transition-opacity duration-200"
                        >

                        {{-- Presale Label --}}
                        <div class="absolute top-4 right-4">
                            <span class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-3.5 py-2 text-xs font-bold text-white shadow-sm">
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                                محصول پیش‌فروش
                            </span>
                        </div>

                    </div>

                    {{-- Thumbnails --}}
                    @if($product->images->count() > 1)
                        <div class="mt-3 flex gap-2.5 overflow-x-auto pb-1" aria-label="تصاویر محصول">

                            @foreach($product->images as $index => $img)
                                <button
                                    type="button"
                                    data-gallery-image="{{ $img->url() }}"
                                    aria-label="نمایش تصویر {{ $index + 1 }}"
                                    aria-pressed="{{ $index === 0 ? 'true' : 'false' }}"
                                    class="pp-thumb group relative h-16 w-16 sm:h-20 sm:w-20 shrink-0 overflow-hidden rounded-xl border-2 {{ $index === 0 ? 'border-amber-500 bg-amber-50' : 'border-slate-100 bg-slate-50' }} p-1 transition-all hover:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/30"
                                >
                                    <img
                                        src="{{ $img->url() }}"
                                        alt="{{ $product->name }} - تصویر {{ $index + 1 }}"
                                        loading="lazy"
                                        class="h-full w-full object-contain"
                                    >
                                </button>
                            @endforeach

                        </div>
                    @endif
                </div>

                {{-- Gallery Hint --}}
                <div class="mt-3 flex items-center gap-2 px-1 text-xs text-slate-400">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                        <rect x="3" y="3" width="18" height="18" rx="3"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <path d="m21 15-5-5L5 21"/>
                    </svg>
                    برای مشاهده تصاویر دیگر، روی تصویر بندانگشتی کلیک کن.
                </div>
            </section>

            {{-- Product Information --}}
            <section class="min-w-0 lg:sticky lg:top-6">

                <div class="rounded-3xl border border-slate-200 bg-white p-5 sm:p-7 lg:p-8">

                    {{-- Badges --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 3v3m0 12v3M3 12h3m12 0h3"/>
                                <circle cx="12" cy="12" r="5"/>
                            </svg>
                            پیش‌فروش
                        </span>

                        <span class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600">
                            {{ verta($presaleCampaign->starts_at)->format('%B') }}
                        </span>

                        @if($variant)
                            <span class="inline-flex items-center rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">
                                {{ $variant }}
                            </span>
                        @endif
                    </div>

                    {{-- Title --}}
                    <div class="mt-5">
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-black leading-relaxed text-slate-900">
                            {{ $product->name }}
                        </h1>

                        @if($product->brand->name ?? false)
                            <div class="mt-2 flex items-center gap-2 text-sm text-slate-500">
                                <svg class="h-4 w-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <path d="M12 3 4 7v10l8 4 8-4V7l-8-4Z"/>
                                    <path d="m4 7 8 4 8-4M12 11v10"/>
                                </svg>
                                <span>{{ $product->brand->name }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Divider --}}
                    <div class="my-6 border-t border-slate-100"></div>

                    {{-- Countdown --}}
                    @if($endsAt)
                        <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4 sm:p-5">

                            <div class="flex items-center gap-2.5">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path d="M12 7v5l3 2"/>
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-sm font-bold text-amber-900">
                                        زمان باقی‌مانده کمپین
                                    </p>
                                    <p class="mt-0.5 text-[11px] text-amber-700">
                                        فرصت ثبت سفارش تا پایان کمپین
                                    </p>
                                </div>
                            </div>

                            <div
                                class="pp-timer mt-4 flex items-center justify-center gap-1.5 sm:gap-3"
                                data-ends-at="{{ $endsAt }}"
                                aria-live="polite"
                            >
                                @foreach([
                                    ['key' => 'd', 'label' => 'روز'],
                                    ['key' => 'h', 'label' => 'ساعت'],
                                    ['key' => 'm', 'label' => 'دقیقه'],
                                    ['key' => 's', 'label' => 'ثانیه'],
                                ] as $unit)
                                    <div class="flex min-w-0 flex-1 flex-col items-center gap-1.5 rounded-xl border border-amber-100 bg-white px-1.5 py-3 sm:px-3">
                                        <b data-t="{{ $unit['key'] }}" class="text-xl sm:text-2xl font-black tabular-nums text-slate-900">
                                            00
                                        </b>
                                        <span class="text-[10px] sm:text-xs font-medium text-slate-500">
                                            {{ $unit['label'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Price --}}
                    <div class="mt-5 overflow-hidden rounded-2xl border border-emerald-100 bg-emerald-50/60">

                        <div class="flex items-center justify-between gap-3 border-b border-emerald-100 px-4 py-3">
                            <span class="text-sm font-bold text-emerald-900">
                                مبلغ موردنیاز برای ثبت سفارش
                            </span>

                            <span class="rounded-lg bg-emerald-100 px-2.5 py-1 text-[10px] font-bold text-emerald-700">
                                بیعانه
                            </span>
                        </div>

                        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1 px-4 py-4">
                            <span class="text-2xl sm:text-3xl font-black tracking-tight text-emerald-700 tabular-nums">
                                {{ number_format($presaleProduct->presale_price) }}
                            </span>
                            <span class="text-sm font-semibold text-emerald-700">
                                تومان
                            </span>
                        </div>

                        <div class="flex items-start gap-2 border-t border-emerald-100 bg-white/60 px-4 py-3">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 11v5m0-8h.01"/>
                            </svg>
                            <p class="text-xs leading-6 text-slate-600">
                                قیمت نهایی محصول پس از پایان کمپین مشخص خواهد شد.
                            </p>
                        </div>
                    </div>

                    {{-- Add To Cart Form --}}
                    <form action="{{ route('cart.presale.store') }}" method="POST" class="mt-6">
                        @csrf

                        <input
                            type="hidden"
                            name="presale_product_id"
                            value="{{ $presaleProduct->id }}"
                        >

                        {{-- Quantity --}}
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <label for="pp-quantity" class="block text-sm font-bold text-slate-800">
                                    تعداد موردنظر
                                </label>
                                <p class="mt-1 text-xs text-slate-400">
                                    حداکثر ۱۰ عدد
                                </p>
                            </div>

                            <div class="flex h-11 items-center overflow-hidden rounded-xl border border-slate-200 bg-white">

                                <button
                                    type="button"
                                    data-qty-change="-1"
                                    aria-label="کاهش تعداد"
                                    class="flex h-full w-11 items-center justify-center text-lg font-bold text-slate-600 transition-colors hover:bg-slate-100 focus:outline-none focus:bg-slate-100"
                                >
                                    −
                                </button>

                                <input
                                    id="pp-quantity"
                                    type="number"
                                    name="quantity"
                                    value="{{ old('quantity', 1) }}"
                                    min="1"
                                    max="10"
                                    readonly
                                    aria-label="تعداد محصول"
                                    class="h-full w-12 border-x border-slate-200 bg-white text-center text-sm font-black tabular-nums text-slate-900 outline-none"
                                >

                                <button
                                    type="button"
                                    data-qty-change="1"
                                    aria-label="افزایش تعداد"
                                    class="flex h-full w-11 items-center justify-center text-lg font-bold text-slate-600 transition-colors hover:bg-slate-100 focus:outline-none focus:bg-slate-100"
                                >
                                    +
                                </button>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <button
                            type="submit"
                            class="mt-5 flex min-h-13 w-full items-center justify-center gap-2.5 rounded-xl bg-slate-900 px-5 py-3.5 text-sm font-extrabold text-white shadow-sm transition-all duration-200 hover:bg-amber-500 hover:text-slate-950 hover:shadow-lg hover:shadow-amber-500/10 focus:outline-none focus:ring-4 focus:ring-amber-400/25 active:scale-[0.99]"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h1.5c.7 0 1.3.48 1.48 1.16L6.5 7.5m0 0h13.75l-1.5 8H8L6.5 7.5Zm1.5 11.25a1.25 1.25 0 1 0 0 2.5 1.25 1.25 0 0 0 0-2.5Zm9 0a1.25 1.25 0 1 0 0 2.5 1.25 1.25 0 0 0 0-2.5Z"/>
                            </svg>

                            افزودن به سبد پیش‌فروش
                        </button>

                        <div class="mt-3 flex items-center justify-center gap-2 text-[11px] text-slate-400">
                            <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m5 12 4 4L19 6"/>
                            </svg>
                            ثبت سفارش با پرداخت مبلغ بیعانه
                        </div>
                    </form>

                    {{-- Campaign Description --}}
                    @if($presaleCampaign->description)
                        <div class="mt-7 border-t border-slate-100 pt-6">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M4 5h16M4 10h16M4 15h10M4 20h7"/>
                                    </svg>
                                </div>

                                <h2 class="text-sm font-extrabold text-slate-800">
                                    توضیحات کمپین
                                </h2>
                            </div>

                            <p class="text-sm leading-8 text-slate-600 whitespace-pre-line">
                                {{ $presaleCampaign->description }}
                            </p>
                        </div>
                    @endif

                </div>

                {{-- Back Link --}}
                <a
                    href="{{ route('presale.campaign', $presaleCampaign) }}"
                    class="mt-4 flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-600 transition-colors hover:border-amber-300 hover:text-amber-700"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/>
                    </svg>
                    بازگشت به محصولات پیش‌فروش
                </a>

            </section>
        </div>
    </div>
</div>

{{-- Page Scripts --}}
<script>
(function () {
    'use strict';

    // Product Gallery
    const mainImage = document.getElementById('pp-main-img');
    const thumbnails = document.querySelectorAll('[data-gallery-image]');

    thumbnails.forEach(function (button) {
        button.addEventListener('click', function () {
            if (!mainImage) return;

            const imageUrl = this.dataset.galleryImage;

            mainImage.style.opacity = '0';

            window.setTimeout(function () {
                mainImage.src = imageUrl;
                mainImage.style.opacity = '1';
            }, 120);

            thumbnails.forEach(function (item) {
                item.classList.remove('border-amber-500', 'bg-amber-50');
                item.classList.add('border-slate-100', 'bg-slate-50');
                item.setAttribute('aria-pressed', 'false');
            });

            this.classList.remove('border-slate-100', 'bg-slate-50');
            this.classList.add('border-amber-500', 'bg-amber-50');
            this.setAttribute('aria-pressed', 'true');
        });
    });

    // Quantity Selector
    const quantityInput = document.getElementById('pp-quantity');
    const quantityButtons = document.querySelectorAll('[data-qty-change]');

    quantityButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (!quantityInput) return;

            const change = parseInt(this.dataset.qtyChange, 10);
            const currentValue = parseInt(quantityInput.value, 10) || 1;

            const nextValue = Math.min(10, Math.max(1, currentValue + change));

            quantityInput.value = nextValue;
        });
    });

    // Campaign Countdown
    const timer = document.querySelector('.pp-timer');

    if (!timer) return;

    const endsAt = new Date(timer.dataset.endsAt).getTime();

    if (Number.isNaN(endsAt)) {
        timer.textContent = 'زمان پایان کمپین مشخص نیست';
        return;
    }

    const pad = function (number) {
        return String(number).padStart(2, '0');
    };

    let countdownInterval = null;

    function updateCountdown() {
        const difference = endsAt - Date.now();

        if (difference <= 0) {
            if (countdownInterval !== null) {
                window.clearInterval(countdownInterval);
            }

            timer.innerHTML = '<span class="col-span-4 w-full rounded-xl bg-red-50 px-3 py-4 text-center text-sm font-bold text-red-600">مهلت این کمپین به پایان رسیده است.</span>';
            return;
        }

        const days = Math.floor(difference / 86400000);
        const hours = Math.floor((difference % 86400000) / 3600000);
        const minutes = Math.floor((difference % 3600000) / 60000);
        const seconds = Math.floor((difference % 60000) / 1000);

        const values = {
            d: days,
            h: hours,
            m: minutes,
            s: seconds
        };

        Object.keys(values).forEach(function (key) {
            const element = timer.querySelector('[data-t="' + key + '"]');

            if (element) {
                element.textContent = pad(values[key]);
            }
        });
    }

    updateCountdown();
    countdownInterval = window.setInterval(updateCountdown, 1000);

})();
</script>

@endsection
