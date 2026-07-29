@extends('layouts.admin')

@section('header', 'گزارش مالی')

@push('scripts')
    @vite('resources/js/admin-financial.js')
@endpush

@section('content')
@php
    $filterParams = array_filter([
        'date_from' => request('date_from', $summary['date_from']),
        'date_to' => request('date_to', $summary['date_to']),
        'payment_status' => request('payment_status'),
    ]);
    $paymentTotal = max(collect($paymentBreakdown)->sum('amount'), 1);
    $aov = $summary['paid_orders'] > 0
        ? (int) round($summary['gross_revenue'] / $summary['paid_orders'])
        : 0;
    $secondaryMetrics = [
        ['label' => 'در انتظار پرداخت', 'value' => format_price($summary['pending_amount']), 'meta' => $summary['pending_orders'].' سفارش', 'href' => route('admin.financial.index', array_merge($filterParams, ['payment_status' => 'pending']))],
        ['label' => 'بازگشت وجه', 'value' => format_price($summary['refunded_amount']), 'meta' => $summary['refunded_orders'].' سفارش', 'href' => route('admin.financial.index', array_merge($filterParams, ['payment_status' => 'refunded']))],
        ['label' => 'تخفیف‌ها', 'value' => format_price($summary['discount_total']), 'meta' => null, 'href' => null],
        ['label' => 'هزینه ارسال', 'value' => format_price($summary['shipping_total']), 'meta' => null, 'href' => null],
        ['label' => 'پرداخت ناموفق', 'value' => format_number($summary['failed_orders']), 'meta' => 'سفارش', 'href' => route('admin.financial.index', array_merge($filterParams, ['payment_status' => 'failed']))],
        ['label' => 'مرجوعی در انتظار', 'value' => format_price($summary['pending_refunds_amount']), 'meta' => $summary['pending_refunds_count'].' مورد', 'href' => route('admin.returns.index', ['status' => 'pending'])],
    ];
@endphp

<div class="admin-financial-toolbar admin-card mb-6">
    <div class="flex flex-col gap-4 p-4 sm:p-5 lg:flex-row lg:items-end lg:justify-between">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            @if(request('payment_status'))
                <input type="hidden" name="payment_status" value="{{ request('payment_status') }}">
            @endif
            <div>
                <label class="admin-field-label text-xs mb-1 block">از تاریخ</label>
                <input type="text" name="date_from" value="{{ format_jalali($summary['date_from'], 'Y/m/d', false) }}" data-jalali-date class="admin-input text-sm w-36" dir="ltr" placeholder="۱۴۰۴/۰۱/۰۱" autocomplete="off">
            </div>
            <div>
                <label class="admin-field-label text-xs mb-1 block">تا تاریخ</label>
                <input type="text" name="date_to" value="{{ format_jalali($summary['date_to'], 'Y/m/d', false) }}" data-jalali-date class="admin-input text-sm w-36" dir="ltr" placeholder="۱۴۰۴/۱۲/۲۹" autocomplete="off">
            </div>
            <button type="submit" class="admin-btn-primary text-sm">اعمال</button>
            <a href="{{ route('admin.financial.index', request('payment_status') ? ['payment_status' => request('payment_status')] : []) }}" class="admin-filter-tab text-xs">۳۰ روز اخیر</a>
        </form>
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs text-zinc-400">
                {{ format_jalali($summary['date_from'], 'j F') }} — {{ format_jalali($summary['date_to'], 'j F Y') }}
            </span>
            <a href="{{ route('admin.financial.export', array_filter([
                'date_from' => $summary['date_from'],
                'date_to' => $summary['date_to'],
                'payment_status' => request('payment_status'),
            ])) }}" class="admin-btn-secondary text-sm">خروجی CSV</a>
        </div>
    </div>
</div>

<div class="admin-financial-hero admin-card mb-6">
    <div class="grid grid-cols-1 divide-y divide-zinc-100 sm:grid-cols-3 sm:divide-x sm:divide-x-reverse sm:divide-y-0">
        <a href="{{ route('admin.financial.index', array_merge($filterParams, ['payment_status' => 'paid'])) }}" class="admin-financial-kpi group">
            <span class="admin-financial-kpi-label">درآمد پرداخت‌شده</span>
            <span class="admin-financial-kpi-value">{{ format_price($summary['gross_revenue']) }}</span>
            <span class="admin-financial-kpi-meta">{{ format_number($summary['paid_orders']) }} سفارش موفق</span>
        </a>
        <div class="admin-financial-kpi">
            <span class="admin-financial-kpi-label">میانگین سفارش</span>
            <span class="admin-financial-kpi-value">{{ format_price($aov) }}</span>
            <span class="admin-financial-kpi-meta">AOV در بازه انتخابی</span>
        </div>
        <div class="admin-financial-kpi">
            <span class="admin-financial-kpi-label">جمع جزء فروش</span>
            <span class="admin-financial-kpi-value">{{ format_price($summary['subtotal_total']) }}</span>
            <span class="admin-financial-kpi-meta">قبل از تخفیف و ارسال</span>
        </div>
    </div>
</div>

<div class="admin-financial-metrics admin-card mb-8">
    <div class="grid grid-cols-2 divide-x divide-x-reverse divide-zinc-100 sm:grid-cols-3 lg:grid-cols-6">
        @foreach($secondaryMetrics as $metric)
            @if($metric['href'])
                <a href="{{ $metric['href'] }}" class="admin-financial-metric group">
            @else
                <div class="admin-financial-metric">
            @endif
                    <span class="admin-financial-metric-label">{{ $metric['label'] }}</span>
                    <span class="admin-financial-metric-value">{{ $metric['value'] }}</span>
                    @if($metric['meta'])
                        <span class="admin-financial-metric-meta">{{ $metric['meta'] }}</span>
                    @endif
            @if($metric['href'])
                </a>
            @else
                </div>
            @endif
        @endforeach
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-5 mb-8">
    <div class="admin-card lg:col-span-3">
        <div class="admin-card-header">
            <h2 class="admin-card-title">روند درآمد</h2>
            <span class="text-xs text-zinc-400">بازه انتخابی</span>
        </div>
        <div class="p-5 pt-2">
            <div class="h-64 sm:h-72">
                <canvas id="financialDailyChart"
                    data-chart="{{ json_encode([
                        'labels' => $dailyChart->pluck('label'),
                        'revenues' => $dailyChart->pluck('revenue'),
                        'orders' => $dailyChart->pluck('orders'),
                    ]) }}"></canvas>
            </div>
        </div>
    </div>

    <div class="admin-card lg:col-span-2">
        <div class="admin-card-header">
            <h2 class="admin-card-title">وضعیت پرداخت</h2>
        </div>
        <div class="space-y-1 p-4">
            @foreach($paymentBreakdown as $item)
                @php
                    $pct = round(($item['amount'] / $paymentTotal) * 100);
                    $barColor = match($item['status']->color()) {
                        'emerald' => 'bg-emerald-500',
                        'amber' => 'bg-amber-400',
                        'rose' => 'bg-rose-400',
                        'blue' => 'bg-blue-400',
                        default => 'bg-zinc-300',
                    };
                    $isActive = request('payment_status') === $item['status']->value;
                @endphp
                <a href="{{ route('admin.financial.index', array_merge($filterParams, ['payment_status' => $item['status']->value])) }}"
                   class="admin-financial-status @if($isActive) is-active @endif">
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="h-2 w-2 shrink-0 rounded-full {{ $barColor }}"></span>
                            <span class="text-sm font-medium text-zinc-700 truncate">{{ $item['status']->label() }}</span>
                        </div>
                        <span class="text-sm font-bold text-zinc-900 shrink-0">{{ format_price($item['amount']) }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="admin-financial-progress flex-1">
                            <div class="admin-financial-progress-bar {{ $barColor }}" style="width: {{ max($pct, $item['amount'] > 0 ? 4 : 0) }}%"></div>
                        </div>
                        <span class="text-[11px] text-zinc-400 shrink-0 w-16 text-start">{{ $item['count'] }} سفارش</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>

<div class="admin-card mb-8">
    <div class="admin-card-header">
        <h2 class="admin-card-title">فروش ماهانه</h2>
        <span class="text-xs text-zinc-400">۱۲ ماه اخیر</span>
    </div>
    <div class="p-5 pt-2">
        <div class="h-56 sm:h-64">
            <canvas id="financialMonthlyChart"
                data-chart="{{ json_encode([
                    'labels' => $monthlyChart->pluck('label'),
                    'revenues' => $monthlyChart->pluck('revenue'),
                    'orders' => $monthlyChart->pluck('orders'),
                ]) }}"></canvas>
        </div>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">تراکنش‌ها</h2>
        <form method="GET" class="admin-status-filter flex flex-wrap items-center gap-2">
            <input type="hidden" name="date_from" value="{{ $summary['date_from'] }}">
            <input type="hidden" name="date_to" value="{{ $summary['date_to'] }}">
            <select name="payment_status" class="admin-select text-sm py-2" data-auto-submit>
                <option value="">همه پرداخت‌ها</option>
                @foreach(\App\Enums\PaymentStatus::cases() as $paymentStatus)
                    <option value="{{ $paymentStatus->value }}" @selected(request('payment_status') === $paymentStatus->value)>{{ $paymentStatus->label() }}</option>
                @endforeach
            </select>
            @if(request('payment_status'))
                <a href="{{ route('admin.financial.index', ['date_from' => $summary['date_from'], 'date_to' => $summary['date_to']]) }}" class="admin-link text-xs">حذف فیلتر</a>
            @endif
        </form>
    </div>

    @if(request('payment_status'))
        <div class="border-b border-zinc-100 bg-zinc-50/80 px-5 py-2.5 text-xs text-zinc-600">
            فیلتر: {{ \App\Enums\PaymentStatus::from(request('payment_status'))->label() }}
        </div>
    @endif

    <div class="admin-table-wrap border-0 rounded-none shadow-none">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>سفارش</th>
                    <th>مشتری</th>
                    <th>مبلغ</th>
                    <th>وضعیت</th>
                    <th>تاریخ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $order)
                    <tr>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="admin-link font-bold">{{ $order->order_number }}</a>
                            @if($order->coupon_code)
                                <p class="text-[11px] text-zinc-400 mt-0.5">{{ $order->coupon_code }}</p>
                            @endif
                        </td>
                        <td class="text-zinc-600">{{ $order->user->name }}</td>
                        <td>
                            <span class="font-bold text-zinc-900">{{ format_price($order->total) }}</span>
                            @if($order->discount_amount > 0)
                                <p class="text-[11px] text-zinc-400 mt-0.5">تخفیف {{ format_price($order->discount_amount) }}</p>
                            @endif
                        </td>
                        <td class="whitespace-nowrap"><x-admin.status-badge :status="$order->payment_status" /></td>
                        <td class="text-zinc-500 text-sm whitespace-nowrap">{{ $order->paid_at ? format_jalali($order->paid_at, 'Y/m/d') : '—' }}</td>
                        <td>
                            <x-admin.table-actions>
                                <x-admin.table-action type="view" :href="route('admin.orders.show', $order)" title="جزئیات" />
                            </x-admin.table-actions>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center text-zinc-400">تراکنشی در این بازه یافت نشد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-admin.pagination :paginator="$transactions" class="border-t border-zinc-100 px-4 py-4 sm:px-6" />
</div>
@endsection
