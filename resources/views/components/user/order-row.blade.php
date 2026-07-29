@props(['order'])

@php
    $needsPayment = in_array($order->payment_status->value, ['pending', 'failed'], true)
        && ! in_array($order->status->value, ['cancelled', 'shipped', 'delivered'], true);
@endphp

<a href="{{ $needsPayment ? route('checkout.payment', $order) : route('user.orders.show', $order) }}" class="user-dash-row">
    <div class="user-dash-row-main">
        <p class="user-dash-row-title">{{ $order->order_number }}</p>
        <p class="user-dash-row-meta">{{ format_jalali($order->created_at, 'Y/m/d') }} · {{ format_price($order->total) }}</p>
    </div>
    <div class="user-dash-row-end">
        <x-user.status-badge :status="$order->payment_status" />
        @if($needsPayment)
            <span class="user-dash-row-cta">{{ $order->payment_status->value === 'failed' || $order->status->value === 'failed' ? 'تلاش مجدد' : 'پرداخت' }}</span>
        @endif
    </div>
</a>
