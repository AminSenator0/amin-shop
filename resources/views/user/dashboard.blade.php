@extends('layouts.user')

@section('title', 'داشبورد')

@section('content')

@php
$hour = (int) now()->format('H');

$greeting = match (true) {
    $hour < 5  => 'شب بخیر',
    $hour < 12 => 'صبح بخیر',
    $hour < 17 => 'ظهر بخیر',
    $hour < 20 => 'عصر بخیر',
    default    => 'شب بخیر',
};

    $statOrders  = $actionOrders->count();
    $statTickets = $unreadMessages->count();
    $statReviews = (int) ($pendingReviewCount ?? 0);
    $statReturns = (int) ($openReturnsCount ?? 0);

    $totalActions = $statOrders + $statTickets + $statReviews + $statReturns;
@endphp

<style>
    /* =========================================================
       PREMIUM DASHBOARD
       ========================================================= */

    .dashboard-shell {
        --dash-primary: #0f766e;
        --dash-primary-dark: #064e4b;
        --dash-primary-light: #2dd4bf;
        --dash-bg: #f6f8f8;

        font-family: inherit;
    }

    /* ---------- Entrance ---------- */

    .dash-reveal {
        opacity: 0;
        transform: translateY(18px);
        animation: dashReveal .65s cubic-bezier(.22, 1, .36, 1) forwards;
    }

    .dash-delay-1 { animation-delay: .05s; }
    .dash-delay-2 { animation-delay: .12s; }
    .dash-delay-3 { animation-delay: .19s; }
    .dash-delay-4 { animation-delay: .26s; }
    .dash-delay-5 { animation-delay: .33s; }

    @keyframes dashReveal {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ---------- Hero ---------- */

    .hero-dashboard {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        background:
            radial-gradient(circle at 10% 10%, rgba(255,255,255,.18), transparent 25%),
            radial-gradient(circle at 90% 90%, rgba(45,212,191,.25), transparent 30%),
            linear-gradient(135deg, #063f3c 0%, #0f766e 45%, #19a99d 100%);
        box-shadow:
            0 25px 70px rgba(15,118,110,.18),
            inset 0 1px 0 rgba(255,255,255,.15);
    }

    .hero-dashboard::before {
        content: "";
        position: absolute;
        width: 360px;
        height: 360px;
        left: -150px;
        top: -180px;
        border-radius: 999px;
        background: rgba(255,255,255,.08);
        filter: blur(1px);
        animation: heroFloat 8s ease-in-out infinite;
        z-index: -1;
    }

    .hero-dashboard::after {
        content: "";
        position: absolute;
        width: 420px;
        height: 420px;
        right: -220px;
        bottom: -250px;
        border-radius: 999px;
        background: rgba(94,234,212,.18);
        filter: blur(3px);
        animation: heroFloatReverse 10s ease-in-out infinite;
        z-index: -1;
    }

    @keyframes heroFloat {
        0%, 100% {
            transform: translate3d(0,0,0) scale(1);
        }

        50% {
            transform: translate3d(35px,25px,0) scale(1.08);
        }
    }

    @keyframes heroFloatReverse {
        0%, 100% {
            transform: translate3d(0,0,0) scale(1);
        }

        50% {
            transform: translate3d(-30px,-20px,0) scale(1.06);
        }
    }

    /* ---------- Avatar ---------- */

    .dashboard-avatar {
        position: relative;
        background: linear-gradient(
            145deg,
            rgba(255,255,255,.28),
            rgba(255,255,255,.08)
        );
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,.35),
            0 12px 30px rgba(0,0,0,.08);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
    }

    .dashboard-avatar::after {
        content: "";
        position: absolute;
        inset: -4px;
        border-radius: inherit;
        border: 1px solid rgba(255,255,255,.15);
        pointer-events: none;
    }

    /* ---------- Primary CTA ---------- */

    .premium-cta {
        position: relative;
        overflow: hidden;
        transition:
            transform .3s cubic-bezier(.22,1,.36,1),
            box-shadow .3s ease,
            background .3s ease;
    }

    .premium-cta::before {
        content: "";
        position: absolute;
        inset: 0;
        transform: translateX(-110%);
        background: linear-gradient(
            90deg,
            transparent,
            rgba(255,255,255,.45),
            transparent
        );
        transition: transform .65s ease;
    }

    .premium-cta:hover::before {
        transform: translateX(110%);
    }

    .premium-cta:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 30px rgba(0,0,0,.12);
    }

    /* ---------- Stats ---------- */

    .stat-card {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        transition:
            transform .35s cubic-bezier(.22,1,.36,1),
            box-shadow .35s ease,
            border-color .35s ease;
    }

    .stat-card::before {
        content: "";
        position: absolute;
        width: 120px;
        height: 120px;
        top: -55px;
        left: -45px;
        border-radius: 999px;
        background: var(--card-glow, rgba(20,184,166,.08));
        transition: transform .5s cubic-bezier(.22,1,.36,1);
        z-index: -1;
    }

    .stat-card:hover {
        transform: translateY(-6px);
        box-shadow:
            0 18px 45px rgba(15,23,42,.08),
            0 4px 12px rgba(15,23,42,.04);
    }

    .stat-card:hover::before {
        transform: scale(2.2);
    }

    .stat-icon {
        transition:
            transform .4s cubic-bezier(.22,1,.36,1),
            box-shadow .3s ease;
    }

    .stat-card:hover .stat-icon {
        transform: rotate(-5deg) scale(1.08);
    }

    /* ---------- Glass ---------- */

    .premium-panel {
        background: rgba(255,255,255,.88);
        border: 1px solid rgba(226,232,240,.85);
        box-shadow:
            0 20px 60px rgba(15,23,42,.045),
            0 2px 10px rgba(15,23,42,.025);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
    }

    /* ---------- Action item ---------- */

    .action-item {
        transition:
            transform .3s cubic-bezier(.22,1,.36,1),
            background .3s ease,
            border-color .3s ease,
            box-shadow .3s ease;
    }

    .action-item:hover {
        transform: translateX(-4px);
        box-shadow: 0 12px 30px rgba(15,23,42,.05);
    }

    [dir="rtl"] .action-item:hover {
        transform: translateX(4px);
    }

    /* ---------- Pulse ---------- */

    .live-dot {
        position: relative;
    }

    .live-dot::before {
        content: "";
        position: absolute;
        inset: -4px;
        border-radius: 999px;
        background: currentColor;
        opacity: .18;
        animation: livePulse 2s ease-out infinite;
    }

    @keyframes livePulse {
        0% {
            transform: scale(.7);
            opacity: .35;
        }

        70%, 100% {
            transform: scale(1.7);
            opacity: 0;
        }
    }

    /* ---------- Number ---------- */

    .stat-number {
        font-variant-numeric: tabular-nums;
        letter-spacing: -.04em;
    }

    /* ---------- Mobile ---------- */

    @media (max-width: 640px) {
        .hero-dashboard {
            border-radius: 24px;
        }

        .stat-card {
            border-radius: 20px;
        }

        .premium-panel {
            border-radius: 24px;
        }

        .stat-number {
            font-size: 1.65rem;
        }
    }

    /* ---------- Accessibility ---------- */

    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
            scroll-behavior: auto !important;
        }

        .dash-reveal {
            opacity: 1;
            transform: none;
        }
    }
</style>


<div
    class="dashboard-shell mx-auto w-full min-w-0 max-w-6xl overflow-x-clip px-3 py-4 sm:px-5 sm:py-7 lg:px-6"
    dir="rtl"
>

    {{-- =========================================================
         HERO
         ========================================================= --}}
    <section
        class="hero-dashboard dash-reveal dash-delay-1 mb-5 rounded-[28px] p-5 text-white sm:mb-7 sm:rounded-[32px] sm:p-7 lg:p-8"
    >

        <div class="relative z-10 flex flex-col gap-7 lg:flex-row lg:items-center lg:justify-between">

            {{-- User --}}
            <div class="flex min-w-0 items-center gap-4 sm:gap-5">

                <div
                    class="dashboard-avatar flex h-16 w-16 shrink-0 items-center justify-center rounded-[22px] text-2xl font-black sm:h-20 sm:w-20 sm:rounded-[26px] sm:text-3xl"
                >
                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                </div>

                <div class="min-w-0">

                    <div class="mb-2 flex items-center gap-2">
                        <span class="live-dot relative h-2 w-2 rounded-full bg-emerald-300 text-emerald-300"></span>

                        <span class="text-[11px] font-bold tracking-wide text-teal-50/75 sm:text-xs">
                            پنل کاربری
                        </span>
                    </div>

                    <h1 class="truncate text-xl font-black tracking-tight sm:text-2xl lg:text-[28px]">
                        {{ $greeting }}، {{ auth()->user()->name }}
                    </h1>

                    <p class="mt-2 text-[11px] leading-6 text-teal-50/75 sm:text-sm">
                        {{ format_jalali(now(), 'l، d F Y') }}
                        <span class="mx-1 opacity-40">•</span>
                        خوشحالیم که دوباره اینجایی
                    </p>

                </div>
            </div>


            {{-- CTA --}}
            <a
                href="{{ route('products.index') }}"
                class="premium-cta group inline-flex w-full items-center justify-center gap-2.5 rounded-2xl bg-white px-5 py-4 text-sm font-black text-[#075e59] shadow-xl shadow-black/10 sm:w-auto sm:px-6"
            >

                <span class="relative z-10 flex items-center gap-2.5">

                    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-teal-50">
                        <svg
                            class="h-4 w-4 transition-transform duration-300 group-hover:-rotate-6 group-hover:scale-110"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974a1.125 1.125 0 011.119 1.007z"
                            />
                        </svg>
                    </span>

                    شروع خرید

                    <svg
                        class="h-4 w-4 transition-transform duration-300 group-hover:-translate-x-1"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>

                </span>

            </a>

        </div>


        {{-- Mini insight --}}
        <div class="relative z-10 mt-7 flex items-center gap-3 border-t border-white/10 pt-5">

            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10">
                <svg class="h-4 w-4 text-teal-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </span>

            <p class="text-[11px] font-medium leading-5 text-teal-50/70 sm:text-xs">
                @if($totalActions > 0)
                    {{ to_persian_digits((string) $totalActions) }} مورد برای بررسی در داشبورد شما وجود دارد.
                @else
                    حساب شما کاملاً مرتب است. آماده خرید بعدی هستید.
                @endif
            </p>

        </div>

    </section>


    {{-- =========================================================
         STATS
         ========================================================= --}}
    <section class="dash-reveal dash-delay-2 mb-5 sm:mb-7">

        <div class="mb-3 flex items-end justify-between px-1 sm:mb-4">

            <div>
                <p class="text-[10px] font-bold uppercase tracking-[.15em] text-teal-600">
                    Overview
                </p>

                <h2 class="mt-1 text-base font-black text-slate-900 sm:text-lg">
                    خلاصه فعالیت
                </h2>
            </div>

            <span class="text-[10px] font-medium text-slate-400 sm:text-xs">
                وضعیت فعلی حساب
            </span>

        </div>


        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">

            {{-- Orders --}}
            <a
                href="{{ route('user.orders.index') }}"
                style="--card-glow: rgba(20,184,166,.10)"
                class="stat-card group rounded-[22px] border border-slate-200/80 bg-white p-4 shadow-[0_5px_20px_rgba(15,23,42,.035)] sm:rounded-[26px] sm:p-5"
            >

                <div class="flex items-start justify-between gap-2">

                    <span class="stat-icon flex h-11 w-11 items-center justify-center rounded-2xl bg-teal-50 text-teal-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-4.5-9 4.5m18 0l-9 4.5m9-4.5V19.5m-9-9l-9 4.5m9-4.5V19.5m0-9L3 12m6 7.5h6"/>
                        </svg>
                    </span>

                    @if($statOrders > 0)
                        <span class="rounded-full bg-teal-50 px-2 py-1 text-[9px] font-black text-teal-600">
                            فعال
                        </span>
                    @endif

                </div>

                <p class="stat-number mt-4 text-2xl font-black text-slate-900 sm:text-[30px]">
                    {{ to_persian_digits((string) $statOrders) }}
                </p>

                <p class="mt-1 text-[10px] font-bold leading-5 text-slate-400 sm:text-xs">
                    سفارش در انتظار بررسی
                </p>

            </a>


            {{-- Tickets --}}
            <a
                href="{{ route('user.messages.index') }}"
                style="--card-glow: rgba(139,92,246,.10)"
                class="stat-card group rounded-[22px] border border-slate-200/80 bg-white p-4 shadow-[0_5px_20px_rgba(15,23,42,.035)] sm:rounded-[26px] sm:p-5"
            >

                <div class="flex items-start justify-between">

                    <span class="stat-icon flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-50 text-violet-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 4.556-4.03 8.25-9 8.25a9.76 9.76 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
                        </svg>
                    </span>

                    @if($statTickets > 0)
                        <span class="rounded-full bg-violet-50 px-2 py-1 text-[9px] font-black text-violet-600">
                            جدید
                        </span>
                    @endif

                </div>

                <p class="stat-number mt-4 text-2xl font-black text-slate-900 sm:text-[30px]">
                    {{ to_persian_digits((string) $statTickets) }}
                </p>

                <p class="mt-1 text-[10px] font-bold leading-5 text-slate-400 sm:text-xs">
                    تیکت در انتظار پاسخ
                </p>

            </a>


            {{-- Reviews --}}
            <a
                href="{{ route('user.reviews.index') }}"
                style="--card-glow: rgba(245,158,11,.11)"
                class="stat-card group rounded-[22px] border border-slate-200/80 bg-white p-4 shadow-[0_5px_20px_rgba(15,23,42,.035)] sm:rounded-[26px] sm:p-5"
            >

                <div class="flex items-start justify-between">

                    <span class="stat-icon flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.56.56 0 011.04 0l2.125 5.111a.56.56 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.56.56 0 00-.182.557l1.285 5.385a.56.56 0 01-.84.61l-4.725-2.885a.56.56 0 00-.586 0L6.982 20.54a.56.56 0 01-.84-.61l1.285-5.386a.56.56 0 00-.182-.557l-4.204-3.602a.56.56 0 01.321-.988l5.518-.442a.56.56 0 00.475-.345L11.48 3.5z"/>
                        </svg>
                    </span>

                </div>

                <p class="stat-number mt-4 text-2xl font-black text-slate-900 sm:text-[30px]">
                    {{ to_persian_digits((string) $statReviews) }}
                </p>

                <p class="mt-1 text-[10px] font-bold leading-5 text-slate-400 sm:text-xs">
                    دیدگاه در انتظار انتشار
                </p>

            </a>


            {{-- Returns --}}
            <a
                href="{{ route('user.returns.index') }}"
                style="--card-glow: rgba(244,63,94,.10)"
                class="stat-card group rounded-[22px] border border-slate-200/80 bg-white p-4 shadow-[0_5px_20px_rgba(15,23,42,.035)] sm:rounded-[26px] sm:p-5"
            >

                <div class="flex items-start justify-between">

                    <span class="stat-icon flex h-11 w-11 items-center justify-center rounded-2xl bg-rose-50 text-rose-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                    </span>

                </div>

                <p class="stat-number mt-4 text-2xl font-black text-slate-900 sm:text-[30px]">
                    {{ to_persian_digits((string) $statReturns) }}
                </p>

                <p class="mt-1 text-[10px] font-bold leading-5 text-slate-400 sm:text-xs">
                    مرجوعی در جریان
                </p>

            </a>

        </div>

    </section>


    {{-- =========================================================
         ACTION CENTER
         ========================================================= --}}
    @if($hasActions)

        <section class="dash-reveal dash-delay-3 premium-panel overflow-hidden rounded-[26px] sm:rounded-[30px]">

            {{-- Header --}}
            <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-4 py-5 sm:px-6 sm:py-6">

                <div class="flex min-w-0 items-center gap-3">

                    <span class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">

                        <span class="absolute inset-0 animate-ping rounded-2xl bg-amber-200/30"></span>

                        <svg class="relative h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>

                    </span>

                    <div class="min-w-0">

                        <h2 class="truncate text-sm font-black text-slate-900 sm:text-base">
                            نیاز به توجه
                        </h2>

                        <p class="mt-1 truncate text-[10px] text-slate-400 sm:text-xs">
                            کارهایی که منتظر اقدام شما هستند
                        </p>

                    </div>

                </div>


                <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1.5 text-[9px] font-black text-slate-500 sm:px-3 sm:text-[10px]">
                    {{ to_persian_digits((string) $totalActions) }} مورد
                </span>

            </div>


            {{-- Items --}}
            <div class="space-y-3 p-3 sm:p-5">

                {{-- Orders --}}
                @foreach($actionOrders as $order)

                    @php
                        $isPendingForTimer =
                            $order->status->value === 'pending'
                            && $order->payment_status->value === 'pending';

                        $expiresAt = $order->created_at->addMinutes(20);

                        $remainingSeconds = $isPendingForTimer
                            ? max(0, (int) now()->diffInSeconds($expiresAt, false))
                            : 0;

                        $progressPercent = $isPendingForTimer
                            ? min(100, max(0, ($remainingSeconds / 1200) * 100))
                            : 0;
                    @endphp


                    @if($isPendingForTimer && $remainingSeconds > 0)

                        <div
                            x-data="{
                                remaining: {{ $remainingSeconds }},
                                total: 1200,
                                timer: null,

                                format(sec) {
                                    const m = Math.floor(sec / 60).toString().padStart(2, '0');
                                    const s = (sec % 60).toString().padStart(2, '0');

                                    return m + ':' + s;
                                },

                                progress() {
                                    return Math.max(0, Math.min(100, (this.remaining / this.total) * 100));
                                },

                                init() {
                                    this.timer = setInterval(() => {
                                        this.remaining--;

                                        if (this.remaining <= 0) {
                                            clearInterval(this.timer);
                                            window.location.reload();
                                        }
                                    }, 1000);
                                },

                                destroy() {
                                    clearInterval(this.timer);
                                }
                            }"
                            x-init="init()"
                            class="relative overflow-hidden rounded-2xl border border-amber-200/80 bg-gradient-to-l from-amber-50 to-orange-50/70 p-4"
                        >

                            <div class="relative z-10 flex items-center justify-between gap-3">

                                <div class="flex min-w-0 items-center gap-3">

                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/80 text-amber-600 shadow-sm">

                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/>
                                            <circle cx="12" cy="12" r="9"/>
                                        </svg>

                                    </span>

                                    <div class="min-w-0">

                                        <p class="text-[10px] font-bold text-amber-700 sm:text-xs">
                                            مهلت پرداخت سفارش
                                        </p>

                                        <p class="mt-0.5 text-[10px] text-amber-600/70">
                                            قبل از پایان زمان، پرداخت را تکمیل کنید
                                        </p>

                                    </div>

                                </div>


                                <span
                                    x-text="format(remaining)"
                                    class="shrink-0 rounded-xl bg-white px-3 py-2 font-mono text-sm font-black tracking-wider text-amber-700 shadow-sm sm:text-base"
                                ></span>

                            </div>


                            <div class="relative z-10 mt-4 h-1.5 overflow-hidden rounded-full bg-amber-200/60">

                                <div
                                    class="h-full rounded-full bg-gradient-to-l from-amber-500 to-orange-400 transition-[width] duration-1000 ease-linear"
                                    :style="'width:' + progress() + '%'"
                                ></div>

                            </div>

                        </div>

                    @elseif($isPendingForTimer && $remainingSeconds <= 0)

                        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-xs font-bold text-rose-700">
                            ⚠️ مهلت پرداخت این سفارش به پایان رسیده است.
                        </div>

                    @endif


                    <div class="action-item overflow-hidden rounded-2xl border border-slate-100 bg-slate-50/60">
                        <x-user.order-row :order="$order" />
                    </div>

                @endforeach


                {{-- Messages --}}
                @foreach($unreadMessages as $message)

                    <a
                        href="{{ route('user.messages.show', $message) }}"
                        class="action-item flex min-w-0 items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50/60 p-3.5 sm:p-4"
                    >

                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-violet-100 text-violet-600">

                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                            </svg>

                        </span>

                        <div class="min-w-0 flex-1">

                            <p class="truncate text-xs font-black text-slate-800 sm:text-sm">
                                {{ $message->subject }}
                            </p>

                            <p class="mt-1 text-[10px] text-slate-400 sm:text-xs">
                                پیام جدید از پشتیبانی
                            </p>

                        </div>

                        <span class="shrink-0 rounded-full bg-violet-600 px-2.5 py-1.5 text-[9px] font-black text-white shadow-sm sm:px-3 sm:text-[10px]">
                            جدید
                        </span>

                    </a>

                @endforeach


                {{-- Reviews --}}
                @if($pendingReviewCount > 0)

                    <a
                        href="{{ route('user.reviews.index') }}"
                        class="action-item flex min-w-0 items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50/60 p-3.5 sm:p-4"
                    >

                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">

                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                            </svg>

                        </span>

                        <div class="min-w-0 flex-1">

                            <p class="truncate text-xs font-black text-slate-800 sm:text-sm">
                                {{ to_persian_digits((string) $pendingReviewCount) }}
                                محصول در انتظار نظر
                            </p>

                            <p class="mt-1 text-[10px] text-slate-400 sm:text-xs">
                                نظر شما به دیگر خریداران کمک می‌کند
                            </p>

                        </div>

                        <span class="shrink-0 rounded-full bg-amber-500 px-2.5 py-1.5 text-[9px] font-black text-white shadow-sm sm:px-3 sm:text-[10px]">
                            ثبت نظر
                        </span>

                    </a>

                @endif


                {{-- Returns --}}
                @if($openReturnsCount > 0)

                    <a
                        href="{{ route('user.returns.index') }}"
                        class="action-item flex min-w-0 items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50/60 p-3.5 sm:p-4"
                    >

                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">

                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                            </svg>

                        </span>

                        <div class="min-w-0 flex-1">

                            <p class="truncate text-xs font-black text-slate-800 sm:text-sm">
                                {{ to_persian_digits((string) $openReturnsCount) }}
                                درخواست مرجوعی باز
                            </p>

                            <p class="mt-1 text-[10px] text-slate-400 sm:text-xs">
                                پیگیری وضعیت مرجوعی
                            </p>

                        </div>

                        <span class="shrink-0 rounded-full bg-rose-500 px-2.5 py-1.5 text-[9px] font-black text-white shadow-sm sm:px-3 sm:text-[10px]">
                            مشاهده
                        </span>

                    </a>

                @endif

            </div>

        </section>

    @else

        {{-- =====================================================
             EMPTY STATE
             ===================================================== --}}

        <section class="dash-reveal dash-delay-3 premium-panel relative overflow-hidden rounded-[28px] px-5 py-14 text-center sm:rounded-[32px] sm:py-20">

            <div class="absolute left-1/2 top-0 h-40 w-40 -translate-x-1/2 -translate-y-1/2 rounded-full bg-teal-100/50 blur-3xl"></div>

            <div class="relative">

                <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-[26px] bg-gradient-to-br from-teal-50 to-emerald-100 text-teal-600 shadow-inner">

                    <svg class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>

                </div>

                <p class="text-base font-black text-slate-900 sm:text-lg">
                    همه‌چیز مرتب است ✨
                </p>

                <p class="mx-auto mt-2 max-w-md text-xs leading-7 text-slate-400 sm:text-sm">
                    فعلاً کاری برای انجام ندارید.
                    می‌توانید از فروشگاه دیدن کنید و محصول مورد علاقه‌تان را پیدا کنید.
                </p>

                <a
                    href="{{ route('products.index') }}"
                    class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-teal-600 px-6 py-3.5 text-xs font-black text-white shadow-lg shadow-teal-600/20 transition duration-300 hover:-translate-y-1 hover:bg-teal-700 hover:shadow-xl active:scale-95"
                >
                    مشاهده محصولات

                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>

                </a>

            </div>

        </section>

    @endif

</div>

@endsection
