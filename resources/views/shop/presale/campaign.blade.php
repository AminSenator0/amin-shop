
@extends('layouts.shop')

@section('title', ($presaleCampaign->title ?? 'پیش‌فروش') . ' | ' . $store['name'])

@section('content')
<div class="min-h-screen bg-slate-50/70" dir="rtl">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 sm:py-8 lg:px-8">

        {{-- Campaign Header --}}
        <section class="relative isolate overflow-hidden rounded-3xl bg-slate-900 px-5 py-8 sm:px-8 sm:py-10 lg:px-10">
            <div class="absolute inset-0 -z-10 opacity-20"
                 style="background-image: radial-gradient(circle at 85% 15%, #f59e0b 0, transparent 32%), radial-gradient(circle at 10% 100%, #0d9488 0, transparent 35%);">
            </div>

            <div class="relative max-w-3xl">
                <div class="flex flex-wrap items-center gap-2.5 mb-5">
                    <span class="inline-flex items-center gap-2 rounded-full bg-amber-400/15 border border-amber-300/20 px-3.5 py-1.5 text-xs font-bold text-amber-300">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 3v3m0 12v3M3 12h3m12 0h3M5.64 5.64l2.12 2.12m8.48 8.48 2.12 2.12m0-12.72-2.12 2.12m-8.48 8.48-2.12 2.12"/>
                            <circle cx="12" cy="12" r="5"/>
                        </svg>
                        فروش ویژه پیش‌فروش
                    </span>

                    @if($presaleCampaign->ends_at && $presaleCampaign->ends_at->isFuture())
                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-400/10 border border-emerald-300/20 px-3.5 py-1.5 text-xs font-semibold text-emerald-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            فعال تا {{ $presaleCampaign->ends_at->diffForHumans() }}
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-white leading-relaxed">
                    {{ $presaleCampaign->title ?? 'پیش‌فروش ویژه' }}
                </h1>

                @if($presaleCampaign->description)
                    <p class="mt-4 max-w-2xl text-sm sm:text-base leading-8 text-slate-300">
                        {{ $presaleCampaign->description }}
                    </p>
                @endif

                <div class="mt-7 flex flex-wrap items-center gap-x-5 gap-y-3 text-xs sm:text-sm text-slate-300">
                    <span class="inline-flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 7 10 17l-5-5"/>
                        </svg>
                        امکان ثبت سفارش پیش‌فروش
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 8v4l3 2"/>
                            <circle cx="12" cy="12" r="9"/>
                        </svg>
                        پرداخت بیعانه
                    </span>
                </div>
            </div>

            <div class="hidden lg:flex absolute left-10 bottom-8 w-24 h-24 items-center justify-center rounded-3xl border border-white/10 bg-white/5 rotate-6">
                <svg class="w-12 h-12 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
                    <path d="M3 8.5 12 4l9 4.5-9 4.5-9-4.5Z"/>
                    <path d="M3 8.5V16l9 4 9-4V8.5M12 13v7"/>
                </svg>
            </div>
        </section>

        {{-- Products Section --}}
        <section class="mt-8 sm:mt-10">

            <div class="flex items-end justify-between gap-4 mb-5 sm:mb-6">
                <div>
                    <span class="text-xs font-bold text-amber-600">محصولات کمپین</span>
                    <h2 class="mt-1.5 text-xl sm:text-2xl font-black text-slate-900">
                        محصولات قابل پیش‌خرید
                    </h2>
                    <p class="mt-1.5 text-xs sm:text-sm text-slate-500">
                        محصول موردنظرت را انتخاب و به سبد پیش‌فروش اضافه کن.
                    </p>
                </div>

                <span class="shrink-0 inline-flex items-center gap-1.5 rounded-xl bg-white border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600">
                    <svg class="w-4 h-4 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="16" rx="2"/>
                        <path d="M8 9h8M8 13h5"/>
                    </svg>
                    {{ $presaleCampaign->presaleProducts->count() }} محصول
                </span>
            </div>

            @if($presaleCampaign->presaleProducts->isEmpty())
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-5 py-14 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M3 8.5 12 4l9 4.5-9 4.5-9-4.5Z"/>
                            <path d="M3 8.5V16l9 4 9-4V8.5M12 13v7"/>
                        </svg>
                    </div>
                    <h3 class="mt-5 text-base font-bold text-slate-800">هنوز محصولی موجود نیست</h3>
                    <p class="mt-2 text-sm text-slate-500">محصولات این کمپین به‌زودی اضافه خواهند شد.</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3.5 sm:gap-5">

                    @foreach($presaleCampaign->presaleProducts as $pp)
                        @php
                            $product = $pp->product;
                            $image = $product->images->first()
                                ? asset('storage/' . $product->images->first()->path)
                                : asset('images/placeholder-product.jpg');
                            $variant = $pp->customFieldsLabel();
                        @endphp

                        <article class="group flex flex-col min-w-0 overflow-hidden rounded-2xl sm:rounded-3xl border border-slate-200/80 bg-white transition-all duration-300 hover:-translate-y-1 hover:border-amber-200 hover:shadow-xl hover:shadow-slate-200/60">

                            {{-- Product Image --}}
                            <a href="{{ route('presale.product', [$presaleCampaign, $pp]) }}"
                               class="relative block aspect-square overflow-hidden bg-slate-50">

                                <img
                                    src="{{ $image }}"
                                    alt="{{ $product->name }}"
                                    loading="lazy"
                                    class="w-full h-full object-contain p-3 sm:p-6 transition-transform duration-500 group-hover:scale-105"
                                >

                                @if($variant)
                                    <span class="absolute top-2.5 right-2.5 sm:top-3 sm:right-3 max-w-[75%] truncate rounded-lg bg-amber-500 px-2.5 py-1.5 text-[10px] sm:text-xs font-bold text-white shadow-sm">
                                        {{ $variant }}
                                    </span>
                                @endif

                                <span class="absolute bottom-2.5 left-2.5 flex h-8 w-8 items-center justify-center rounded-xl bg-white/90 text-slate-500 opacity-0 shadow-sm transition-opacity group-hover:opacity-100">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M7 17 17 7M7 7h10v10"/>
                                    </svg>
                                </span>
                            </a>

                            {{-- Product Details --}}
                            <div class="flex flex-1 flex-col p-3 sm:p-4">

                                <a href="{{ route('presale.product', [$presaleCampaign, $pp]) }}"
                                   class="min-h-[2.7rem] text-xs sm:text-sm font-extrabold leading-6 text-slate-800 transition-colors hover:text-amber-600 line-clamp-2">
                                    {{ $product->name }}
                                </a>

                                @if($product->brand->name ?? false)
                                    <div class="mt-1.5 flex items-center gap-1.5 text-[10px] sm:text-xs text-slate-400">
                                        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                            <path d="M12 3 4 7v10l8 4 8-4V7l-8-4Z"/>
                                            <path d="m4 7 8 4 8-4M12 11v10"/>
                                        </svg>
                                        <span class="truncate">{{ $product->brand->name }}</span>
                                    </div>
                                @endif

                                {{-- Deposit Price --}}
                                <div class="mt-4 rounded-xl sm:rounded-2xl bg-emerald-50/80 border border-emerald-100/80 p-2.5 sm:p-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-[10px] sm:text-xs font-semibold text-emerald-700">
                                            مبلغ بیعانه
                                        </span>
                                        <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                        </svg>
                                    </div>
                                    <div class="mt-1 flex flex-wrap items-baseline gap-x-1">
                                        <span class="text-base sm:text-xl font-black tracking-tight text-emerald-700">
                                            {{ number_format($pp->presale_price) }}
                                        </span>
                                        <span class="text-[10px] sm:text-xs font-medium text-emerald-700">تومان</span>
                                    </div>
                                </div>

                                {{-- Add To Cart --}}
                                <form action="{{ route('cart.presale.store') }}" method="POST" class="mt-auto pt-3">
                                    @csrf
                                    <input type="hidden" name="presale_product_id" value="{{ $pp->id }}">
                                    <input type="hidden" name="quantity" value="1">

                                    <button type="submit"
                                            class="flex w-full min-h-10 sm:min-h-11 items-center justify-center gap-2 rounded-xl bg-slate-900 px-2.5 py-2.5 text-[10px] sm:text-xs font-bold text-white transition-all duration-200 hover:bg-amber-500 hover:text-slate-950 focus:outline-none focus:ring-4 focus:ring-amber-400/20 active:scale-[0.98]">
                                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M5 7h14l-1.5 13h-11L5 7Z"/>
                                            <path d="M9 9V6a3 3 0 0 1 6 0v3"/>
                                            <path d="M9 13h.01M15 13h.01"/>
                                        </svg>
                                        <span>افزودن به سبد</span>
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach

                </div>
            @endif
        </section>
    </div>
</div>
@endsection
