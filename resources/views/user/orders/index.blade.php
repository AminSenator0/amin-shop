@extends('layouts.user')

@section('title', 'سفارشات من')

@section('content')
<x-user.breadcrumb :items="[
    ['label' => 'داشبورد', 'url' => route('user.dashboard')],
    ['label' => 'سفارشات من'],
]" />

<x-user.page-header
    title="سفارشات من"
    subtitle="همه سفارش‌های ثبت‌شده، وضعیت ارسال و پرداخت را از اینجا پیگیری کنید."
/>

<form method="GET" class="user-filter-panel">
    <div class="user-filter-grid">
        <div class="user-filter-field user-filter-field-search">
            <label for="search" class="user-filter-label">جستجو</label>
            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="شماره سفارش یا کد رهگیری..." class="user-filter-control input-shop">
        </div>
        <div class="user-filter-field user-filter-field-status">
            <label for="status" class="user-filter-label">وضعیت سفارش</label>
            <select name="status" id="status" class="user-filter-control input-shop">
                <option value="">همه وضعیت‌ها</option>
                @foreach(\App\Enums\OrderStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="user-filter-field user-filter-field-status">
            <label for="payment_status" class="user-filter-label">وضعیت پرداخت</label>
            <select name="payment_status" id="payment_status" class="user-filter-control input-shop">
                <option value="">همه پرداخت‌ها</option>
                @foreach(\App\Enums\PaymentStatus::cases() as $paymentStatus)
                    <option value="{{ $paymentStatus->value }}" @selected(request('payment_status') === $paymentStatus->value)>{{ $paymentStatus->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="user-filter-field user-filter-actions">
            <span class="user-filter-label" aria-hidden="true">&nbsp;</span>
            <div class="user-filter-buttons">
                <button type="submit" class="user-filter-btn btn-primary">اعمال فیلتر</button>
                @if(request()->hasAny(['search', 'status', 'payment_status']))
                    <a href="{{ route('user.orders.index') }}" class="user-filter-btn btn-secondary">پاک کردن</a>
                @endif
            </div>
        </div>
    </div>
</form>

@if($orders->isEmpty())
    <x-user.empty-state
        icon="orders"
        title="سفارشی یافت نشد"
        :description="request()->hasAny(['search', 'status', 'payment_status']) ? 'با فیلتر دیگری جستجو کنید یا فیلترها را پاک کنید.' : 'هنوز سفارشی ثبت نکرده‌اید. از فروشگاه خرید کنید.'"
        :action-url="route('products.index')"
        action-label="مشاهده محصولات"
    />
@else
    <div class="hidden md:block user-table-wrap overflow-x-auto">
        <table>
            <thead>
                <tr>
                    <th>شماره سفارش</th>
                    <th>تاریخ ثبت</th>
                    <th>اقلام</th>
                    <th>مبلغ کل</th>
                    <th>وضعیت</th>
                    <th>پرداخت</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td class="font-bold">
                            {{ $order->order_number }}
                            @if($order->tracking_code)
                                <p class="mt-0.5 text-[11px] font-normal text-shop-muted" dir="ltr">{{ $order->tracking_code }}</p>
                            @endif
                        </td>
                        <td class="text-shop-muted">{{ format_jalali($order->created_at, 'Y/m/d — H:i') }}</td>
                        <td class="text-shop-muted">{{ format_number($order->items->sum('quantity')) }} قلم</td>
                        <td class="font-bold text-shop-primary">{{ format_price($order->total) }}</td>
                        <td class="whitespace-nowrap"><x-user.status-badge :status="$order->status" /></td>
                        <td class="whitespace-nowrap"><x-user.status-badge :status="$order->payment_status" /></td>
                        <td>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('user.orders.show', $order) }}" class="user-section-link">جزئیات</a>
                                @if(in_array($order->payment_status->value, ['pending', 'failed'], true) && ! in_array($order->status->value, ['cancelled', 'shipped', 'delivered'], true))
                                    <a href="{{ route('checkout.payment', $order) }}" class="text-sm font-bold text-emerald-600 hover:underline">
                                        {{ $order->payment_status->value === 'failed' || $order->status->value === 'failed' ? 'تلاش مجدد' : 'پرداخت' }}
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="space-y-3 md:hidden">
        @foreach($orders as $order)
            <x-user.order-card :order="$order" />
        @endforeach
    </div>

    @if($orders->hasPages())
        <div class="mt-6">{{ $orders->links() }}</div>
    @endif
@endif
@endsection
