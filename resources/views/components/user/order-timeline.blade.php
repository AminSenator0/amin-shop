@props(['order'])

@php
    use App\Enums\OrderStatus;
    use App\Enums\PaymentStatus;

    $steps = [
        ['label' => 'ثبت سفارش', 'done' => true, 'date' => $order->created_at],
        ['label' => 'پرداخت موفق', 'done' => $order->paid_at !== null, 'date' => $order->paid_at],
        ['label' => 'آماده‌سازی', 'done' => in_array($order->status, [OrderStatus::Processing, OrderStatus::Shipped, OrderStatus::Delivered]), 'date' => null],
        ['label' => 'ارسال مرسوله', 'done' => in_array($order->status, [OrderStatus::Shipped, OrderStatus::Delivered]), 'date' => $order->shipped_at],
        ['label' => 'تحویل به مشتری', 'done' => $order->status === OrderStatus::Delivered, 'date' => $order->delivered_at],
    ];

    $isCancelled = $order->status === OrderStatus::Cancelled;

    // ═══ تایمر ۲۰ دقیقه ═══
    $isPendingForTimer = ! $isCancelled && $order->status === OrderStatus::Pending && $order->payment_status === PaymentStatus::Pending;
    $expiresAt = $order->created_at->addMinutes(20);
    $remainingSeconds = $isPendingForTimer ? max(0, (int) now()->diffInSeconds($expiresAt, false)) : 0;
    @endphp

<div class="user-timeline-card">
    <h2 class="mb-6 text-base font-black text-shop-text">پیگیری وضعیت سفارش</h2>

    {{-- ═══ تایمر مهلت پرداخت ═══ --}}
    @if($isPendingForTimer && $remainingSeconds > 0)
        <div x-data="{
            remaining: {{ $remainingSeconds }},
            timer: null,
            format(sec) {
                const m = Math.floor(sec / 60).toString().padStart(2, '0');
                const s = (sec % 60).toString().padStart(2, '0');
                return m + ':' + s;
            },
            init() {
                this.timer = setInterval(() => {
                    this.remaining--;
                    if (this.remaining <= 0) {
                        clearInterval(this.timer);
                        window.location.reload();
                    }
                }, 1000);
            }
        }" x-init="init()" class="mb-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-center">
            <p class="text-amber-800 font-bold text-sm">
                ⏳ مهلت پرداخت: <span x-text="format(remaining)" class="font-mono"></span>
            </p>
            <p class="text-amber-600 text-xs mt-1">بعد از اتمام مهلت، سفارش خودکار لغو و موجودی بازگردانده می‌شود.</p>
        </div>
    @elseif($isPendingForTimer && $remainingSeconds <= 0)
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-center">
            <p class="text-red-700 font-bold text-sm">⚠️ مهلت پرداخت به پایان رسیده است.</p>
            <p class="text-red-600 text-xs mt-1">این سفارش به زودی به صورت خودکار لغو می‌شود.</p>
        </div>
    @endif

    @if($isCancelled)
        <div class="flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3.5 text-sm font-medium text-rose-700">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            این سفارش لغو شده است.
        </div>
    @else
        <ol class="space-y-0">
            @foreach($steps as $index => $step)
                <li class="flex gap-4 {{ $index < count($steps) - 1 ? 'pb-7' : '' }}">
                    <div class="flex flex-col items-center">
                        <span @class([
                            'flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-black shadow-sm',
                            'bg-shop-primary text-white ring-4 ring-shop-primary/15' => $step['done'],
                            'bg-shop-background text-shop-muted ring-1 ring-shop-border' => ! $step['done'],
                        ])>
                            @if($step['done'])
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </span>
                        @if($index < count($steps) - 1)
                            <span @class(['mt-1.5 w-0.5 flex-1 min-h-[1.75rem] rounded-full', $step['done'] ? 'bg-shop-primary/35' : 'bg-shop-border/80'])></span>
                        @endif
                    </div>
                    <div class="min-w-0 pt-1.5">
                        <p @class(['text-sm font-bold', $step['done'] ? 'text-shop-text' : 'text-shop-muted'])>{{ $step['label'] }}</p>
                        @if($step['date'])
                            <p class="mt-1 text-xs text-shop-muted">{{ format_jalali($step['date'], 'Y/m/d — H:i') }}</p>
                        @elseif(! $step['done'])
                            <p class="mt-1 text-xs text-shop-muted">در انتظار</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    @endif
</div>