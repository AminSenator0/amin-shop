@extends('layouts.shop')

@section('title', 'سبد خرید')

@section('content')
@php
    $itemCount = $items->sum('quantity');
    $qualifiesFreeShipping = $store['freeShippingThreshold'] > 0 && $subtotal >= $store['freeShippingThreshold'];
    $remainingForFreeShipping = $store['freeShippingThreshold'] > 0
        ? max(0, $store['freeShippingThreshold'] - $subtotal)
        : 0;
    $freeShippingProgress = $store['freeShippingThreshold'] > 0
        ? min(100, ($subtotal / $store['freeShippingThreshold']) * 100)
        : 0;
    $canCheckout = $store['minOrderAmount'] <= 0 || $subtotal >= $store['minOrderAmount'];
@endphp

<section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
    <div class="shop-flow-intro mb-6 !py-5">
        <div class="shop-flow-intro-content">
            <span class="shop-flow-step-badge">مرحله ۲ از ۴</span>
            <h1 class="shop-flow-title !text-xl sm:!text-2xl">سبد خرید شما</h1>
            <p class="shop-flow-desc">
                @if($items->isEmpty())
                    موارد انتخاب‌شده را بررسی کنید و برای تکمیل سفارش ادامه دهید.
                @else
                    {{ format_number($itemCount) }} قلم در سبد شما — موارد را بررسی کنید و برای تکمیل سفارش ادامه دهید.
                @endif
            </p>
        </div>
    </div>

    @if($store['minOrderAmount'] > 0 && !$items->isEmpty() && $subtotal < $store['minOrderAmount'])
        <div class="mb-6 rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            حداقل مبلغ سفارش {{ format_price($store['minOrderAmount']) }} است. مبلغ فعلی: {{ format_price($subtotal) }}
        </div>
    @endif

    @if($items->isEmpty())
        <div class="card flex flex-col items-center p-12 text-center">
            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-shop-background text-shop-muted">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5V6a3.75 3.75 0 117.5 0v4.5"/></svg>
            </div>
            <p class="mb-2 text-lg font-bold text-shop-text">سبد خرید شما خالی است</p>
            <p class="mb-6 text-sm text-shop-muted">برای شروع، محصولات مورد علاقه‌تان را انتخاب کنید.</p>
            <a href="{{ route('products.index', ['shop' => 1]) }}" class="btn-primary !rounded-xl !px-6 !py-3">انتخاب محصول</a>
        </div>
    @else
        <div class="grid gap-6 lg:grid-cols-[1fr_360px] lg:gap-8">
            <div class="shop-cart-items">
                @foreach($items as $item)
                    <article class="shop-cart-item">
                        <a href="{{ route('products.show', $item['product']->slug) }}" class="shop-cart-item-image">
                            @if($thumbnailUrl = $item['product']->thumbnailUrl())
                                <img src="{{ $thumbnailUrl }}" alt="{{ $item['product']->name }}" loading="lazy">
                            @else
                                <svg class="shop-cart-item-image-placeholder h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H5.25A2.25 2.25 0 003 5.25v13.5A2.25 2.25 0 005.25 21z"/></svg>
                            @endif
                        </a>

                        <div class="shop-cart-item-body">
                            <div class="shop-cart-item-head">
                                <a href="{{ route('products.show', $item['product']->slug) }}" class="shop-cart-item-title">{{ $item['product']->name }}</a>
                                <div class="shop-cart-item-meta">
                                    @if($item['product']->category)
                                        <a href="{{ route('products.index', ['category' => $item['product']->category->slug]) }}" class="shop-cart-item-category">{{ $item['product']->category->name }}</a>
                                    @endif
                                    @if($item['options_label'])
                                        <span class="shop-cart-item-options">{{ $item['options_label'] }}</span>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('cart.destroy', $item['key']) }}">
                                    @csrf @method('DELETE')
                                    <button
                                        type="submit"
                                        data-confirm-title="حذف از سبد خرید"
                                        data-confirm-message="آیا می‌خواهید این محصول را از سبد خرید حذف کنید؟"
                                        class="shop-cart-item-remove"
                                        aria-label="حذف از سبد"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </div>

                            {{-- ═══ فیلدهای سفارشی ═══ --}}
                            @if(!empty($item['custom_fields_display']))
                                <div class="mt-1.5 space-y-0.5 text-xs text-zinc-500">
                                    @foreach($item['custom_fields_display'] as $cf)
                                        <div>
                                            <span class="font-medium text-zinc-600">{{ $cf['label'] }}:</span>
                                            <span>{{ $cf['value'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            {{-- ═══ پایان فیلدهای سفارشی ═══ --}}

                            <div class="shop-cart-item-meta">
                                <span class="shop-cart-item-unit-label">قیمت واحد:</span>
                                <span class="shop-cart-item-price">{{ format_price($item['product']->price) }}</span>
                            </div>

                            <div class="shop-cart-item-footer">
                                <form method="POST" action="{{ route('cart.update', $item['key']) }}" class="shop-cart-qty">
                                    @csrf @method('PATCH')
                                    <button
                                        type="button"
                                        onclick="const i=this.nextElementSibling; if(+i.value>1){i.value--; this.form.submit()}"
                                        class="shop-cart-qty-btn"
                                        aria-label="کاهش تعداد"
                                    >−</button>
                                    <input
                                        type="text"
                                        name="quantity"
                                        value="{{ $item['quantity'] }}"
                                        data-numeric-input
                                        class="shop-cart-qty-input"
                                        dir="ltr"
                                        inputmode="numeric"
                                        maxlength="4"
                                        onchange="this.form.submit()"
                                    >
                                    <button
                                        type="button"
                                        onclick="const i=this.previousElementSibling; if(+i.value<{{ $item['product']->stock }}){i.value++; this.form.submit()}"
                                        class="shop-cart-qty-btn"
                                        aria-label="افزایش تعداد"
                                        @disabled($item['quantity'] >= $item['product']->stock)
                                    >+</button>
                                </form>

                                <div class="shop-cart-item-subtotal-wrap">
                                    <span class="shop-cart-item-subtotal-label">جمع</span>
                                    <span class="shop-cart-item-subtotal">{{ format_price($item['subtotal']) }}</span>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <aside class="shop-cart-summary">
                @if($store['freeShippingThreshold'] > 0)
                    <div class="shop-cart-free-shipping {{ $qualifiesFreeShipping ? 'shop-cart-free-shipping--done' : 'shop-cart-free-shipping--pending' }}">
                        <p class="shop-cart-free-shipping-text">
                            @if($qualifiesFreeShipping)
                                <svg class="h-4 w-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>ارسال رایگان برای این سفارش فعال است</span>
                            @else
                                <svg class="h-4 w-4 shrink-0 text-shop-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                                <span>{{ format_price($remainingForFreeShipping) }} تا ارسال رایگان</span>
                            @endif
                        </p>
                        <div class="shop-cart-free-shipping-bar">
                            <div class="shop-cart-free-shipping-progress" style="width: {{ $freeShippingProgress }}%"></div>
                        </div>
                    </div>
                @endif

                <h2 class="shop-cart-summary-title">خلاصه سفارش</h2>

                <div class="shop-cart-summary-row">
                    <span>تعداد اقلام</span>
                    <span>{{ format_number($itemCount) }} عدد</span>
                </div>
                <div class="shop-cart-summary-row">
                    <span>جمع محصولات</span>
                    <span>{{ format_price($subtotal) }}</span>
                </div>
                <div class="shop-cart-summary-row">
                    <span>هزینه ارسال</span>
                    <span class="text-xs font-bold text-shop-muted">در مرحله بعد محاسبه می‌شود</span>
                </div>

                <div class="shop-cart-summary-total">
                    <span>مبلغ قابل پرداخت</span>
                    <span>{{ format_price($subtotal) }}</span>
                </div>
                <p class="shop-cart-summary-note">هزینه ارسال و تخفیف احتمالی در مرحله بعد اعمال می‌شود.</p>

                <x-shop.payment-methods class="shop-cart-payment-methods" />

                @auth
                    @if($canCheckout)
                        <a href="{{ route('checkout.index') }}" class="btn-primary shop-cart-checkout-btn">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                            ادامه به پرداخت
                        </a>
                    @else
                        <button type="button" disabled class="btn-primary shop-cart-checkout-btn is-disabled">ادامه به پرداخت</button>
                    @endif
                @else
                    <a href="{{ route('checkout.index') }}" class="btn-primary shop-cart-checkout-btn">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                        ورود و پرداخت
                    </a>
                    <p class="shop-cart-guest-note">برای پرداخت باید وارد حساب کاربری شوید.</p>
                @endauth

                <a href="{{ route('products.index', ['shop' => 1]) }}" class="shop-cart-continue-link">
                    ادامه خرید
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                </a>

                @if(!empty($store['trustBadges']))
                    <div class="shop-cart-trust">
                        @foreach(array_slice($store['trustBadges'], 0, 3) as $badge)
                            <div class="shop-cart-trust-item">
                                <span class="shop-cart-trust-icon" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $badge['icon'] }}"/></svg>
                                </span>
                                <span>{{ $badge['title'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </aside>
        </div>
    @endif
</section>
@endsection