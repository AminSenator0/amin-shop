

@extends('layouts.user')



@section('title', 'جزئیات پیش‌فروش ' . $order->order_number)



@section('content')

@php

    $totalDue = ($order->presale_final_total ?? 0) + ($order->presale_penalties_total ?? 0);
    $paid = $order->presale_total_paid ?? 0;
    $baseRemaining = max(0, $totalDue - $order->subtotal);
    $installmentsPaid = max(0, $paid - $order->payable);
    $remaining = max(0, $baseRemaining - $installmentsPaid);
    $finalPriceSet = !is_null($order->presale_final_total);

@endphp



<div class="mx-auto w-full max-w-5xl space-y-5 sm:space-y-7">



    {{-- Header --}}

    <div class="flex items-center gap-3">

        <a

            href="{{ route('user.presale-orders.index') }}"

            aria-label="بازگشت به سفارش‌های پیش‌فروش"

            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-600 transition hover:border-shop-primary hover:text-shop-primary"

        >

            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">

                <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6"/>

            </svg>

        </a>



        <div class="min-w-0">

            <h1 class="text-lg font-black text-gray-800 sm:text-2xl">

                جزئیات پیش‌فروش

            </h1>

            <p class="mt-1 truncate text-xs text-shop-muted sm:text-sm">

                سفارش {{ $order->order_number }}

            </p>

        </div>

    </div>



    {{-- Order overview --}}

    <section class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-5">

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>

                <p class="text-xs font-medium text-shop-muted">شماره سفارش</p>

                <p class="mt-1 text-base font-black tracking-wide text-gray-800 sm:text-lg">

                    {{ $order->order_number }}

                </p>

                <p class="mt-2 text-xs text-shop-muted">

                    تاریخ ثبت:

                    {{ $order->created_at->toJalali('Y/m/d H:i') }}

                </p>

            </div>



            <div class="flex items-center justify-between gap-3 border-t border-gray-100 pt-3 sm:border-0 sm:pt-0">

                <span class="text-xs text-shop-muted sm:hidden">وضعیت سفارش</span>

                <x-order-status-badge :status="$order->presale_status" />

            </div>

        </div>

    </section>

{{-- هشدار بیعانه پرداخت نشده --}}
@if($order->payment_status === \App\Enums\PaymentStatus::Pending && $order->presale_payment_deadline_at && now()->lt($order->presale_payment_deadline_at))
    <div class="mx-4 mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4 sm:mx-5">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-bold text-amber-800">
                    <svg class="mb-0.5 inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    بیعانه پرداخت نشده!
                </p>
                <p class="mt-1 text-xs text-amber-600">
                    مهلت پرداخت: {{ $order->presale_payment_deadline_at->diffForHumans() }}
                </p>
            </div>
            <form action="{{ route('user.presale-orders.pay-deposit', $order) }}" method="POST" class="inline">
    @csrf
    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow transition hover:bg-amber-600">
        پرداخت بیعانه
    </button>
</form>
        </div>
    </div>
@elseif($order->payment_status === \App\Enums\PaymentStatus::Pending && $order->presale_payment_deadline_at && now()->gte($order->presale_payment_deadline_at))
    <div class="mx-4 mb-4 rounded-xl border border-rose-200 bg-rose-50 p-4 sm:mx-5">
        <p class="text-sm font-bold text-rose-800">
            <svg class="mb-0.5 inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            مهلت پرداخت بیعانه به پایان رسید
        </p>
        <p class="mt-1 text-xs text-rose-600">
            این سفارش به‌زودی لغو می‌شود.
        </p>
    </div>
@endif

    {{-- Financial overview --}}

    <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white">



        <div class="border-b border-gray-100 px-4 py-4 sm:px-5">

            <h2 class="font-black text-gray-800">وضعیت مالی سفارش</h2>

            <p class="mt-1 text-xs text-shop-muted">

                خلاصه پرداخت‌ها و مبلغ قابل پرداخت

            </p>

        </div>



        <div class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 sm:p-5">



            <div class="rounded-xl bg-gray-50 p-4">

                <p class="text-xs text-gray-500">مبلغ پرداخت‌شده</p>

                <p class="mt-2 text-lg font-black text-emerald-600">

                    {{ format_price($order->subtotal) }}

                </p>

                <p class="mt-1 text-xs text-gray-400">

                    شامل بیعانه و اقساط تأییدشده

                </p>

            </div>



            <div class="rounded-xl border border-gray-100 bg-white p-4">

                <p class="text-xs text-gray-500">باقی‌مانده قابل پرداخت</p>

                <p class="mt-2 text-xl font-black text-shop-primary">

                    {{ format_price($remaining) }}

                </p>

                <p class="mt-1 text-xs text-gray-400">

                    {{ $finalPriceSet ? 'مبلغ نهایی پس از کسر پرداخت‌ها' : 'در انتظار تعیین قیمت نهایی' }}

                </p>

            </div>



        </div>



        <div class="space-y-3 px-4 pb-4 sm:px-5 sm:pb-5">



            <div class="flex items-center justify-between gap-3 text-sm">

                <span class="text-shop-muted">مبلغ کل پرداخت‌شده</span>

                <span class="font-bold text-gray-800">

                    {{ format_price($order->payable) }}

                </span>

            </div>

            @if($order->shipping_cost > 0)
            <div class="flex items-center justify-between gap-3 text-sm">
                <span class="text-shop-muted">هزینه ارسال</span>
                <span class="font-bold text-gray-800">+{{ format_price($order->shipping_cost) }}</span>
            </div>
            @endif
            @if($order->discount_amount > 0)
            <div class="flex items-center justify-between gap-3 text-sm">
                <span class="text-shop-muted">تخفیف (@if($order->coupon_code){{ $order->coupon_code }}@endif)</span>
                <span class="font-bold text-emerald-600">−{{ format_price($order->discount_amount) }}</span>
            </div>
            @endif

            <div class="flex items-center justify-between gap-3 text-sm">
                <span class="text-shop-muted">پیش‌پرداخت کسرشده</span>
                <span class="font-bold text-emerald-600">
                  −{{ format_price($order->subtotal) }}
                </span>
            </div>


            <div class="flex items-center justify-between gap-3 text-sm">

                <span class="text-shop-muted">قیمت نهایی محصولات</span>



                @if($finalPriceSet)

                    <span class="font-bold text-gray-800">

                        {{ format_price($order->presale_final_total) }}

                    </span>

                @else

                    <span class="text-left text-xs font-medium text-amber-600">

                        پس از بسته شدن کمپین مشخص می‌شود

                    </span>

                @endif

            </div>



            @if(($order->presale_penalties_total ?? 0) > 0)

                <div class="flex items-center justify-between gap-3 text-sm">

                    <span class="text-shop-muted">جریمه دیرکرد</span>

                    <span class="font-bold text-rose-600">

                        +{{ format_price($order->presale_penalties_total) }}

                    </span>

                </div>

            @endif



            <div class="flex items-center justify-between gap-3 border-t border-gray-200 pt-3">

                <span class="text-sm font-bold text-gray-700">

                    قیمت نهایی کل

                </span>

                <span class="text-base font-black text-gray-900">

                    {{ format_price($totalDue) }}

                </span>

            </div>



        </div>



        {{-- Payment deadline --}}

        @if($order->presale_deadline_at)

            <div class="mx-4 mb-4 flex items-start gap-3 rounded-xl p-3 text-sm sm:mx-5 sm:mb-5

                {{ $deadlinePassed ? 'bg-rose-50 text-rose-700' : 'bg-blue-50 text-blue-700' }}">



                <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">

                    <circle cx="12" cy="12" r="9"/>

                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>

                </svg>



                <div>

                    @if($deadlinePassed)

                        <p class="font-bold">

                            مهلت پرداخت به پایان رسیده است

                        </p>

                    @else

                        <p class="font-bold">مهلت پرداخت</p>

                        <p class="mt-1 text-xs">

                            {{ \Morilog\Jalali\Jalalian::fromCarbon($order->presale_deadline_at)->format('Y/m/d H:i') }}

                        </p>

                    @endif

                </div>

            </div>

        @endif



    </section>



    {{-- Products --}}

    <section class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-5">



        <div class="mb-4 flex items-center justify-between">

            <div>

                <h2 class="font-black text-gray-800">محصولات سفارش</h2>

                <p class="mt-1 text-xs text-shop-muted">

                    جزئیات کالاهای ثبت‌شده در این پیش‌فروش

                </p>

            </div>



            <span class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-600">

                {{ $order->items->count() }} کالا

            </span>

        </div>



        <div class="divide-y divide-gray-100">

            @foreach($order->items as $item)

                <div class="flex items-start gap-3 py-4 first:pt-0 last:pb-0 sm:items-center sm:gap-4">



                    <img

                        src="{{ $item->product?->thumbnailUrl() ?? asset('images/placeholder.png') }}"

                        alt="{{ $item->product_name }}"

                        loading="lazy"

                        class="h-16 w-16 shrink-0 rounded-xl border border-gray-100 bg-gray-50 object-cover sm:h-20 sm:w-20"

                    >



                    <div class="min-w-0 flex-1">

                        <p class="text-sm font-bold leading-6 text-gray-800 sm:text-base">

                            {{ $item->product_name }}

                        </p>



                        <p class="mt-1 text-xs text-shop-muted sm:text-sm">

                            {{ $item->quantity }} عدد

                            <span class="mx-1 text-gray-300">×</span>

                            {{ format_price($item->price) }}

                        </p>



                        @if(!is_null($item->presale_final_price))

                            <p class="mt-1 text-xs font-bold text-emerald-600">

                                قیمت نهایی واحد:

                                {{ format_price($item->presale_final_price) }}

                            </p>

                        @endif

                    </div>



                    <div class="shrink-0 text-left">

                        <p class="text-sm font-black text-gray-800 sm:text-base">

                            {{ format_price($item->total) }}

                        </p>

                        <p class="mt-1 text-[11px] text-gray-400">

                            مبلغ ردیف

                        </p>

                    </div>



                </div>

            @endforeach

        </div>



    </section>



    {{-- Payment history --}}

    @if($order->presalePayments->isNotEmpty())

        <section class="rounded-2xl border border-gray-200 bg-white p-4 sm:p-5">



            <div class="mb-4">

                <h2 class="font-black text-gray-800">تاریخچه پرداخت</h2>

                <p class="mt-1 text-xs text-shop-muted">

                    سوابق بیعانه و اقساط این سفارش

                </p>

            </div>



            <div class="space-y-3">

                @foreach($order->presalePayments as $payment)

                    <div class="flex flex-col gap-3 rounded-xl border border-gray-100 p-3 sm:flex-row sm:items-center sm:justify-between sm:p-4">



                        <div class="flex min-w-0 items-start gap-3">



                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl

                                {{ $payment->status === 'verified' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">



                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">

                                    <rect x="3" y="5" width="18" height="14" rx="2"/>

                                    <path stroke-linecap="round" d="M3 10h18M7 15h3"/>

                                </svg>



                            </div>



                            <div class="min-w-0">

                                <p class="text-sm font-bold text-gray-800">

                                    @if($payment->type === 'presale')

                                        بیعانه

                                    @else

                                        قسط

                                    @endif



                                    <span class="mx-1 text-gray-300">·</span>



                                    {{ $payment->method === 'zarinpal' ? 'زرین‌پال' : ($payment->method === 'wallet' ? 'کیف پول' : 'کارت به کارت') }}

                                </p>



                                <p class="mt-1 text-xs text-shop-muted">

                                    {{ $payment->created_at->toJalali('Y/m/d H:i') }}

                                </p>

                            </div>



                        </div>



                        <div class="flex items-center justify-between gap-3 border-t border-gray-100 pt-3 sm:flex-col sm:items-end sm:border-0 sm:pt-0">



                            <p class="font-black text-gray-800">

                               {{ format_price($payment->amount) }}

                            </p>



                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold

                                {{ $payment->status === 'verified' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">



                               {{ $payment->status === 'verified' ? 'تأیید شده' : ($payment->status === 'rejected' ? 'رد شده' : 'در انتظار تأیید') }}



                            </span>



                        </div>



                    </div>

                @endforeach

            </div>



        </section>

    @endif



    {{-- Installment payment --}}

    @if($remaining > 0 && !$deadlinePassed && in_array($order->presale_status, [

        \App\Enums\PresaleStatus::AwaitingPayment->value,

        \App\Enums\PresaleStatus::PartiallyPaid->value

    ]))



        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white">



            {{-- Payment header --}}

            <div class="border-b border-gray-100 px-4 py-5 sm:px-6">

                <div class="flex items-center gap-3">



                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-teal-50 text-teal-700">

                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">

                            <rect x="3" y="5" width="18" height="14" rx="2"/>

                            <path stroke-linecap="round" d="M3 10h18M7 15h3"/>

                        </svg>

                    </div>



                    <div>

                        <h2 class="font-black text-gray-800">

                            پرداخت قسط

                        </h2>

                        <p class="mt-1 text-xs text-gray-500">

                            مبلغ و روش پرداخت خود را انتخاب کنید.

                        </p>

                    </div>



                </div>

            </div>



            <div class="space-y-6 p-4 sm:p-6">



                {{-- Error --}}

                @if(session('error'))

                    <div class="flex items-start gap-2 rounded-xl border border-rose-100 bg-rose-50 p-3 text-sm text-rose-700">



                        <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">

                            <circle cx="12" cy="12" r="9"/>

                            <path stroke-linecap="round" d="M12 8v5m0 3h.01"/>

                        </svg>



                        {{ session('error') }}



                    </div>

                @endif



                <form

                    action="{{ route('user.presale-orders.pay', $order) }}"

                    method="POST"

                    id="presale-payment-form"

                    class="space-y-6"

                >

                    @csrf



                    {{-- Amount --}}

                    <div>



                        <div class="mb-2 flex items-center justify-between gap-2">

                            <label for="installment-amount-display" class="text-sm font-bold text-gray-800">

                                مبلغ قسط

                            </label>



                            <span class="text-xs text-shop-muted">

                                واحد پول: تومان

                            </span>

                        </div>



                        <div class="relative">



                            <input

                                id="installment-amount-display"

                                type="text"

                                inputmode="numeric"

                                autocomplete="off"

                                dir="ltr"

                                placeholder="مثلاً 1,500,000"

                                value="{{ number_format((int) old('amount', min($minInstallment * 2, $remaining))) }}"

                                class="w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-4 pl-16 text-left text-xl font-black tracking-wide text-gray-800 outline-none transition placeholder:text-gray-300 focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-500/10 sm:text-2xl"

                            >



                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-gray-400">

                                تومان

                            </span>



                            {{-- Raw amount sent to Laravel --}}

                            <input

                                type="hidden"

                                name="amount"

                                id="installment-amount"

                                value="{{ old('amount', min($minInstallment * 2, $remaining)) }}"

                            >



                        </div>



                        {{-- Amount in words --}}

                        <div class="mt-3 rounded-xl border border-teal-100 bg-teal-50/60 p-3">



                            <div class="flex items-start gap-2">



                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-teal-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">

                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m9-9H3"/>

                                </svg>



                                <div class="min-w-0">

                                    <p class="text-[11px] font-medium text-teal-700">

                                        مبلغ به حروف

                                    </p>



                                    <p

                                        id="installment-amount-words"

                                        class="mt-1 text-sm font-bold leading-7 text-teal-900"

                                    >

                                        در حال محاسبه...

                                    </p>

                                </div>



                            </div>



                        </div>



                        {{-- Min / Max --}}

                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">



                            <span>

                                حداقل:

                                <strong class="text-gray-700">

                                    {{ format_price($minInstallment) }}

                                </strong>

                            </span>



                            <span>

                                حداکثر:

                                <strong class="text-gray-700">

                                    {{ format_price($remaining) }}

                                </strong>

                            </span>



                        </div>



                        @error('amount')

                            <p class="mt-2 text-xs font-medium text-rose-600">

                                {{ $message }}

                            </p>

                        @enderror



                    </div>



                    {{-- Payment methods --}}

                    <div>



                        <div class="mb-3">

                            <h3 class="text-sm font-bold text-gray-800">

                                روش پرداخت

                            </h3>



                            <p class="mt-1 text-xs text-gray-500">

                                روش موردنظر خود را انتخاب کنید.

                            </p>

                        </div>



                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">



                            {{-- Zarinpal --}}

                            <label class="group relative flex cursor-pointer items-center gap-3 rounded-2xl border-2 border-gray-200 bg-white p-4 transition-all duration-200 hover:border-pink-300 hover:bg-pink-50/30 has-[:checked]:border-pink-500 has-[:checked]:bg-pink-50/60 has-[:checked]:shadow-sm">



                                <input

                                    type="radio"

                                    name="payment_method"

                                    value="online"

                                    {{ old('payment_method', 'online') === 'online' ? 'checked' : '' }}

                                    class="peer sr-only"

                                >



                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-pink-50 text-pink-600 transition group-hover:scale-105 peer-checked:bg-pink-100">



                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">

                                        <rect x="3" y="5" width="18" height="14" rx="3"/>

                                        <path stroke-linecap="round" d="M3 10h18M7 15h3"/>

                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 14l1.5 1.5L20 13"/>

                                    </svg>



                                </span>



                                <span class="min-w-0 flex-1">

                                    <span class="block text-sm font-black text-gray-800">

                                        زرین‌پال

                                    </span>

                                    <span class="mt-1 block text-[11px] text-gray-500">

                                        پرداخت آنلاین

                                    </span>

                                </span>



                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 border-gray-300 text-white transition peer-checked:border-pink-500 peer-checked:bg-pink-500">



                                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="3">

                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 10l4 4 8-8"/>

                                    </svg>



                                </span>



                            </label>



                            {{-- Wallet --}}

                            <label class="group relative flex cursor-pointer items-center gap-3 rounded-2xl border-2 border-gray-200 bg-white p-4 transition-all duration-200 hover:border-emerald-300 hover:bg-emerald-50/30 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/60 has-[:checked]:shadow-sm">



                                <input

                                    type="radio"

                                    name="payment_method"

                                    value="wallet"

                                    {{ old('payment_method') === 'wallet' ? 'checked' : '' }}

                                    class="peer sr-only"

                                >



                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition group-hover:scale-105 peer-checked:bg-emerald-100">



                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">

                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7.5A2.5 2.5 0 016.5 5H19a2 2 0 012 2v11a2 2 0 01-2 2H5a2 2 0 01-2-2V7.5z"/>

                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8h15a2 2 0 012 2v2h-5a2 2 0 000 4h5"/>

                                        <circle cx="15" cy="14" r=".8" fill="currentColor"/>

                                    </svg>



                                </span>



                                <span class="min-w-0 flex-1">

                                    <span class="block text-sm font-black text-gray-800">

                                        کیف پول

                                    </span>

                                    <span class="mt-1 block text-[11px] text-gray-500">

                                        پرداخت از اعتبار حساب

                                    </span>

                                </span>



                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 border-gray-300 text-white transition peer-checked:border-emerald-500 peer-checked:bg-emerald-500">



                                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="3">

                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 10l4 4 8-8"/>

                                    </svg>



                                </span>



                            </label>



                            {{-- Card to card --}}

                            <label class="group relative flex cursor-pointer items-center gap-3 rounded-2xl border-2 border-gray-200 bg-white p-4 transition-all duration-200 hover:border-blue-300 hover:bg-blue-50/30 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/60 has-[:checked]:shadow-sm">



                                <input

                                    type="radio"

                                    name="payment_method"

                                    value="c2c"

                                    {{ old('payment_method') === 'c2c' ? 'checked' : '' }}

                                    class="peer sr-only"

                                >



                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 transition group-hover:scale-105 peer-checked:bg-blue-100">



                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">

                                        <rect x="2.5" y="5" width="19" height="14" rx="2.5"/>

                                        <path stroke-linecap="round" d="M3 10h18"/>

                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 15h3m5 0h2"/>

                                    </svg>



                                </span>



                                <span class="min-w-0 flex-1">

                                    <span class="block text-sm font-black text-gray-800">

                                        کارت به کارت

                                    </span>

                                    <span class="mt-1 block text-[11px] text-gray-500">

                                        انتقال وجه بانکی

                                    </span>

                                </span>



                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 border-gray-300 text-white transition peer-checked:border-blue-500 peer-checked:bg-blue-500">



                                    <svg class="h-3 w-3" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="3">

                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 10l4 4 8-8"/>

                                    </svg>



                                </span>



                            </label>



                        </div>



                        @error('payment_method')

                            <p class="mt-2 text-xs font-medium text-rose-600">

                                {{ $message }}

                            </p>

                        @enderror



                    </div>



                    {{-- Submit --}}

                    <div class="border-t border-gray-100 pt-5">



                        <button

                            type="submit"

                            class="group flex w-full items-center justify-center gap-2 rounded-xl bg-shop-primary px-5 py-4 text-sm font-black text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg active:translate-y-0"

                        >



                            <svg class="h-5 w-5 transition-transform group-hover:scale-110" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">

                                <rect x="3" y="5" width="18" height="14" rx="2"/>

                                <path stroke-linecap="round" d="M3 10h18"/>

                            </svg>



                            <span>ادامه و پرداخت قسط</span>



                            <svg class="h-4 w-4 transition-transform group-hover:-translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">

                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6"/>

                            </svg>



                        </button>



                        <p class="mt-3 text-center text-[11px] leading-6 text-gray-400">

                            اطلاعات پرداخت شما مطابق روش انتخاب‌شده پردازش خواهد شد.

                        </p>



                    </div>



                </form>

            </div>



        </section>



    @elseif($remaining <= 0)



        {{-- Paid successfully --}}

        <div class="flex items-start gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-emerald-700">



            <svg class="h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">

                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12l4 4L19 6"/>

            </svg>



            <div>

                <p class="font-black">سفارش شما تسویه شده است</p>

                <p class="mt-1 text-sm">

                    تمام مبلغ این سفارش پرداخت شده است.

                </p>

            </div>



        </div>



    @elseif($deadlinePassed)



        {{-- Deadline expired --}}

        <div class="flex items-start gap-3 rounded-2xl border border-rose-100 bg-rose-50 p-4 text-rose-700">



            <svg class="h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">

                <circle cx="12" cy="12" r="9"/>

                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>

            </svg>



            <div>

                <p class="font-black">

                    مهلت پرداخت به پایان رسیده است

                </p>

                <p class="mt-1 text-sm">

                    برای بررسی وضعیت سفارش، لطفاً با پشتیبانی تماس بگیرید.

                </p>

            </div>



        </div>



    @endif



</div>


<script>
(function () {
    function initInstallmentAmount() {
        const input = document.getElementById('installment-amount-display');
        const hidden = document.getElementById('installment-amount');
        const words = document.getElementById('installment-amount-words');

        if (!input || !hidden || !words) return;

        const ones = ['', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه'];
        const teens = ['ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده', 'شانزده', 'هفده', 'هجده', 'نوزده'];
        const tens = ['', '', 'بیست', 'سی', 'چهل', 'پنجاه', 'شصت', 'هفتاد', 'هشتاد', 'نود'];
        const hundreds = ['', 'یکصد', 'دویست', 'سیصد', 'چهارصد', 'پانصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'];
        const scales = ['', 'هزار', 'میلیون', 'میلیارد', 'تریلیون'];

        function underThousand(n) {
            const parts = [];
            if (n >= 100) {
                parts.push(hundreds[Math.floor(n / 100)]);
                n %= 100;
            }
            if (n >= 10 && n < 20) {
                parts.push(teens[n - 10]);
            } else {
                if (Math.floor(n / 10)) parts.push(tens[Math.floor(n / 10)]);
                if (n % 10) parts.push(ones[n % 10]);
            }
            return parts.join(' و ');
        }

        function toPersianWords(number) {
            if (number === 0) return 'صفر';
            const parts = [];
            let scale = 0;

            while (number > 0) {
                const chunk = number % 1000;
                if (chunk) {
                    let part = underThousand(chunk);
                    if (scales[scale]) part += ' ' + scales[scale];
                    parts.unshift(part);
                }
                number = Math.floor(number / 1000);
                scale++;
            }
            return parts.join(' و ');
        }

        function normalizeDigits(value) {
            return value
                .replace(/[۰-۹]/g, function (d) { return String('۰۱۲۳۴۵۶۷۸۹'.indexOf(d)); })
                .replace(/[٠-٩]/g, function (d) { return String('٠١٢٣٤٥٦٧٨٩'.indexOf(d)); })
                .replace(/[^\d]/g, '');
        }

        function updateAmount() {
            const raw = normalizeDigits(input.value);
            const number = raw === '' ? 0 : Number(raw);

            hidden.value = raw;
            input.value = raw ? Number(raw).toLocaleString('en-US') : '';

            if (raw === '') {
                words.textContent = 'مبلغ را وارد کنید';
            } else if (!Number.isSafeInteger(number) || number > 999999999999999) {
                words.textContent = 'مبلغ واردشده بیش از حد مجاز است';
            } else {
                words.textContent = toPersianWords(number) + ' تومان';
            }
        }

        input.addEventListener('input', updateAmount);

        // مقدار اولیه‌ای که از Laravel آمده نیز بلافاصله قالب‌بندی می‌شود.
        if (hidden.value) input.value = hidden.value;
        updateAmount();

        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function (event) {
                updateAmount();
                if (!hidden.value || !Number.isSafeInteger(Number(hidden.value))) {
                    event.preventDefault();
                    words.textContent = 'لطفاً مبلغ معتبر وارد کنید';
                    input.focus();
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initInstallmentAmount);
    } else {
        initInstallmentAmount();
    }
})();
</script>

@endsection
