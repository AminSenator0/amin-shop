@extends('layouts.shop')

@section('title', 'پرداخت')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
<div class="mx-auto max-w-lg card p-6 text-center sm:p-8">
    <span class="shop-flow-step-badge mb-4">مرحله ۴ از ۴</span>
    <h1 class="mb-4 text-2xl font-black text-shop-text">پرداخت سفارش</h1>
    <p class="text-shop-muted mb-2">شماره سفارش: <strong>{{ $order->order_number }}</strong></p>
    <p class="text-3xl font-bold text-shop-primary mb-6">{{ format_price($order->total) }}</p>

    <x-shop.payment-methods class="mb-6 text-right" />

    <p class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-start text-sm leading-7 text-amber-900" dir="rtl">
        درگاه زرین‌پال آماده نیست. برای حالت تست، سندباکس را فعال کنید؛ برای حالت واقعی، مرچنت‌کد را از پنل مدیریت وارد کنید.
    </p>
    <a href="{{ route('checkout.payment', $order) }}" class="btn-primary shop-cart-checkout-btn !mt-0 inline-flex w-full items-center justify-center">تلاش مجدد برای اتصال به درگاه</a>
</div>
</section>
@endsection
