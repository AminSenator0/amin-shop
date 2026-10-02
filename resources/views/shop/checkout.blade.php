@extends('layouts.shop')

@section('title', 'تسویه حساب')

@section('content')
@php
    $payableBase = (int) ($subtotal - $discount);
    $currency = \App\Support\StoreSettings::get('currency', 'تومان');
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
            paymentMethod: '{{ old('payment_method', 'online') }}',
            useWallet: {{ old('use_wallet') ? 'true' : 'false' }},
            walletBalance: {{ (int) ($wallet->balance ?? 0) }},
            payableBase: {{ (int) $payableBase }},
            currency: '{{ $currency }}',
            get current() {
                return this.totals[this.shippingId] || { cost: 0, costLabel: '—', totalLabel: {{ Js::from(format_price($payableBase)) }} };
            },
            get currentTotal() {
                return this.payableBase + (this.current.cost || 0);
            },
            get walletPart() {
                return this.useWallet ? Math.min(this.walletBalance, this.currentTotal) : 0;
            },
            get remainder() {
                return this.currentTotal - this.walletPart;
            },
            get coverage() {
                return this.currentTotal > 0 ? Math.round((this.walletPart / this.currentTotal) * 100) : 0;
            },
            fmt(n) {
                return new Intl.NumberFormat('fa-IR').format(Math.round(n || 0));
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

            {{-- آدرس ارسال --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    <h2 class="font-black text-gray-800">آدرس ارسال</h2>
                </div>
                @if($addresses->isEmpty())
                    <p class="text-shop-muted mb-4">آدرسی ثبت نشده است.</p>
                    <a href="{{ route('user.addresses.create') }}" class="text-shop-primary hover:underline">افزودن آدرس</a>
                @else
                    <div class="space-y-3">
                        @foreach($addresses as $address)
                            <label class="flex items-start gap-3 rounded-2xl border-2 border-gray-200 p-4 cursor-pointer transition hover:border-teal-400 has-[:checked]:border-teal-500 has-[:checked]:bg-teal-50/50">
                                <input type="radio" name="address_id" value="{{ $address->id }}" @checked(old('address_id', $defaultAddress?->id) == $address->id) class="mt-1 accent-teal-600">
                                <div>
                                    <p class="font-bold text-gray-800">{{ $address->title }} — {{ $address->full_name }}</p>
                                    <p class="text-sm text-shop-muted">{{ $address->fullAddress() }}</p>
                                    <p class="text-sm text-shop-muted" dir="ltr">{{ $address->phone }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    <a href="{{ route('user.addresses.create') }}" class="text-shop-primary text-sm mt-3 inline-block hover:underline">افزودن آدرس جدید</a>
                @endif
                @error('address_id')<p class="text-rose-500 text-sm mt-2">{{ $message }}</p>@enderror
            </div>

            {{-- روش ارسال --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                    </span>
                    <h2 class="font-black text-gray-800">روش ارسال</h2>
                </div>
                <div class="space-y-3">
                    @foreach($shippingMethods as $method)
                        @php $option = $shippingTotals[$method->id]; @endphp
                        <label class="flex items-center justify-between rounded-2xl border-2 border-gray-200 p-4 cursor-pointer transition hover:border-teal-400"
                               :class="shippingId == '{{ $method->id }}' && '!border-teal-500 !bg-teal-50/50'">
                            <div class="flex items-center gap-3">
                                <input type="radio"
                                       name="shipping_method_id"
                                       value="{{ $method->id }}"
                                       x-model="shippingId"
                                       @checked((string) $initialShippingId === (string) $method->id)
                                       class="accent-teal-600">
                                <div>
                                    <p class="font-bold text-gray-800">{{ $method->name }}</p>
                                    @if($method->description)<p class="text-sm text-shop-muted">{{ $method->description }}</p>@endif
                                </div>
                            </div>
                            <span @class(['text-sm font-bold', 'text-emerald-600' => $option['cost'] === 0])>{{ $option['costLabel'] }}</span>
                        </label>
                    @endforeach
                </div>
                @error('shipping_method_id')<p class="text-rose-500 text-sm mt-2">{{ $message }}</p>@enderror
            </div>

            {{-- یادداشت --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
                <h2 class="font-bold mb-4 text-gray-800">یادداشت (اختیاری)</h2>
                <textarea name="notes" rows="3" class="w-full rounded-2xl border-2 border-gray-200 bg-gray-50 px-4 py-3 outline-none transition focus:border-teal-500 focus:bg-white" placeholder="توضیحات سفارش...">{{ old('notes') }}</textarea>
            </div>

            {{-- ═══════════ روش پرداخت + کیف پول (جدید) ═══════════ --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m3 0h3m-9.75 0H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                    </span>
                    <h2 class="font-black text-gray-800">روش پرداخت</h2>
                </div>

                {{-- انتخاب درگاه --}}
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    {{-- زرین‌پال --}}
                    <button type="button" @click="paymentMethod = 'online'"
                        :class="paymentMethod === 'online'
                            ? 'border-teal-500 bg-teal-50/70 ring-2 ring-teal-500/10'
                            : 'border-gray-200 hover:border-gray-300'"
                        class="flex items-center gap-3 rounded-2xl border-2 p-4 text-right transition">
                        <span :class="paymentMethod === 'online' ? 'border-teal-600 bg-teal-600' : 'border-gray-300 bg-white'"
                              class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition">
                            <span x-show="paymentMethod === 'online'" class="h-2 w-2 rounded-full bg-white"></span>
                        </span>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-teal-600 text-white shadow-sm">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h18M5.25 4.5h13.5A2.25 2.25 0 0121 6.75v10.5a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 17.25V6.75A2.25 2.25 0 015.25 4.5z"/></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-bold text-gray-800">پرداخت آنلاین</span>
                            <span class="mt-0.5 block text-xs text-gray-400">درگاه امن زرین‌پال</span>
                        </span>
                    </button>

                    {{-- کارت به کارت --}}
                    <button type="button" @click="paymentMethod = 'c2c'"
                        :class="paymentMethod === 'c2c'
                            ? 'border-teal-500 bg-teal-50/70 ring-2 ring-teal-500/10'
                            : 'border-gray-200 hover:border-gray-300'"
                        class="flex items-center gap-3 rounded-2xl border-2 p-4 text-right transition">
                        <span :class="paymentMethod === 'c2c' ? 'border-teal-600 bg-teal-600' : 'border-gray-300 bg-white'"
                              class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition">
                            <span x-show="paymentMethod === 'c2c'" class="h-2 w-2 rounded-full bg-white"></span>
                        </span>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M3.75 5.25h16.5A1.5 1.5 0 0121.75 6.75v10.5a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5V6.75a1.5 1.5 0 011.5-1.5z"/></svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center gap-2">
                                <span class="block text-sm font-bold text-gray-800">کارت به کارت</span>
                                <span class="inline-flex items-center rounded-full bg-gradient-to-l from-emerald-500 to-emerald-600 px-2 py-0.5 text-[10px] font-bold text-white shadow-sm">۱٪ تخفیف</span>
                            </span>
                            <span class="mt-0.5 block text-xs text-gray-400">واریز مستقیم به کارت فروشنده</span>
                        </span>
                    </button>
                </div>

                <input type="hidden" name="payment_method" :value="paymentMethod">
                @error('payment_method')<p class="text-rose-500 text-sm mt-2">{{ $message }}</p>@enderror

                {{-- ───── کیف پول ───── --}}
                @if($wallet && $wallet->balance > 0)
                    <div class="mt-4 border-t-2 border-dashed border-gray-100 pt-4">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-600 to-emerald-500 text-white shadow-md">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"/></svg>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-gray-800">پرداخت با کیف پول</p>
                                    <p class="text-xs text-gray-400">موجودی: {{ format_price($wallet->balance) }}</p>
                                </div>
                            </div>

                            {{-- تگل سوییچ --}}
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" name="use_wallet" value="1" x-model="useWallet" class="peer sr-only">
                                <span class="block h-7 w-12 rounded-full bg-gray-300 transition peer-checked:bg-teal-600"></span>
                                <span class="absolute right-1 top-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:-translate-x-5"></span>
                            </label>
                        </div>

                        {{-- جزئیات تخصیص --}}
                        <div x-show="useWallet" x-cloak
                             class="mt-4 space-y-3 rounded-2xl border border-teal-100 bg-teal-50/50 p-4">

                            {{-- نوار پیشرفت پوشش --}}
                            <div>
                                <div class="mb-1.5 flex items-center justify-between text-xs">
                                    <span class="font-bold text-teal-700">پوشش با کیف پول</span>
                                    <span class="font-black text-teal-700" x-text="coverage + '٪'"></span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-teal-100">
                                    <div class="h-full rounded-full bg-gradient-to-l from-teal-600 to-emerald-500 transition-all duration-300"
                                         :style="'width: ' + coverage + '%'"></div>
                                </div>
                            </div>

                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between" x-show="walletPart > 0">
                                    <span class="text-gray-500">از کیف پول</span>
                                    <span class="font-bold text-teal-700" x-text="'−' + fmt(walletPart) + ' ' + currency"></span>
                                </div>
                                <div class="flex items-center justify-between" x-show="remainder > 0">
                                    <span class="text-gray-500">مابقی از درگاه</span>
                                    <span class="font-black text-gray-800" x-text="fmt(remainder) + ' ' + currency"></span>
                                </div>
                            </div>

                            <div x-show="remainder === 0"
                                 class="rounded-xl bg-emerald-50 p-3 text-center text-sm font-bold text-emerald-700">
                                کل مبلغ با کیف پول پرداخت می‌شود — بدون نیاز به درگاه
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @if($addresses->isNotEmpty())
                <button type="submit" @disabled($minOrderAmount > 0 && $subtotal < $minOrderAmount) class="btn-primary w-full !rounded-xl !py-3.5 disabled:cursor-not-allowed disabled:opacity-50">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m3 0h3m-9.75 0H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
                    <span x-text="useWallet && remainder === 0 ? 'ثبت سفارش (پرداخت با کیف پول)' : 'ثبت سفارش و پرداخت'">ثبت سفارش و پرداخت</span>
                    <span class="opacity-90" x-text="'(' + (useWallet && walletPart > 0 ? fmt(remainder) + ' ' + currency : current.totalLabel) + ')'"></span>
                </button>
            @endif
        </form>
    </div>

    <div class="checkout-summary-sidebar space-y-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
            <h2 class="font-bold mb-4 text-gray-800">کد تخفیف</h2>
            @if($appliedCoupon)
                <p class="text-sm text-emerald-600 mb-2">{{ $appliedCoupon->code }} اعمال شد</p>
                <form method="POST" action="{{ route('checkout.coupon.destroy') }}">
                    @csrf @method('DELETE')
                    <button
                        type="submit"
                        data-confirm-title="حذف کد تخفیف"
                        data-confirm-message="آیا می‌خواهید کد تخفیف اعمال‌شده را حذف کنید؟"
                        class="text-rose-500 text-sm hover:underline"
                    >حذف کد تخفیف</button>
                </form>
            @else
                <form method="POST" action="{{ route('checkout.coupon.apply') }}" class="flex gap-2">
                    @csrf
                    <input type="text" name="code" placeholder="کد تخفیف" class="flex-1 rounded-xl border-2 border-gray-200 bg-gray-50 px-3 py-2.5 text-sm outline-none transition focus:border-teal-500 focus:bg-white">
                    <button type="submit" class="btn-primary !rounded-xl !px-4 !py-2 !text-sm">اعمال</button>
                </form>
            @endif
        </div>

        <div class="checkout-summary-box bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sm:p-6">
            <h2 class="font-bold mb-4 text-gray-800">خلاصه سفارش</h2>
            <div class="checkout-summary-items">
            @foreach($items as $item)
                <div class="flex justify-between gap-3 text-sm py-2 border-b border-gray-50">
                    <span class="text-gray-600">{{ $item['product']->name }} × {{ $item['quantity'] }}</span>
                    <span class="font-bold whitespace-nowrap">{{ format_price($item['subtotal']) }}</span>
                </div>
            @endforeach
            </div>
            <div class="flex justify-between text-sm mt-3">
                <span class="text-gray-500">جمع محصولات</span>
                <span class="font-bold">{{ format_price($subtotal) }}</span>
            </div>
            @if($discount > 0)
                <div class="flex justify-between text-sm text-emerald-600 mt-1">
                    <span>تخفیف</span>
                    <span class="font-bold">-{{ format_price($discount) }}</span>
                </div>
            @endif
            <div class="flex justify-between text-sm mt-2">
                <span class="text-gray-500">هزینه ارسال</span>
                <span x-text="current.costLabel"
                      :class="current.cost === 0 ? 'text-emerald-600 font-bold' : 'font-bold'">
                    {{ $shippingTotals[$initialShippingId]['costLabel'] ?? '—' }}
                </span>
            </div>

            @if($wallet && $wallet->balance > 0)
                <div class="flex justify-between text-sm mt-2 text-teal-700" x-show="useWallet && walletPart > 0" x-cloak>
                    <span>کسر از کیف پول</span>
                    <span class="font-bold" x-text="'−' + fmt(walletPart) + ' ' + currency"></span>
                </div>
            @endif

            <div class="flex justify-between font-black mt-4 pt-4 border-t-2 border-gray-100 text-base">
                <span x-text="useWallet && walletPart > 0 ? 'مابقی قابل پرداخت' : 'مبلغ قابل پرداخت'">مبلغ قابل پرداخت</span>
                <span class="text-shop-primary" x-text="useWallet && walletPart > 0 ? fmt(remainder) + ' ' + currency : current.totalLabel">
                    {{ $shippingTotals[$initialShippingId]['totalLabel'] ?? format_price($payableBase) }}
                </span>
            </div>
            <p class="text-xs text-shop-muted mt-2" x-show="!(useWallet && remainder === 0)">با انتخاب روش ارسال، مبلغ نهایی به‌روز می‌شود.</p>
        </div>
    </div>
</div>
</section>
@endsection