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
