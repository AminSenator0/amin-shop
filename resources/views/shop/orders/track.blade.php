@extends('layouts.shop')

@section('title', 'پیگیری سفارش')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
<div class="mx-auto max-w-2xl">
    <h1 class="text-2xl font-bold mb-2">پیگیری سفارش</h1>
    <p class="text-shop-muted mb-6">شماره سفارش یا کد رهگیری و شماره موبایل ثبت‌شده را وارد کنید.</p>

    <form method="GET" action="{{ route('orders.track') }}" class="bg-white rounded-lg shadow p-6 mb-8 space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">شماره سفارش یا کد رهگیری</label>
            <input type="text" name="code" value="{{ $code }}" placeholder="مثال: ORD-20260607-XXXXXX" class="w-full border rounded-lg px-3 py-2" dir="ltr" required>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">شماره موبایل</label>
            <input type="text" name="phone" value="{{ $phone }}" placeholder="09xxxxxxxxx" class="w-full border rounded-lg px-3 py-2" dir="ltr" required>
        </div>
        <button type="submit" class="btn-primary w-full !rounded-lg !px-6 !py-2">جستجو</button>
    </form>

    @if($code && ! $order && ! $phoneRequired && ! $phoneMismatch)
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-6 text-center">سفارشی با این کد یافت نشد.</div>
    @elseif($phoneRequired)
        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-6 text-center">برای مشاهده جزئیات، شماره موبایل ثبت‌شده در سفارش را وارد کنید.</div>
    @elseif($phoneMismatch)
        <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-lg p-6 text-center">شماره موبایل با سفارش مطابقت ندارد.</div>
    @elseif($order)
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex flex-wrap justify-between gap-4 mb-4">
                    <div>
                        <p class="text-xs text-shop-muted">شماره سفارش</p>
                        <p class="font-bold text-lg" dir="ltr">{{ $order->order_number }}</p>
                    </div>
                    <span class="bg-shop-primary/10 text-shop-primary px-3 py-1 rounded-full text-sm">{{ $order->status->label() }}</span>
                </div>

                @if($order->tracking_code)
                    <div class="mb-4 rounded-lg bg-shop-primary/5 px-4 py-3">
                        <p class="text-xs text-shop-muted">کد رهگیری مرسوله</p>
                        <p class="font-bold text-lg" dir="ltr">{{ $order->tracking_code }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-shop-muted">تاریخ ثبت</p>
                        <p class="font-medium">{{ format_jalali($order->created_at, 'Y/m/d H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-shop-muted">وضعیت پرداخت</p>
                        <p class="font-medium">{{ $order->payment_status->label() }}</p>
                    </div>
                </div>
            </div>

            <x-user.order-timeline :order="$order" />

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="font-bold mb-4">اقلام سفارش</h2>
                <div class="space-y-3">
                    @foreach($order->items as $item)
                        <div class="flex justify-between gap-4 text-sm border-b border-shop-border/40 pb-3 last:border-0 last:pb-0">
                            <div>
                                <x-order-item-name :item="$item" />
                                <p class="text-shop-muted mt-1">× {{ $item->quantity }}</p>
                            </div>
                            <span class="font-medium shrink-0">{{ format_price($item->total) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="border-t pt-4 mt-4 flex justify-between font-bold text-lg">
                    <span>جمع کل</span>
                    <span>{{ format_price($order->total) }}</span>
                </div>
            </div>
        </div>
    @endif
</div>
</section>
@endsection
