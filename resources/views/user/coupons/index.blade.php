@extends('layouts.user')

@section('title', 'کدهای تخفیف')

@section('content')
<x-user.breadcrumb :items="[
    ['label' => 'داشبورد', 'url' => route('user.dashboard')],
    ['label' => 'کدهای تخفیف'],
]" />

<x-user.page-header
    title="کدهای تخفیف من"
    subtitle="کدهای تخفیف خود را ذخیره کنید و هنگام خرید از آن‌ها استفاده کنید."
/>

<div class="user-coupon-add">
    <form method="POST" action="{{ route('user.coupons.store') }}" class="user-coupon-add-form">
        @csrf
        <label for="coupon-code" class="sr-only">کد تخفیف</label>
        <input
            id="coupon-code"
            type="text"
            name="code"
            value="{{ old('code') }}"
            placeholder="کد تخفیف را وارد کنید"
            class="user-coupon-input"
            dir="ltr"
            autocomplete="off"
            required
        >
        <button type="submit" class="btn-primary !text-sm shrink-0">افزودن کد</button>
    </form>
    @error('code')
        <p class="mt-2 text-sm font-bold text-rose-600">{{ $message }}</p>
    @enderror
</div>

@if($coupons->isEmpty())
    <x-user.empty-state
        icon="coupon"
        title="کد تخفیفی ندارید"
        description="اگر کد تخفیف دارید، در فرم بالا وارد کنید تا در حساب شما ذخیره شود."
    />
@else
    <div class="mt-6 grid gap-4 md:grid-cols-2">
        @foreach($coupons as $coupon)
            <article @class([
                'user-coupon-card',
                'is-unavailable' => ! $coupon->isAvailable(),
            ])>
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <span class="user-coupon-code" dir="ltr">{{ $coupon->code }}</span>
                        <p class="mt-2 text-lg font-black text-shop-text">{{ $coupon->valueLabel() }} تخفیف</p>
                    </div>
                    <span @class([
                        'user-coupon-status',
                        'is-active' => $coupon->isAvailable(),
                        'is-inactive' => ! $coupon->isAvailable(),
                    ])>{{ $coupon->statusLabel() }}</span>
                </div>

                <dl class="user-coupon-meta">
                    <div>
                        <dt>نوع</dt>
                        <dd>{{ $coupon->typeLabel() }}</dd>
                    </div>
                    @if($coupon->min_order > 0)
                        <div>
                            <dt>حداقل خرید</dt>
                            <dd>{{ format_price($coupon->min_order) }}</dd>
                        </div>
                    @endif
                    @if($coupon->expires_at)
                        <div>
                            <dt>انقضا</dt>
                            <dd>{{ format_jalali($coupon->expires_at) }}</dd>
                        </div>
                    @endif
                </dl>

                <div class="mt-5 flex flex-wrap items-center gap-3">
                    @if($coupon->isAvailable())
                        <form method="POST" action="{{ route('user.coupons.apply', $coupon) }}">
                            @csrf
                            <button type="submit" class="btn-primary !text-sm">استفاده در خرید</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('user.coupons.destroy', $coupon) }}">
                        @csrf @method('DELETE')
                        <button
                            type="submit"
                            data-confirm-message="آیا می‌خواهید این کد تخفیف را از حساب خود حذف کنید؟"
                            class="text-sm font-bold text-rose-500 hover:underline"
                        >حذف</button>
                    </form>
                </div>
            </article>
        @endforeach
    </div>
@endif
@endsection
