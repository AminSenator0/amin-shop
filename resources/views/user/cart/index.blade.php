@extends('layouts.user')

@section('title', 'سبد خرید')

@section('content')
<x-user.breadcrumb :items="[
    ['label' => 'داشبورد', 'url' => route('user.dashboard')],
    ['label' => 'سبد خرید'],
]" />

<x-user.page-header
    title="سبد خرید"
    subtitle="محصولات انتخاب‌شده برای خرید در این بخش نمایش داده می‌شود."
/>

@if($store['minOrderAmount'] > 0 && ! $items->isEmpty() && $subtotal < $store['minOrderAmount'])
    <x-user.alert-banner
        type="warning"
        title="حداقل مبلغ سفارش رعایت نشده"
        :description="'حداقل مبلغ سفارش '.format_price($store['minOrderAmount']).' است. مبلغ فعلی: '.format_price($subtotal)"
    >
        <x-slot:icon>
            <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
        </x-slot:icon>
    </x-user.alert-banner>
@endif

@if($items->isEmpty())
    <x-user.empty-state
        icon="cart"
        title="سبد خرید شما خالی است"
        description="محصولات مورد نظر را به سبد اضافه کنید و از اینجا تسویه حساب کنید."
        :action-url="route('products.index')"
        action-label="مشاهده محصولات"
    />
@else
    <div class="user-table-wrap overflow-x-auto">
        <table>
            <thead>
                <tr>
                    <th>محصول</th>
                    <th>قیمت</th>
                    <th>تعداد</th>
                    <th>جمع</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>
                            <a href="{{ route('products.show', $item['product']->slug) }}" class="font-bold text-shop-primary hover:underline">{{ $item['product']->name }}</a>
                            @if($item['options_label'])
                                <p class="mt-1 text-xs text-shop-muted">{{ $item['options_label'] }}</p>
                            @endif
                        </td>
                        <td>{{ format_price($item['product']->price) }}</td>
                        <td>
                            <form method="POST" action="{{ route('cart.update', $item['key']) }}">
                                @csrf
                                @method('PATCH')
                                <input
                                    type="text"
                                    name="quantity"
                                    value="{{ $item['quantity'] }}"
                                    data-numeric-input
                                    class="input-shop w-20 px-2 py-1.5 text-sm"
                                    dir="ltr"
                                    inputmode="numeric"
                                    maxlength="4"
                                    onchange="this.form.submit()"
                                >
                            </form>
                        </td>
                        <td class="font-bold">{{ format_price($item['subtotal']) }}</td>
                        <td>
                            <form method="POST" action="{{ route('cart.destroy', $item['key']) }}">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    data-confirm-title="حذف از سبد خرید"
                                    data-confirm-message="آیا می‌خواهید این محصول را از سبد خرید حذف کنید؟"
                                    class="text-sm font-bold text-rose-600 hover:text-rose-700"
                                >حذف</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6 flex flex-col gap-4 rounded-3xl border border-shop-border/85 bg-shop-surface p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
        <a href="{{ route('products.index') }}" class="user-section-link">ادامه خرید</a>
        <div class="text-start sm:text-end">
            <p class="mb-3 text-lg font-black text-shop-text">جمع کل: {{ format_price($subtotal) }}</p>
            <a href="{{ route('checkout.index') }}" class="btn-primary !rounded-2xl !px-6 !py-3">تسویه حساب</a>
        </div>
    </div>
@endif
@endsection
