@props(['order'])

<article class="user-order-card">
    <div class="mb-4 flex items-start justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <p class="font-black text-shop-text">{{ $order->order_number }}</p>
                @if($order->isPresale())
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">
                        پیش‌فروش
                    </span>
                @endif
            </div>
            <p class="mt-1 text-xs text-shop-muted">{{ format_jalali($order->created_at, 'Y/m/d — H:i') }}</p>
        </div>
        <x-user.status-badge :status="$order->status" />
    </div>
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <span class="text-lg font-black text-shop-primary">
                @if($order->isPresale())
                    {{ format_price($order->subtotal) }}
                @else
                    {{ format_price($order->total) }}
                @endif
            </span>
            <p class="mt-0.5 text-xs text-shop-muted">{{ format_number($order->items->sum('quantity')) }} قلم</p>
        </div>
        <x-user.status-badge :status="$order->payment_status" />
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('user.orders.show', $order) }}" class="user-action-chip">مشاهده جزئیات</a>
        @if($order->isPresale())
            @if(in_array($order->presale_status, [\App\Enums\PresaleStatus::AwaitingFinalPrice->value, \App\Enums\PresaleStatus::AwaitingPayment->value, \App\Enums\PresaleStatus::PartiallyPaid->value], true))
                <!-- <a href="{{ route('checkout.payment', $order) }}" class="user-action-chip is-primary">
                    @if($order->presale_status === \App\Enums\PresaleStatus::AwaitingFinalPrice->value)
                        پرداخت بیعانه
                    @else
                        پرداخت قسط
                    @endif
                </a>
            @endif -->
        @else
            @if(in_array($order->payment_status->value, ['pending', 'failed'], true) && ! in_array($order->status->value, ['cancelled', 'shipped', 'delivered'], true))
                <a href="{{ route('checkout.payment', $order) }}" class="user-action-chip is-primary">
                    {{ $order->payment_status->value === 'failed' || $order->status->value === 'failed' ? 'تلاش مجدد' : 'پرداخت' }}
                </a>
            @endif
        @endif
    </div>
</article>