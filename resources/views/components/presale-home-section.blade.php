
@props(['campaign'])

@if($campaign && $campaign->presaleProducts->isNotEmpty())

    @php
        $endsAt = $campaign->ends_at
            ? $campaign->ends_at->toIso8601String()
            : null;

        $sectionId = 'presale-section-' . $campaign->id;
    @endphp

    <section id="{{ $sectionId }}" class="home-section home-how-section scroll-mt-28">
        <div class="home-section-container">
            <div class="home-how-panel">

                <div class="home-how-glow" aria-hidden="true"></div>

                <div class="relative">

                    {{-- Header --}}
                    <div class="mb-7 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

                        <div class="min-w-0">

                            <div class="flex flex-wrap items-center gap-2.5">

                                <span class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-3.5 py-2 text-[11px] font-extrabold text-white shadow-lg shadow-slate-900/10">
                                    <svg class="h-4 w-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 3v3m0 12v3M3 12h3m12 0h3"/>
                                        <circle cx="12" cy="12" r="5"/>
                                    </svg>
                                    پیش‌فروش ویژه
                                </span>

                                @if($endsAt)
                                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-2 text-[11px] font-bold text-emerald-700">
                                        <span class="relative flex h-2 w-2">
                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-50"></span>
                                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                                        </span>
                                        در حال برگزاری
                                    </span>
                                @endif

                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-3">

                                <h2 class="text-xl font-black tracking-tight text-slate-900 sm:text-2xl lg:text-3xl">
                                    پیش‌فروش {{ verta($campaign->starts_at)->format('%B') }}
                                </h2>

                                <span class="rounded-xl bg-white px-3 py-1.5 text-xs font-bold text-slate-500 ring-1 ring-slate-200">
                                    {{ $campaign->presaleProducts->count() }} محصول
                                </span>

                            </div>

                            <p class="mt-2 text-xs leading-7 text-slate-500 sm:text-sm">
                                محصولات منتخب را با پرداخت بیعانه پیش‌خرید کنید.
                            </p>

                        </div>

                        {{-- Header Actions --}}
                        <div class="flex w-full flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center lg:w-auto lg:shrink-0">

                            {{-- Countdown --}}
                            @if($endsAt)
                                <div
                                    class="ph-timer"
                                    data-ends-at="{{ $endsAt }}"
                                    aria-label="زمان باقی‌مانده تا پایان پیش‌فروش"
                                    aria-live="off"
                                >
                                    <div class="ph-timer-heading">
                                        <span class="ph-timer-icon">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <circle cx="12" cy="12" r="9"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>
                                            </svg>
                                        </span>

                                        <div>
                                            <strong>فرصت باقی‌مانده</strong>
                                            <small>تا پایان پیش‌فروش</small>
                                        </div>
                                    </div>

                                    <div class="ph-timer-countdown" dir="ltr">
                                        <span class="ph-timer-unit">
                                            <b data-t="d">00</b>
                                            <i>روز</i>
                                        </span>

                                        <span class="ph-timer-separator">:</span>

                                        <span class="ph-timer-unit">
                                            <b data-t="h">00</b>
                                            <i>ساعت</i>
                                        </span>

                                        <span class="ph-timer-separator">:</span>

                                        <span class="ph-timer-unit">
                                            <b data-t="m">00</b>
                                            <i>دقیقه</i>
                                        </span>

                                        <span class="ph-timer-separator">:</span>

                                        <span class="ph-timer-unit ph-timer-seconds">
                                            <b data-t="s">00</b>
                                            <i>ثانیه</i>
                                        </span>
                                    </div>

                                    <span class="ph-timer-shine" aria-hidden="true"></span>
                                </div>
                            @endif

                            {{-- View All --}}
                            <a
                                href="{{ route('presale.campaign', $campaign) }}"
                                class="group inline-flex min-h-12 items-center justify-center gap-2 rounded-2xl bg-slate-900 px-5 py-3 text-xs font-extrabold text-white shadow-lg shadow-slate-900/10 transition-all duration-300 hover:-translate-y-1 hover:bg-amber-500 hover:text-slate-950 hover:shadow-amber-500/20 focus:outline-none focus:ring-4 focus:ring-amber-400/20"
                            >
                                مشاهده همه محصولات

                                <svg class="h-4 w-4 transition-transform duration-300 group-hover:-translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/>
                                </svg>
                            </a>

                        </div>
                    </div>

                    {{-- Products Carousel --}}
                    <div class="relative">

                        <div class="ph-scroll -mx-1 overflow-x-auto px-1 pb-5">

                            <div class="ph-track flex w-max gap-3 sm:gap-4">

                                @foreach($campaign->presaleProducts as $pp)

                                    @php
                                        $product = $pp->product;
                                        $firstImg = $product->images->first();

                                        $image = $firstImg
                                            ? $firstImg->url()
                                            : asset('images/placeholder-product.jpg');

                                        $variant = $pp->customFieldsLabel();
                                    @endphp

                                    <article class="ph-card group flex w-[165px] shrink-0 flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white transition-all duration-500 hover:-translate-y-2 hover:border-amber-300 hover:shadow-2xl hover:shadow-slate-300/50 sm:w-[205px] sm:rounded-3xl">

                                        {{-- Product Image --}}
                                        <div class="relative aspect-square overflow-hidden bg-gradient-to-br from-slate-50 to-slate-100/70">

                                            <a
                                                href="{{ route('presale.product', [$campaign, $pp]) }}"
                                                class="flex h-full w-full items-center justify-center p-3 sm:p-5"
                                                aria-label="مشاهده {{ $product->name }}"
                                            >
                                                <img
                                                    src="{{ $image }}"
                                                    alt="{{ $product->name }}"
                                                    loading="lazy"
                                                    class="h-full w-full object-contain transition-transform duration-700 ease-out group-hover:scale-110"
                                                >
                                            </a>

                                            {{-- Image Overlay --}}
                                            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-slate-900/5 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>

                                            {{-- Variant --}}
                                            @if($variant)
                                                <span class="absolute right-2.5 top-2.5 max-w-[80%] truncate rounded-lg bg-amber-500 px-2.5 py-1.5 text-[10px] font-bold text-white shadow-lg shadow-amber-500/20 sm:right-3 sm:top-3 sm:text-xs">
                                                    {{ $variant }}
                                                </span>
                                            @endif

                                            {{-- Product Type --}}
                                            <span class="absolute bottom-2.5 left-2.5 rounded-lg border border-white/80 bg-white/90 px-2 py-1 text-[9px] font-bold text-slate-600 shadow-sm backdrop-blur-md sm:text-[10px]">
                                                پیش‌فروش
                                            </span>

                                        </div>

                                        {{-- Product Information --}}
                                        <div class="flex flex-1 flex-col p-3 sm:p-4">

                                            <a
                                                href="{{ route('presale.product', [$campaign, $pp]) }}"
                                                class="line-clamp-2 min-h-[2.8rem] text-xs font-extrabold leading-6 text-slate-800 transition-colors duration-300 hover:text-amber-600 sm:text-sm"
                                            >
                                                {{ $product->name }}
                                            </a>

                                            {{-- Brand --}}
                                            <div class="mt-1.5 min-h-5">
                                                @if($product->brand->name ?? false)
                                                    <p class="truncate text-[10px] text-slate-400 sm:text-xs">
                                                        {{ $product->brand->name }}
                                                    </p>
                                                @endif
                                            </div>

                                            {{-- Deposit --}}
                                            <div class="mt-3 rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-green-50/50 p-2.5 transition-colors duration-300 group-hover:border-emerald-200 sm:p-3">

                                                <div class="flex items-center justify-between gap-1">
                                                    <span class="text-[10px] font-semibold text-emerald-700">
                                                        مبلغ بیعانه
                                                    </span>

                                                    <svg class="h-3.5 w-3.5 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v20m5-16H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                                    </svg>
                                                </div>

                                                <div class="mt-1 flex flex-wrap items-baseline gap-x-1">
                                                    <strong class="text-base font-black tracking-tight text-emerald-700 tabular-nums sm:text-lg">
                                                        {{ number_format($pp->presale_price) }}
                                                    </strong>

                                                    <span class="text-[10px] font-medium text-emerald-700">
                                                        تومان
                                                    </span>
                                                </div>

                                            </div>

                                            {{-- Add To Cart --}}
                                            <form
                                                action="{{ route('cart.presale.store') }}"
                                                method="POST"
                                                class="mt-auto pt-3"
                                            >
                                                @csrf

                                                <input
                                                    type="hidden"
                                                    name="presale_product_id"
                                                    value="{{ $pp->id }}"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="quantity"
                                                    value="1"
                                                >

                                                <button
                                                    type="submit"
                                                    class="group/btn flex min-h-10 w-full items-center justify-center gap-1.5 rounded-xl bg-slate-900 px-2 py-2.5 text-[10px] font-bold text-white transition-all duration-300 hover:-translate-y-0.5 hover:bg-amber-500 hover:text-slate-950 hover:shadow-lg hover:shadow-amber-500/20 focus:outline-none focus:ring-4 focus:ring-amber-400/20 active:scale-[0.97] sm:min-h-11 sm:text-xs"
                                                >
                                                    <svg class="h-4 w-4 shrink-0 transition-transform duration-300 group-hover/btn:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h1.5c.7 0 1.3.48 1.48 1.16L6.5 7.5m0 0h13.75l-1.5 8H8L6.5 7.5Zm1.5 11.25a1.25 1.25 0 1 0 0 2.5 1.25 1.25 0 0 0 0-2.5Zm9 0a1.25 1.25 0 1 0 0 2.5 1.25 1.25 0 0 0 0-2.5Z"/>
                                                    </svg>

                                                    افزودن به سبد
                                                </button>
                                            </form>

                                        </div>
                                    </article>

                                @endforeach

                            </div>
                        </div>

                        {{-- Scroll Hint --}}
                        <div class="mt-1 flex items-center justify-center gap-2 text-[10px] font-medium text-slate-400 sm:justify-start sm:text-xs">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h8m-4-4 4 4-4 4"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 1 0 18 0 9 9 0 0 0-18 0Z"/>
                            </svg>
                            برای مشاهده محصولات بیشتر، افقی اسکرول کنید.
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Component Styles --}}
    <style>
        #{{ $sectionId }} .ph-scroll {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
            scroll-behavior: smooth;
            scroll-snap-type: x proximity;
            -webkit-overflow-scrolling: touch;
        }

        #{{ $sectionId }} .ph-scroll::-webkit-scrollbar {
            height: 5px;
        }

        #{{ $sectionId }} .ph-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        #{{ $sectionId }} .ph-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        #{{ $sectionId }} .ph-card {
            scroll-snap-align: start;
        }

        /* Countdown */
        #{{ $sectionId }} .ph-timer {
            position: relative;
            display: flex;
            align-items: center;
            gap: 20px;
            min-height: 82px;
            padding: 13px 18px;
            overflow: hidden;
            isolation: isolate;
            border: 1px solid rgba(251, 191, 36, .28);
            border-radius: 20px;
            background:
                radial-gradient(circle at 10% 0%, rgba(245, 158, 11, .2), transparent 45%),
                linear-gradient(135deg, #0f172a 0%, #1e293b 65%, #273449 100%);
            box-shadow:
                0 8px 24px rgba(15, 23, 42, .13),
                inset 0 1px 0 rgba(255, 255, 255, .08);
            color: #fff;
        }

        #{{ $sectionId }} .ph-timer-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        #{{ $sectionId }} .ph-timer-icon {
            display: flex;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(251, 191, 36, .3);
            border-radius: 14px;
            background: rgba(245, 158, 11, .13);
            color: #fbbf24;
            animation: ph-clock-pulse 2.5s ease-in-out infinite;
        }

        #{{ $sectionId }} .ph-timer-icon svg {
            width: 22px;
            height: 22px;
        }

        #{{ $sectionId }} .ph-timer-heading strong {
            display: block;
            color: #fff;
            font-size: 12px;
            font-weight: 900;
            white-space: nowrap;
        }

        #{{ $sectionId }} .ph-timer-heading small {
            display: block;
            margin-top: 4px;
            color: #cbd5e1;
            font-size: 10px;
            white-space: nowrap;
        }

        #{{ $sectionId }} .ph-timer-countdown {
            display: flex;
            align-items: center;
            gap: 7px;
            direction: ltr;
        }

        #{{ $sectionId }} .ph-timer-unit {
            display: flex;
            min-width: 48px;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }

        #{{ $sectionId }} .ph-timer-unit b {
            display: flex;
            width: 48px;
            height: 43px;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 11px;
            background: rgba(255, 255, 255, .09);
            color: #fbbf24;
            font-size: 23px;
            font-weight: 900;
            line-height: 1;
            font-variant-numeric: tabular-nums;
            text-shadow: 0 0 16px rgba(251, 191, 36, .2);
            transition: transform .2s ease, background .2s ease;
        }

        #{{ $sectionId }} .ph-timer-unit i {
            color: #cbd5e1;
            font-size: 9px;
            font-style: normal;
            font-weight: 600;
        }

        #{{ $sectionId }} .ph-timer-separator {
            margin-top: -15px;
            color: rgba(251, 191, 36, .7);
            font-size: 19px;
            font-weight: 900;
            animation: ph-separator-blink 1s ease-in-out infinite;
        }

        #{{ $sectionId }} .ph-timer-shine {
            position: absolute;
            z-index: -1;
            top: -60%;
            right: -15%;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(245, 158, 11, .08);
            filter: blur(25px);
            pointer-events: none;
        }

        @keyframes ph-clock-pulse {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(251, 191, 36, 0);
            }
            50% {
                box-shadow: 0 0 0 7px rgba(251, 191, 36, .07);
            }
        }

        @keyframes ph-separator-blink {
            0%, 100% { opacity: 1; }
            50% { opacity: .35; }
        }

        /* Responsive */
        @media (max-width: 640px) {
            #{{ $sectionId }} .ph-timer {
                width: 100%;
                justify-content: space-between;
                gap: 8px;
                padding: 12px;
                border-radius: 17px;
            }

            #{{ $sectionId }} .ph-timer-icon {
                width: 35px;
                height: 35px;
                border-radius: 11px;
            }

            #{{ $sectionId }} .ph-timer-icon svg {
                width: 19px;
                height: 19px;
            }

            #{{ $sectionId }} .ph-timer-heading {
                gap: 7px;
            }

            #{{ $sectionId }} .ph-timer-heading strong {
                font-size: 10px;
            }

            #{{ $sectionId }} .ph-timer-heading small {
                font-size: 9px;
            }

            #{{ $sectionId }} .ph-timer-countdown {
                gap: 3px;
            }

            #{{ $sectionId }} .ph-timer-unit {
                min-width: 32px;
                gap: 4px;
            }

            #{{ $sectionId }} .ph-timer-unit b {
                width: 32px;
                height: 35px;
                border-radius: 8px;
                font-size: 16px;
            }

            #{{ $sectionId }} .ph-timer-unit i {
                font-size: 8px;
            }

            #{{ $sectionId }} .ph-timer-separator {
                margin-top: -12px;
                font-size: 13px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            #{{ $sectionId }} .ph-card,
            #{{ $sectionId }} .ph-card img,
            #{{ $sectionId }} .ph-scroll,
            #{{ $sectionId }} .ph-timer-icon,
            #{{ $sectionId }} .ph-timer-separator {
                animation: none !important;
                transition: none !important;
                scroll-behavior: auto !important;
            }
        }
    </style>

    {{-- Countdown Script --}}
    @if($endsAt)
        <script>
            (function () {
                'use strict';

                const section = document.getElementById(@json($sectionId));

                if (!section) return;

                const timer = section.querySelector('.ph-timer');

                if (!timer || timer.dataset.initialized === 'true') return;

                timer.dataset.initialized = 'true';

                const endsAt = new Date(timer.dataset.endsAt).getTime();

                if (Number.isNaN(endsAt)) {
                    timer.innerHTML = '<span class="text-xs font-bold text-red-300">زمان پایان مشخص نیست</span>';
                    return;
                }

                const pad = function (number) {
                    return String(number).padStart(2, '0');
                };

                let interval = null;

                function tick() {
                    const diff = endsAt - Date.now();

                    if (diff <= 0) {
                        if (interval !== null) {
                            clearInterval(interval);
                            interval = null;
                        }

                        timer.innerHTML = `
                            <span style="display:flex;align-items:center;gap:10px;color:#fecaca;font-size:13px;font-weight:900;">
                                <span style="width:10px;height:10px;border-radius:50%;background:#ef4444;"></span>
                                کمپین به پایان رسید
                            </span>
                        `;

                        return;
                    }

                    const values = {
                        d: Math.floor(diff / 86400000),
                        h: Math.floor((diff % 86400000) / 3600000),
                        m: Math.floor((diff % 3600000) / 60000),
                        s: Math.floor((diff % 60000) / 1000)
                    };

                    Object.keys(values).forEach(function (key) {
                        const element = timer.querySelector('[data-t="' + key + '"]');

                        if (element) {
                            element.textContent = pad(values[key]);
                        }
                    });
                }

                tick();

                if (endsAt > Date.now()) {
                    interval = window.setInterval(tick, 1000);
                }
            })();
        </script>
    @endif

@endif
