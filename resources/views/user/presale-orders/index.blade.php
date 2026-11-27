
@extends('layouts.user')

@section('title', 'سفارش‌های پیش‌فروش من')

@section('content')
<div class="mx-auto w-full max-w-5xl space-y-5 sm:space-y-7">

    {{-- Page header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-black tracking-tight text-gray-800 sm:text-2xl">
                سفارش‌های پیش‌فروش من
            </h1>
            <p class="mt-1 text-sm text-shop-muted">
                وضعیت سفارش‌ها، مبلغ باقی‌مانده و جزئیات پرداخت را دنبال کنید.
            </p>
        </div>

        <a href="{{ route('user.orders.index') }}"
           class="inline-flex w-fit items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-bold text-gray-600 transition hover:border-shop-primary hover:text-shop-primary">
            <span>سفارش‌های عادی</span>
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    @if($orders->isEmpty())

        {{-- Empty state --}}
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-5 py-12 text-center sm:py-16">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-50 text-gray-400">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12l1 13H5L6 7z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 9V6a3 3 0 016 0v3"/>
                </svg>
            </div>

            <h2 class="mt-4 font-bold text-gray-800">
                هنوز سفارش پیش‌فروشی ندارید
            </h2>
            <p class="mt-2 text-sm leading-6 text-gray-500">
                سفارش‌های پیش‌فروش شما پس از ثبت، در این بخش نمایش داده می‌شوند.
            </p>
        </div>

    @else

        {{-- Orders --}}
        <div class="space-y-4">
            @foreach($orders as $order)
                @php
                     $totalDue = ($order->presale_final_total ?? 0) + ($order->presale_penalties_total ?? 0);
                    $paid = $order->presale_total_paid ?? 0;
                    $baseRemaining = max(0, $totalDue - $order->subtotal);
                    $installmentsPaid = max(0, $paid - $order->payable);
                    $remaining = max(0, $baseRemaining - $installmentsPaid);
                    $finalPriceSet = !is_null($order->presale_final_total);
                @endphp

                <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white transition hover:border-gray-300">

                    {{-- Order heading --}}
                    <div class="p-4 sm:p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                            <div class="min-w-0 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-black text-gray-800 sm:text-base">
                                        {{ $order->order_number }}
                                    </span>
                                    <x-order-status-badge :status="$order->presale_status" />
                                </div>

                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-shop-muted sm:text-sm">
                                    <span>
                                        {{ $order->created_at->toJalali('Y/m/d H:i') }}
                                    </span>

                                    @if($order->presaleCampaign)
                                        <span class="text-gray-300">|</span>
                                        <span class="max-w-full truncate">
                                            {{ $order->presaleCampaign->title }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Remaining amount --}}
                            <div class="rounded-xl bg-gray-50 px-4 py-3 sm:min-w-44 sm:text-left">
                                @if($finalPriceSet)
                                    <p class="text-xs font-medium text-gray-500">
                                        مبلغ باقی‌مانده
                                    </p>
                                    <p class="mt-1 text-lg font-black text-shop-primary sm:text-xl">
                                        {{ format_price($remaining) }}
                                    </p>
                                @else
                                    <p class="text-xs font-medium text-amber-600">
                                        قیمت نهایی هنوز مشخص نشده
                                    </p>
                                    <p class="mt-1 text-sm font-bold text-amber-700">
                                        در انتظار اعلام قیمت
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Order items --}}
                    <div class="border-t border-gray-100 px-4 py-4 sm:px-5">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-xs font-bold text-gray-500">
                                محصولات سفارش
                            </h3>
                            <span class="text-xs text-gray-400">
                                {{ $order->items->count() }} کالا
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @foreach($order->items as $item)
                                <div class="flex min-w-0 items-center gap-3 rounded-xl bg-gray-50/80 p-2.5">
                                    <img
src="{{ $item->product?->thumbnailUrl() ?? asset('images/placeholder.png') }}"
                                        class="h-12 w-12 shrink-0 rounded-lg border border-gray-100 bg-white object-cover sm:h-14 sm:w-14"
                                        alt="{{ $item->product_name }}"
                                        loading="lazy"
                                    >

                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-bold text-gray-800">
                                            {{ $item->product_name }}
                                        </p>
                                        <p class="mt-1 text-xs text-shop-muted">
                                            تعداد: {{ $item->quantity }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex flex-col gap-3 border-t border-gray-100 bg-gray-50/60 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                        <p class="text-xs text-gray-500">
                            @if($finalPriceSet)
                                مبلغ پرداخت‌شده:
                                <span class="font-bold text-gray-700">
                                    {{ format_price($paid) }}
                                </span>
                            @else
                                جزئیات مبلغ پس از تعیین قیمت نهایی مشخص می‌شود.
                            @endif
                        </p>

                        <a
                            href="{{ route('user.presale-orders.show', $order) }}"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-shop-primary px-5 py-2.5 text-sm font-bold text-white transition hover:opacity-90 sm:w-auto"
                        >
                            <span>جزئیات و پرداخت</span>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                </article>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
            <div class="border-t border-gray-100 pt-5">
                {{ $orders->links() }}
            </div>
        @endif

    @endif
</div>
@endsection
