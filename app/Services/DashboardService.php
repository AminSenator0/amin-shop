<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReturnStatus;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\Review;
use App\Models\SiteVisit;
use App\Models\User;
use Hekmatinasser\Verta\Verta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public const LOW_STOCK_THRESHOLD = 5;

    public function __construct(
        private OrderService $orders,
        private SiteVisitService $siteVisits,
        private FinancialReportService $financialReport,
    ) {}

    public function data(int $salesDays = 7, int $visitorsDays = 30): array
    {
        $salesDays = in_array($salesDays, [7, 14, 30], true) ? $salesDays : 7;
        $visitorsDays = in_array($visitorsDays, [7, 14, 30, 90], true) ? $visitorsDays : 30;

        $cacheKey = "admin.dashboard.{$salesDays}.{$visitorsDays}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($salesDays, $visitorsDays) {
            $stats = $this->stats();

            return [
            'stats' => $stats,
            'attention' => $this->attentionItems($stats),
            'salesChart' => $this->salesChart($salesDays),
            'monthlySalesChart' => $this->monthlySalesChart(),
            'visitorsChart' => $this->visitorsChart($visitorsDays),
            'orderStatusChart' => $this->orderStatusChart(),
            'paymentStatusChart' => $this->paymentStatusChart(),
            'topProducts' => $this->topProducts(),
            'recentOrders' => $this->recentOrders(),
            'actionOrders' => $this->actionOrders(),
            'pendingPaymentOrders' => $this->pendingPaymentOrders(),
            'unreadMessages' => $this->unreadMessages(),
            'lowStockProducts' => $this->lowStockProducts(),
            'pendingReviews' => $this->pendingReviews(),
            'openReturns' => $this->openReturns(),
            'topCoupons' => $this->topCoupons(),
            'couponStats' => $this->couponStats(),
            'newsletterStats' => $this->newsletterStats(),
            'catalogIssues' => $this->catalogIssues(),
            'recentActivity' => $this->recentActivity(),
            'filters' => [
                'sales_days' => $salesDays,
                'visitors_days' => $visitorsDays,
            ],
            ];
        });
    }

    public function exportOrders(Carbon $from, Carbon $to): Collection
    {
        return Order::with('user')
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->where('status', '!=', OrderStatus::Cancelled)
            ->latest()
            ->get();
    }

    public function lowStockThreshold(): int
    {
        return self::LOW_STOCK_THRESHOLD;
    }

    public function stats(): array
    {
        $today = today();
        $yesterday = today()->subDay();

        $ordersToday = Order::whereDate('created_at', $today)->count();
        $ordersYesterday = Order::whereDate('created_at', $yesterday)->count();

        $revenueToday = (int) $this->revenueQuery($today, $today)->sum('total');
        $revenueYesterday = (int) $this->revenueQuery($yesterday, $yesterday)->sum('total');

        $visitorsToday = $this->siteVisits->todayCount();
        $visitorsYesterday = SiteVisit::query()
            ->whereDate('visit_date', $yesterday)
            ->count();

        $paidOrdersToday = Order::query()
            ->whereDate('created_at', $today)
            ->where('payment_status', PaymentStatus::Paid)
            ->where('status', '!=', OrderStatus::Cancelled)
            ->count();

        $month = Verta::now();
        $monthStart = $month->copy()->startMonth()->datetime();
        $monthEnd = $month->copy()->endMonth()->datetime();
        $revenueMonth = (int) Order::query()
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('status', '!=', OrderStatus::Cancelled)
            ->sum('total');

        $newCustomersToday = User::where('role', 'customer')->whereDate('created_at', $today)->count();
        $newCustomersWeek = User::where('role', 'customer')
            ->where('created_at', '>=', $today->copy()->subDays(6)->startOfDay())
            ->count();

        return [
            'orders_today' => $ordersToday,
            'orders_today_trend' => $this->percentChange($ordersToday, $ordersYesterday),
            'revenue_today' => $revenueToday,
            'revenue_today_trend' => $this->percentChange($revenueToday, $revenueYesterday),
            'revenue_month' => $revenueMonth,
            'total_orders' => Order::count(),
            'total_products' => Product::count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'new_customers_today' => $newCustomersToday,
            'new_customers_week' => $newCustomersWeek,
            'low_stock' => Product::where('stock', '<', self::LOW_STOCK_THRESHOLD)->count(),
            'visitors_today' => $visitorsToday,
            'visitors_today_trend' => $this->percentChange($visitorsToday, $visitorsYesterday),
            'unread_orders' => $this->orders->unreadCount(),
            'needs_action' => $this->orders->pendingActionCount(),
            'pending_reviews' => Review::where('is_approved', false)->count(),
            'open_returns' => OrderReturn::whereIn('status', [ReturnStatus::Pending, ReturnStatus::Approved])->count(),
            'pending_payment' => Order::query()
                ->where('payment_status', PaymentStatus::Pending)
                ->where('status', '!=', OrderStatus::Cancelled)
                ->count(),
            'unread_messages' => ContactMessage::unread()->count(),
            'aov_today' => $paidOrdersToday > 0
                ? (int) round($revenueToday / max($paidOrdersToday, 1))
                : 0,
            'conversion_rate' => $visitorsToday > 0
                ? round(($ordersToday / $visitorsToday) * 100, 1)
                : 0,
            'newsletter_total' => NewsletterSubscriber::count(),
            'active_coupons' => Coupon::where('is_active', true)->count(),
        ];
    }

    public function attentionItems(array $stats): array
    {
        return [
            [
                'label' => 'سفارش خوانده‌نشده',
                'count' => $stats['unread_orders'],
                'href' => route('admin.orders.index', ['unread' => 1]),
            ],
            [
                'label' => 'نیاز به اقدام',
                'count' => $stats['needs_action'],
                'href' => route('admin.orders.index', ['needs_action' => 1]),
            ],
            [
                'label' => 'در انتظار پرداخت',
                'count' => $stats['pending_payment'],
                'href' => route('admin.orders.index', ['payment_status' => 'pending']),
            ],
            [
                'label' => 'نظر معلق',
                'count' => $stats['pending_reviews'],
                'href' => route('admin.reviews.index', ['pending' => 1]),
            ],
            [
                'label' => 'مرجوعی باز',
                'count' => $stats['open_returns'],
                'href' => route('admin.returns.index', ['status' => 'pending']),
            ],
            [
                'label' => 'پیام خوانده‌نشده',
                'count' => $stats['unread_messages'],
                'href' => route('admin.messages.index', ['unread' => 1]),
            ],
            [
                'label' => 'موجودی کم',
                'count' => $stats['low_stock'],
                'href' => route('admin.products.index', ['low_stock' => 1]),
            ],
        ];
    }

    public function salesChart(int $days): Collection
    {
        return collect(range($days - 1, 0))->map(function (int $daysAgo) {
            $date = today()->subDays($daysAgo);

            return [
                'label' => format_jalali($date, 'm/d', false),
                'revenue' => (int) $this->revenueQuery($date, $date)->sum('total'),
                'orders' => Order::whereDate('created_at', $date)->count(),
            ];
        })->values();
    }

    public function monthlySalesChart(): Collection
    {
        return collect(range(11, 0))->map(function (int $monthsAgo) {
            $month = Verta::now()->subMonths($monthsAgo);
            $start = $month->copy()->startMonth()->datetime();
            $end = $month->copy()->endMonth()->datetime();

            return [
                'label' => $month->format('F y'),
                'revenue' => (int) Order::whereBetween('created_at', [$start, $end])
                    ->where('status', '!=', OrderStatus::Cancelled)
                    ->sum('total'),
                'orders' => Order::whereBetween('created_at', [$start, $end])
                    ->where('status', '!=', OrderStatus::Cancelled)
                    ->count(),
            ];
        })->values();
    }

    public function visitorsChart(int $days): Collection
    {
        return $this->siteVisits->dailyChart($days);
    }

    public function orderStatusChart(): array
    {
        $from = today()->subDays(29)->startOfDay();

        return collect(OrderStatus::cases())->map(function (OrderStatus $status) use ($from) {
            return [
                'label' => $status->label(),
                'count' => Order::query()
                    ->where('created_at', '>=', $from)
                    ->where('status', $status)
                    ->count(),
                'color' => $this->statusChartColor($status->color()),
            ];
        })->filter(fn (array $item) => $item['count'] > 0)->values()->all();
    }

    public function paymentStatusChart(): array
    {
        $from = today()->subDays(29)->startOfDay();

        return collect(PaymentStatus::cases())->map(function (PaymentStatus $status) use ($from) {
            return [
                'label' => $status->label(),
                'count' => Order::query()
                    ->where('created_at', '>=', $from)
                    ->where('payment_status', $status)
                    ->where('status', '!=', OrderStatus::Cancelled)
                    ->count(),
                'color' => $this->statusChartColor($status->color()),
            ];
        })->filter(fn (array $item) => $item['count'] > 0)->values()->all();
    }

    public function topProducts(): Collection
    {
        return OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', OrderStatus::Cancelled)
            ->select('order_items.product_name', 'order_items.product_id')
            ->selectRaw('SUM(order_items.quantity) as total_sold')
            ->selectRaw('SUM(order_items.total) as total_revenue')
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();
    }

    public function recentOrders(): Collection
    {
        return Order::with('user')->latest()->take(10)->get();
    }

    public function actionOrders(): Collection
    {
        return Order::with('user')
            ->where('payment_status', PaymentStatus::Paid)
            ->whereIn('status', [OrderStatus::Paid, OrderStatus::Processing])
            ->latest()
            ->take(5)
            ->get();
    }

    public function pendingPaymentOrders(): Collection
    {
        return Order::with('user')
            ->where('payment_status', PaymentStatus::Pending)
            ->where('status', '!=', OrderStatus::Cancelled)
            ->latest()
            ->take(5)
            ->get();
    }

    public function unreadMessages(): Collection
    {
        return ContactMessage::unread()->latest()->take(5)->get();
    }

    public function lowStockProducts(): Collection
    {
        return Product::where('stock', '<', self::LOW_STOCK_THRESHOLD)
            ->orderBy('stock')
            ->take(5)
            ->get();
    }

    public function pendingReviews(): Collection
    {
        return Review::with(['user', 'product'])
            ->where('is_approved', false)
            ->latest()
            ->take(5)
            ->get();
    }

    public function openReturns(): Collection
    {
        return OrderReturn::with('order.user')
            ->whereIn('status', [ReturnStatus::Pending, ReturnStatus::Approved])
            ->latest()
            ->take(5)
            ->get();
    }

    public function topCoupons(): Collection
    {
        return Coupon::query()
            ->orderByDesc('used_count')
            ->take(5)
            ->get();
    }

    public function couponStats(): array
    {
        return [
            'total' => Coupon::count(),
            'active' => Coupon::where('is_active', true)->count(),
            'expired' => Coupon::where('is_active', true)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<', now())
                ->count(),
            'total_uses' => (int) Coupon::sum('used_count'),
        ];
    }

    public function newsletterStats(): array
    {
        $today = today();

        return [
            'total' => NewsletterSubscriber::count(),
            'today' => NewsletterSubscriber::whereDate('created_at', $today)->count(),
            'week' => NewsletterSubscriber::where('created_at', '>=', $today->copy()->subDays(6)->startOfDay())->count(),
        ];
    }

    public function catalogIssues(): array
    {
        return [
            'without_image' => Product::query()
                ->where(function ($query) {
                    $query->whereNull('image')->orWhere('image', '');
                })
                ->whereDoesntHave('images')
                ->count(),
            'without_category' => Product::whereNull('category_id')->count(),
            'inactive' => Product::where('is_active', false)->count(),
        ];
    }

    public function recentActivity(): Collection
    {
        $activities = collect();

        Order::latest()->take(5)->get()->each(function (Order $order) use ($activities) {
            $activities->push([
                'type' => 'order',
                'title' => "سفارش {$order->order_number}",
                'subtitle' => $order->user?->name.' — '.$order->status->label(),
                'url' => route('admin.orders.show', $order),
                'at' => $order->created_at,
            ]);
        });

        Review::with('product')->latest()->take(3)->get()->each(function (Review $review) use ($activities) {
            $activities->push([
                'type' => 'review',
                'title' => 'نظر جدید برای '.$review->product?->name,
                'subtitle' => $review->is_approved ? 'تأیید شده' : 'در انتظار تأیید',
                'url' => route('admin.reviews.index', $review->is_approved ? [] : ['pending' => 1]),
                'at' => $review->created_at,
            ]);
        });

        User::where('role', 'customer')->latest()->take(3)->get()->each(function (User $user) use ($activities) {
            $activities->push([
                'type' => 'user',
                'title' => 'مشتری جدید: '.$user->name,
                'subtitle' => $user->email ?? $user->phone ?? '',
                'url' => route('admin.users.show', $user),
                'at' => $user->created_at,
            ]);
        });

        ContactMessage::latest()->take(3)->get()->each(function (ContactMessage $message) use ($activities) {
            $activities->push([
                'type' => 'message',
                'title' => $message->subject,
                'subtitle' => $message->name,
                'url' => route('admin.messages.show', $message),
                'at' => $message->created_at,
            ]);
        });

        OrderReturn::with('order')->latest()->take(3)->get()->each(function (OrderReturn $return) use ($activities) {
            $activities->push([
                'type' => 'return',
                'title' => 'مرجوعی سفارش '.$return->order?->order_number,
                'subtitle' => $return->status->label(),
                'url' => route('admin.returns.index'),
                'at' => $return->created_at,
            ]);
        });

        return $activities
            ->sortByDesc('at')
            ->take(12)
            ->values();
    }

    private function revenueQuery(Carbon $from, Carbon $to)
    {
        return Order::query()
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->where('status', '!=', OrderStatus::Cancelled);
    }

    private function percentChange(int|float $current, int|float $previous): ?float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function statusChartColor(string $color): string
    {
        return match ($color) {
            'amber', 'yellow' => '#f59e0b',
            'emerald', 'green' => '#10b981',
            'rose', 'red' => '#f43f5e',
            'blue' => '#3b82f6',
            'indigo' => '#6366f1',
            'purple' => '#a855f7',
            default => '#71717a',
        };
    }
}
