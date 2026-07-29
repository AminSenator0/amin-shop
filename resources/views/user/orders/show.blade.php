@extends('layouts.user')

@section('title', 'جزئیات سفارش')

@section('content')
<x-user.breadcrumb :items="[
    ['label' => 'داشبورد', 'url' => route('user.dashboard')],
    ['label' => 'سفارشات من', 'url' => route('user.orders.index')],
    ['label' => $order->order_number],
]" />

<div class="mb-6 flex flex-wrap items-start justify-between gap-4">
    <x-user.page-header
        class="!mb-0"
        :title="'سفارش '.$order->order_number"
        :subtitle="'ثبت‌شده در '.format_jalali($order->created_at, 'Y/m/d — H:i')"
    />
    <div class="flex flex-wrap items-center gap-2">
        <x-user.status-badge :status="$order->status" />
        <x-user.status-badge :status="$order->payment_status" />
    </div>
</div>

<div class="user-quick-actions">
    @if(in_array($order->payment_status->value, ['pending', 'failed'], true) && ! in_array($order->status->value, ['cancelled', 'shipped', 'delivered'], true))
        <a href="{{ route('checkout.payment', $order) }}" class="user-action-chip is-primary">
            {{ $order->payment_status->value === 'failed' || $order->status->value === 'failed' ? 'تلاش مجدد پرداخت' : 'پرداخت سفارش' }}
        </a>
    @endif
    @if($order->payment_status->value === 'paid')
        <a href="{{ route('user.orders.invoice', $order) }}" target="_blank" rel="noopener" class="user-action-chip">مشاهده فاکتور</a>
        <a href="{{ route('user.orders.invoice', ['order' => $order, 'format' => 'pdf']) }}" class="user-action-chip">دانلود PDF</a>
    @endif
    @if($order->tracking_code)
        <a href="{{ route('orders.track', ['code' => $order->order_number, 'phone' => $order->shipping_address['phone'] ?? '']) }}" class="user-action-chip">پیگیری مرسوله</a>
    @endif
    @if($order->status->value !== 'cancelled')
        <form method="POST" action="{{ route('user.orders.reorder', $order) }}">
            @csrf
            <button type="submit" class="user-action-chip">خرید مجدد</button>
        </form>
    @endif
    @if($order->canBeCancelledByUser())
        <form method="POST" action="{{ route('user.orders.cancel', $order) }}" data-confirm-message="آیا از لغو این سفارش مطمئن هستید؟">
            @csrf
            <button type="submit" class="user-action-chip is-danger">لغو سفارش</button>
        </form>
    @endif
</div>

<div class="mb-8 grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <x-user.order-timeline :order="$order" />
    </div>
    <div class="space-y-4">
        @if($order->tracking_code)
            <div class="user-info-card">
                <h3 class="mb-2 text-sm font-black text-shop-text">کد رهگیری پست</h3>
                <p class="rounded-2xl bg-shop-primary/5 px-4 py-3 text-center font-mono text-lg font-black text-shop-primary" dir="ltr">{{ $order->tracking_code }}</p>
                <p class="mt-2 text-xs text-shop-muted">با این کد می‌توانید وضعیت مرسوله را از سایت پست پیگیری کنید.</p>
            </div>
        @endif
        @if($return = $order->latestReturn())
            <div class="user-info-card">
                <h3 class="mb-3 text-sm font-black text-shop-text">درخواست مرجوعی</h3>
                <x-user.status-badge :status="$return->status" />
                @if($return->is_partial && $return->items->isNotEmpty())
                    <ul class="mt-2 space-y-1 text-sm text-shop-muted">
                        @foreach($return->items as $returnItem)
                            <li>{{ $returnItem->orderItem?->product_name }} × {{ $returnItem->quantity }}</li>
                        @endforeach
                    </ul>
                @endif
                <p class="mt-3 text-sm leading-relaxed text-shop-muted">{{ $return->reason }}</p>
                @if($return->admin_note)
                    <p class="mt-3 rounded-2xl bg-shop-background px-3 py-2 text-sm text-shop-text">{{ $return->admin_note }}</p>
                @endif
            </div>
        @elseif($order->canBeReturnedByUser())
            <div class="user-info-card" x-data="{ open: false }">
                <h3 class="mb-2 text-sm font-black text-shop-text">درخواست مرجوعی</h3>
                <p class="mb-4 text-sm leading-relaxed text-shop-muted">اگر محصول مشکل دارد، درخواست مرجوعی ثبت کنید تا پشتیبانی بررسی کند.</p>
                <button type="button" @click="open = !open" class="user-action-chip w-full justify-center">ثبت درخواست مرجوعی</button>
                <form x-show="open" x-cloak method="POST" action="{{ route('user.orders.returns.store', $order) }}" class="mt-4 space-y-3">
                    @csrf
                    @if($order->items->count() > 1)
                        <div class="rounded-2xl border border-shop-border/60 p-3 space-y-2">
                            <p class="text-xs font-bold text-shop-text">اقلام مرجوعی (اختیاری — خالی = کل سفارش)</p>
                            @foreach($order->items as $item)
                                <label class="flex items-center justify-between gap-3 text-sm">
                                    <span class="truncate">{{ $item->product_name }}</span>
                                    <input type="number" name="items[{{ $item->id }}]" value="{{ old('items.'.$item->id, 0) }}" min="0" max="{{ $item->quantity }}" class="input-shop w-20 px-2 py-1 text-center text-sm" dir="ltr">
                                </label>
                            @endforeach
                        </div>
                    @endif
                    <textarea name="reason" rows="4" required placeholder="دلیل مرجوعی را به فارسی بنویسید..." class="input-shop w-full px-4 py-3 text-sm">{{ old('reason') }}</textarea>
                    @error('reason')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                    <button type="submit" class="btn-primary w-full !text-sm">ارسال درخواست</button>
                </form>
            </div>
        @endif
    </div>
</div>

<div class="mb-8 grid gap-6 md:grid-cols-2">
    <div class="user-info-card">
        <h2 class="mb-4 text-base font-black text-shop-text">آدرس ارسال</h2>
        <div class="space-y-1.5 text-sm">
            <p class="font-bold">{{ $order->shipping_address['full_name'] }}</p>
            <p class="text-shop-muted">{{ $order->shipping_address['province'] }}، {{ $order->shipping_address['city'] }}</p>
            <p class="leading-relaxed text-shop-muted">{{ $order->shipping_address['address'] }}</p>
            <p class="text-shop-muted">کد پستی: <span dir="ltr">{{ $order->shipping_address['postal_code'] }}</span></p>
            <p class="text-shop-muted">تلفن: <span dir="ltr">{{ $order->shipping_address['phone'] }}</span></p>
        </div>
    </div>
    <div class="user-info-card">
        <h2 class="mb-4 text-base font-black text-shop-text">خلاصه مالی</h2>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-shop-muted">جمع محصولات</span><span class="font-bold">{{ format_price($order->subtotal) }}</span></div>
            @if($order->discount_amount > 0)
                <div class="flex justify-between text-emerald-600"><span>تخفیف ({{ $order->coupon_code }})</span><span class="font-bold">-{{ format_price($order->discount_amount) }}</span></div>
            @endif
            <div class="flex justify-between"><span class="text-shop-muted">هزینه ارسال</span><span class="font-bold">{{ format_price($order->shipping_cost) }}</span></div>
            <div class="flex justify-between border-t border-shop-border/60 pt-3 text-base"><span class="font-black">مبلغ قابل پرداخت</span><span class="font-black text-shop-primary">{{ format_price($order->total) }}</span></div>
        </div>
        @if($order->shippingMethod)
            <p class="mt-4 text-sm text-shop-muted">روش ارسال: {{ $order->shippingMethod->name }}</p>
        @endif
        @if($order->payment_ref)
            <p class="mt-1 text-sm text-shop-muted">کد پیگیری پرداخت: <span dir="ltr" class="font-mono">{{ $order->payment_ref }}</span></p>
        @endif
    </div>
</div>

@if($order->notes)
    <div class="user-info-card mb-8">
        <h2 class="mb-2 text-base font-black text-shop-text">یادداشت شما</h2>
        <p class="text-sm leading-relaxed text-shop-muted">{{ $order->notes }}</p>
    </div>
@endif

<div class="user-section-head">
    <h2 class="user-section-title">اقلام سفارش</h2>
</div>
<div class="user-table-wrap overflow-x-auto">
    <table>
        <thead>
            <tr>
                <th>نام محصول</th>
                <th>قیمت واحد</th>
                <th>تعداد</th>
                <th>جمع</th>
                @if($order->status->value === 'delivered')
                    <th></th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        <x-order-item-name
                            :item="$item"
                            :link="$item->product ? route('products.show', $item->product->slug) : null"
                        />
                    </td>
                    <td>{{ format_price($item->price) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td class="font-bold text-shop-primary">{{ format_price($item->total) }}</td>
                    @if($order->status->value === 'delivered')
                        <td>
                            @if($item->product_id && auth()->user()->canReviewProduct($item->product_id))
                                <a href="{{ route('products.show', ['slug' => $item->product->slug, 'from' => 'panel']) }}#reviews" class="text-sm font-bold text-shop-primary hover:underline">ثبت نظر</a>
                            @elseif($item->product_id && auth()->user()->hasReviewedProduct($item->product_id))
                                <span class="text-xs text-shop-muted">نظر ثبت شد</span>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
