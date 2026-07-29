<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReturnStatus;
use App\Models\Order;
use App\Models\OrderReturn;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class FinancialReportService
{
    public function resolveDateRange(?string $dateFrom, ?string $dateTo): array
    {
        $from = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : today()->subDays(29)->startOfDay();
        $to = $dateTo ? Carbon::parse($dateTo)->endOfDay() : today()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    public function summary(?string $dateFrom, ?string $dateTo): array
    {
        [$from, $to] = $this->resolveDateRange($dateFrom, $dateTo);

        $paidQuery = $this->paidOrdersQuery($from, $to);
        $pendingQuery = $this->scopedQuery($from, $to)
            ->where('payment_status', PaymentStatus::Pending)
            ->where('status', '!=', OrderStatus::Cancelled);

        $refundedOrders = $this->scopedQuery($from, $to)
            ->where('payment_status', PaymentStatus::Refunded);

        $pendingRefunds = OrderReturn::query()
            ->whereIn('status', [ReturnStatus::Pending, ReturnStatus::Approved])
            ->with('order')
            ->get();

        $pendingRefundAmount = $pendingRefunds->sum(
            fn (OrderReturn $return) => $return->refund_amount ?? $return->order?->total ?? 0
        );

        return [
            'gross_revenue' => (int) (clone $paidQuery)->sum('total'),
            'paid_orders' => (clone $paidQuery)->count(),
            'pending_amount' => (int) (clone $pendingQuery)->sum('total'),
            'pending_orders' => (clone $pendingQuery)->count(),
            'refunded_amount' => (int) (clone $refundedOrders)->sum('total'),
            'refunded_orders' => (clone $refundedOrders)->count(),
            'discount_total' => (int) (clone $paidQuery)->sum('discount_amount'),
            'shipping_total' => (int) (clone $paidQuery)->sum('shipping_cost'),
            'subtotal_total' => (int) (clone $paidQuery)->sum('subtotal'),
            'failed_orders' => $this->scopedQuery($from, $to)
                ->where('payment_status', PaymentStatus::Failed)
                ->count(),
            'pending_refunds_count' => $pendingRefunds->count(),
            'pending_refunds_amount' => (int) $pendingRefundAmount,
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
        ];
    }

    /**
     * @return array<int, array{status: PaymentStatus, count: int, amount: int}>
     */
    public function paymentBreakdown(?string $dateFrom, ?string $dateTo): array
    {
        [$from, $to] = $this->resolveDateRange($dateFrom, $dateTo);

        return collect(PaymentStatus::cases())->map(function (PaymentStatus $status) use ($from, $to) {
            $query = $this->scopedQuery($from, $to)->where('payment_status', $status);

            return [
                'status' => $status,
                'count' => (clone $query)->count(),
                'amount' => (int) (clone $query)->sum('total'),
            ];
        })->all();
    }

    public function dailyChart(?string $dateFrom, ?string $dateTo): \Illuminate\Support\Collection
    {
        [$from, $to] = $this->resolveDateRange($dateFrom, $dateTo);

        $chartEnd = $from->copy()->addDays(89)->endOfDay();
        if ($chartEnd->gt($to)) {
            $chartEnd = $to->copy();
        }

        $days = $from->diffInDays($chartEnd) + 1;

        return collect(range(0, $days - 1))->map(function (int $offset) use ($from) {
            $date = $from->copy()->addDays($offset)->startOfDay();
            $paid = $this->paidOrdersQuery($date, $date->copy()->endOfDay());

            return [
                'label' => format_jalali($date, 'm/d', false),
                'revenue' => (int) (clone $paid)->sum('total'),
                'orders' => (clone $paid)->count(),
            ];
        })->values();
    }

    public function monthlyChart(): \Illuminate\Support\Collection
    {
        return collect(range(11, 0))->map(function (int $monthsAgo) {
            $month = Verta::now()->subMonths($monthsAgo);
            $start = $month->copy()->startMonth()->datetime();
            $end = $month->copy()->endMonth()->datetime();

            $paid = Order::query()
                ->where('payment_status', PaymentStatus::Paid)
                ->where('status', '!=', OrderStatus::Cancelled)
                ->whereBetween('created_at', [$start, $end]);

            return [
                'label' => $month->format('F y'),
                'revenue' => (int) (clone $paid)->sum('total'),
                'orders' => (clone $paid)->count(),
            ];
        })->values();
    }

    public function transactionsQuery(?string $dateFrom, ?string $dateTo, ?string $paymentStatus = null): Builder
    {
        [$from, $to] = $this->resolveDateRange($dateFrom, $dateTo);

        $query = Order::with('user')
            ->whereBetween('created_at', [$from, $to])
            ->where('status', '!=', OrderStatus::Cancelled);

        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }

        return $query->latest('created_at');
    }

    private function scopedQuery(Carbon $from, Carbon $to): Builder
    {
        return Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', '!=', OrderStatus::Cancelled);
    }

    private function paidOrdersQuery(Carbon $from, Carbon $to): Builder
    {
        return $this->scopedQuery($from, $to)
            ->where('payment_status', PaymentStatus::Paid);
    }
}
