@extends('layouts.shop')

@section('title', 'تسویه حساب')

@section('content')
@php
    $payableBase = $subtotal - $discount;
    $shippingTotals = $shippingMethods->mapWithKeys(function ($method) use ($payableBase) {
        $cost = $method->calculateCost($payableBase);

        return [
            $method->id => [
                'cost' => $cost,
                'costLabel' => $cost > 0 ? format_price($cost) : 'رایگان',
                'totalLabel' => format_price($payableBase + $cost),
            ],
        ];
    });
    $initialShippingId = old('shipping_method_id', $shippingMethods->first()?->id);
@endphp

<section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8"
         x-data="{
            shippingId: {{ Js::from($initialShippingId ? (string) $initialShippingId : null) }},
            totals: {{ Js::from($shippingTotals) }},
            get current() {
                return this.totals[this.shippingId] || { cost: 0, costLabel: '—', totalLabel: {{ Js::from(format_price($payableBase)) }} };
            }
         }">
    <div class="shop-flow-intro mb-6 !py-5">
        <div class="shop-flow-intro-content">
            <span class="shop-flow-step-badge">مرحله ۳ از ۴</span>
            <h1 class="shop-flow-title !text-xl sm:!text-2xl">اطلاعات ارسال و تکمیل سفارش</h1>
            <p class="shop-flow-desc">آدرس و روش ارسال را انتخاب کنید تا مبلغ نهایی مشخص شود.</p>
        </div>
    </div>

@if($minOrderAmount > 0 && $subtotal < $minOrderAmount)
    <div class="mb-6 rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        حداقل مبلغ سفارش {{ format_price($minOrderAmount) }} است. مبلغ فعلی سبد: {{ format_price($subtotal) }}
    </div>
@endif

<div class="grid md:grid-cols-3 gap-8">
    <div class="md:col-span-2 space-y-6">
        <form method="POST" action="{{ route('checkout.store') }}" class="space-y-6">
            @csrf
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="font-bold mb-4">آدرس ارسال</h2>
                @if($addresses->isEmpty())
                    <p class="text-shop-muted mb-4">آدرسی ثبت نشده است.</p>
                    <a href="{{ route('user.addresses.create') }}" class="text-shop-primary hover:underline">افزودن آدرس</a>
                @else
                    <div class="space-y-3">
                        @foreach($addresses as $address)
                            <label class="flex items-start gap-3 border rounded-lg p-4 cursor-pointer hover:border-shop-primary/50">
                                <input type="radio" name="address_id" value="{{ $address->id }}" @checked(old('address_id', $defaultAddress?->id) == $address->id) class="mt-1">
                                <div>
                                    <p class="font-medium">{{ $address->title }} — {{ $address->full_name }}</p>
                                    <p class="text-sm text-shop-muted">{{ $address->fullAddress() }}</p>
                                    <p class="text-sm text-shop-muted">{{ $address->phone }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <a href="{{ route('user.addresses.create') }}" class="text-shop-primary text-sm mt-3 inline-block hover:underline">افزودن آدرس جدید</a>
                @endif
                @error('address_id')<p class="text-red-500 text-sm mt-2">{{ $message }}</p>@enderror
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="font-bold mb-4">روش ارسال</h2>
                <div class="space-y-3">
                    @foreach($shippingMethods as $method)
                        @php $option = $shippingTotals[$method->id]; @endphp
                        <label class="flex items-center justify-between border rounded-lg p-4 cursor-pointer hover:border-shop-primary/50 transition"
                               :class="shippingId == '{{ $method->id }}' && 'border-shop-primary bg-shop-primary/5'">
                            <div class="flex items-center gap-3">
                                <input type="radio"
                                       name="shipping_method_id"
                                       value="{{ $method->id }}"
                                       x-model="shippingId"
                                       @checked((string) $initialShippingId === (string) $method->id)>
                                <div>
                                    <p class="font-medium">{{ $method->name }}</p>
                                    @if($method->description)<p class="text-sm text-shop-muted">{{ $method->description }}</p>@endif
                                </div>
                            </div>
                            <span @class(['text-sm font-bold', 'text-emerald-600' => $option['cost'] === 0])>{{ $option['costLabel'] }}</span>
                        </label>
                    @endforeach
                </div>
                @error('shipping_method_id')<p class="text-red-500 text-sm mt-2">{{ $message }}</p>@enderror
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="font-bold mb-4">یادداشت (اختیاری)</h2>
                <textarea name="notes" rows="3" class="w-full border rounded-lg px-3 py-2" placeholder="توضیحات سفارش...">{{ old('notes') }}</textarea>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <x-shop.payment-methods :selectable="true" />
            </div>

            @if($addresses->isNotEmpty())
                <button type="submit" @disabled($minOrderAmount > 0 && $subtotal < $minOrderAmount) class="btn-primary w-full !rounded-xl !py-3.5 disabled:cursor-not-allowed disabled:opacity-50">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m3 0h3m-9.75 0H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                    ثبت سفارش و پرداخت
                    <span class="opacity-90" x-text="'(' + current.totalLabel + ')'"></span>
                </button>
            @endif
        </form>
    </div>

    <div class="checkout-summary-sidebar space-y-4">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-bold mb-4">کد تخفیف</h2>
            @if($appliedCoupon)
                <p class="text-sm text-green-600 mb-2">{{ $appliedCoupon->code }} اعمال شد</p>
                <form method="POST" action="{{ route('checkout.coupon.destroy') }}">
                    @csrf @method('DELETE')
                    <button
                        type="submit"
                        data-confirm-title="حذف کد تخفیف"
                        data-confirm-message="آیا می‌خواهید کد تخفیف اعمال‌شده را حذف کنید؟"
                        class="text-red-500 text-sm hover:underline"
                    >حذف کد تخفیف</button>
                </form>
            @else
                <form method="POST" action="{{ route('checkout.coupon.apply') }}" class="flex gap-2">
                    @csrf
                    <input type="text" name="code" placeholder="کد تخفیف" class="flex-1 border rounded-lg px-3 py-2 text-sm">
                    <button type="submit" class="btn-primary !rounded-lg !px-4 !py-2 !text-sm">اعمال</button>
                </form>
            @endif
        </div>

        <div class="checkout-summary-box bg-white rounded-lg shadow p-6">
            <h2 class="font-bold mb-4">خلاصه سفارش</h2>
            <div class="checkout-summary-items">
            @foreach($items as $item)
                <div class="flex justify-between text-sm py-2 border-b">
                    <span>{{ $item['product']->name }} × {{ $item['quantity'] }}</span>
                    <span>{{ format_price($item['subtotal']) }}</span>
                </div>
            @endforeach
            </div>
            <div class="flex justify-between text-sm mt-3">
                <span>جمع محصولات</span>
                <span>{{ format_price($subtotal) }}</span>
            </div>
            @if($discount > 0)
                <div class="flex justify-between text-sm text-green-600">
                    <span>تخفیف</span>
                    <span>-{{ format_price($discount) }}</span>
                </div>
            @endif
            <div class="flex justify-between text-sm mt-2">
                <span>هزینه ارسال</span>
                <span x-text="current.costLabel"
                      :class="current.cost === 0 ? 'text-emerald-600 font-bold' : ''">
                    {{ $shippingTotals[$initialShippingId]['costLabel'] ?? '—' }}
                </span>
            </div>
            <div class="flex justify-between font-bold mt-4 pt-4 border-t text-base">
                <span>مبلغ قابل پرداخت</span>
                <span class="text-shop-primary" x-text="current.totalLabel">
                    {{ $shippingTotals[$initialShippingId]['totalLabel'] ?? format_price($payableBase) }}
                </span>
            </div>
            <p class="text-xs text-shop-muted mt-2">با انتخاب روش ارسال، مبلغ نهایی به‌روز می‌شود.</p>
        </div>
    </div>
</div>
</section>
@endsection
