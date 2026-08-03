@extends('layouts.user')

@section('title', 'داشبورد')

@section('content')
@php
    $hour = (int) now()->format('H');
    $greeting = match (true) {
        $hour < 12 => 'صبح بخیر',
        $hour < 17 => 'ظهر بخیر',
        $hour < 20 => 'عصر بخیر',
        default => 'شب بخیر',
    };
@endphp

<div class="user-dash">
    <header class="user-dash-header">
        <div>
            <p class="user-dash-greeting">{{ $greeting }}، {{ auth()->user()->name }}</p>
            <p class="user-dash-subtitle">خلاصه کارهای مهم حساب شما</p>
        </div>
        <a href="{{ route('products.index') }}" class="user-dash-shop-btn">شروع خرید</a>
    </header>

    @if($hasActions)
        <div class="user-dash-panel">
            <h2 class="user-dash-panel-title">نیاز به توجه</h2>

            <div class="user-dash-list">
                @foreach($actionOrders as $order)
                    @php
                        $isPendingForTimer = $order->status->value === 'pending' && $order->payment_status->value === 'pending';
                        $expiresAt = $order->created_at->addMinutes(20);
                        $remainingSeconds = $isPendingForTimer ? max(0, (int) now()->diffInSeconds($expiresAt, false)) : 0;
                    @endphp

                    @if($isPendingForTimer && $remainingSeconds > 0)
                        <div x-data="{
                            remaining: {{ $remainingSeconds }},
                            timer: null,
                            format(sec) {
                                const m = Math.floor(sec / 60).toString().padStart(2, '0');
                                const s = (sec % 60).toString().padStart(2, '0');
                                return m + ':' + s;
                            },
                            init() {
                                this.timer = setInterval(() => {
                                    this.remaining--;
                                    if (this.remaining <= 0) {
                                        clearInterval(this.timer);
                                        window.location.reload();
                                    }
                                }, 1000);
                            }
                        }" x-init="init()" class="mb-1.5 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700">
                            ⏳ مهلت پرداخت: <span x-text="format(remaining)" class="font-mono"></span>
                        </div>
                    @elseif($isPendingForTimer && $remainingSeconds <= 0)
                        <div class="mb-1.5 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700">
                            ⚠️ مهلت پرداخت به پایان رسید
                        </div>
                    @endif

                    <x-user.order-row :order="$order" />
                @endforeach

                @foreach($unreadMessages as $message)
                    <a href="{{ route('user.messages.show', $message) }}" class="user-dash-row">
                        <div class="user-dash-row-main">
                            <p class="user-dash-row-title">{{ $message->subject }}</p>
                            <p class="user-dash-row-meta">پیام جدید از پشتیبانی</p>
                        </div>
                        <span class="user-dash-row-badge">جدید</span>
                    </a>
                @endforeach

                @if($pendingReviewCount > 0)
                    <a href="{{ route('user.reviews.index') }}" class="user-dash-row">
                        <div class="user-dash-row-main">
                            <p class="user-dash-row-title">{{ $pendingReviewCount }} محصول در انتظار نظر</p>
                            <p class="user-dash-row-meta">نظر شما به دیگر خریداران کمک می‌کند</p>
                        </div>
                        <span class="user-dash-row-cta">ثبت نظر</span>
                    </a>
                @endif

                @if($openReturnsCount > 0)
                    <a href="{{ route('user.returns.index') }}" class="user-dash-row">
                        <div class="user-dash-row-main">
                            <p class="user-dash-row-title">{{ $openReturnsCount }} درخواست مرجوعی باز</p>
                            <p class="user-dash-row-meta">پیگیری وضعیت مرجوعی</p>
                        </div>
                        <span class="user-dash-row-cta">مشاهده</span>
                    </a>
                @endif
            </div>
        </div>
    @else
        <div class="user-dash-empty">
            <p class="user-dash-empty-title">همه‌چیز مرتب است</p>
            <p class="user-dash-empty-desc">فعلاً کاری برای انجام ندارید. از منوی کناری به بخش‌های مختلف دسترسی دارید.</p>
        </div>
    @endif
</div>
@endsection