@props(['order'])

<article class="user-order-card">
    <div class="mb-4 flex items-start justify-between gap-3">
        <div>
            <p class="font-black text-shop-text">{{ $order->order_number }}</p>
            <p class="mt-1 text-xs text-shop-muted">{{ format_jalali($order->created_at, 'Y/m/d — H:i') }}</p>
        </div>
        <x-user.status-badge :status="$order->status" />
    </div>
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <span class="text-lg font-black text-shop-primary">{{ format_price($order->total) }}</span>
            <p class="mt-0.5 text-xs text-shop-muted">{{ format_number($order->items->sum('quantity')) }} قلم</p>
        </div>
        <x-user.status-badge :status="$order->payment_status" />
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('user.orders.show', $order) }}" class="user-action-chip">مشاهده جزئیات</a>
        @if(in_array($order->payment_status->value, ['pending', 'failed'], true) && ! in_array($order->status->value, ['cancelled', 'shipped', 'delivered'], true))
            <a href="{{ route('checkout.payment', $order) }}" class="user-action-chip is-primary">
                {{ $order->payment_status->value === 'failed' || $order->status->value === 'failed' ? 'تلاش مجدد' : 'پرداخت' }}
            </a>
        @endif
    </div>
</article>
