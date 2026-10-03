<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\BlogPost;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SiteVisit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public const VISITS_DAYS = 15;
    public const TOP_SELLING_LIMIT = 10;
    public const TOP_VIEWED_PRODUCTS_LIMIT = 10;
    public const TOP_VIEWED_POSTS_LIMIT = 20;

    public const PERIODS = ['all', 'today', 'week', 'month'];

    // ═══════════════════════════════════════════════════════════
    //  تب ۱ — نمودار بازدیدها (بر اساس بازه تاریخی)
    // ═══════════════════════════════════════════════════════════

    /** خلاصه کارت‌های بالای صفحه — روی بازه انتخاب‌شده */
    public function visitsSummary(Carbon $from, Carbon $to): array
    {
        $key = "admin.analytics.visits.summary.{$from->toDateString()}.{$to->toDateString()}";

        return Cache::remember($key, now()->addMinutes(5), function () use ($from, $to) {
            $days = max(1, $from->diffInDays($to) + 1);

            $pageViewsTotal = (int) SiteVisit::query()
                ->whereBetween('visit_date', [$from->toDateString(), $to->toDateString()])
                ->sum('page_views');

            $uniquesTotal = SiteVisit::query()
                ->whereBetween('visit_date', [$from->toDateString(), $to->toDateString()])
                ->count();

            return [
                'page_views_total' => $pageViewsTotal,
                'uniques_total'    => $uniquesTotal,
                'avg_page_views'   => (int) round($pageViewsTotal / $days),
                'avg_uniques'      => (int) round($uniquesTotal / $days),
                'days'             => $days,
            ];
        });
    }

    /** نمودار خطی: بازدید صفحه */
    public function pageViewsChart(Carbon $from, Carbon $to): Collection
    {
        $key = "admin.analytics.page_views.{$from->toDateString()}.{$to->toDateString()}";

        return Cache::remember($key, now()->addMinutes(5), function () use ($from, $to) {
            $counts = SiteVisit::query()
                ->selectRaw('visit_date, SUM(page_views) as total')
                ->whereBetween('visit_date', [$from->toDateString(), $to->toDateString()])
                ->groupBy('visit_date')
                ->pluck('total', 'visit_date');

            return $this->dailySeries($from, $to, $counts);
        });
    }

    /** نمودار خطی: بازدید یکتا */
    public function uniqueVisitorsChart(Carbon $from, Carbon $to): Collection
    {
        $key = "admin.analytics.uniques.{$from->toDateString()}.{$to->toDateString()}";

        return Cache::remember($key, now()->addMinutes(5), function () use ($from, $to) {
            $counts = SiteVisit::query()
                ->selectRaw('visit_date, COUNT(*) as total')
                ->whereBetween('visit_date', [$from->toDateString(), $to->toDateString()])
                ->groupBy('visit_date')
                ->pluck('total', 'visit_date');

            return $this->dailySeries($from, $to, $counts);
        });
    }

    /** نمودار دایره‌ای: سیستم‌عامل کاربران */
    public function osStats(Carbon $from, Carbon $to): Collection
    {
        $key = "admin.analytics.os.{$from->toDateString()}.{$to->toDateString()}";

        return Cache::remember($key, now()->addMinutes(5), function () use ($from, $to) {
            return $this->dimensionStats('os', $from, $to);
        });
    }

    /** نمودار دایره‌ای: مرورگر کاربران */
    public function browserStats(Carbon $from, Carbon $to): Collection
    {
        $key = "admin.analytics.browser.{$from->toDateString()}.{$to->toDateString()}";

        return Cache::remember($key, now()->addMinutes(5), function () use ($from, $to) {
            return $this->dimensionStats('browser', $from, $to);
        });
    }

    /** اولین تاریخی که بازدید ثبت شده (برای بازه «از ابتدا») */
    public function firstVisitDate(): ?Carbon
    {
        $min = Cache::remember('admin.analytics.first_visit', now()->addHour(), function () {
            return SiteVisit::query()->min('visit_date');
        });

        return $min ? Carbon::parse($min) : null;
    }

    // ═══════════════════════════════════════════════════════════
    //  تب ۲ — آمار محصولات
    // ═══════════════════════════════════════════════════════════

    /**
     * محصولات پرفروش با فیلتر بازه زمانی.
     * $period: all | today | week | month
     */
    public function topSellingProducts(string $period = 'all', int $limit = self::TOP_SELLING_LIMIT): Collection
    {
        $period = in_array($period, self::PERIODS, true) ? $period : 'all';

        return Cache::remember(
            "admin.analytics.top_selling.{$period}.{$limit}",
            now()->addMinutes(5),
            function () use ($period, $limit) {
                [$from, $to] = $this->periodRange($period);

                return OrderItem::query()
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
                    ->where('orders.status', '!=', OrderStatus::Cancelled)
                    ->when($from, fn ($q) => $q->whereBetween('orders.created_at', [$from, $to]))
                    ->select('order_items.product_id')
                    ->selectRaw('COALESCE(products.name, order_items.product_name) as name')
                    ->selectRaw('COALESCE(products.image, NULL) as image')
                    ->selectRaw('COALESCE(products.slug, NULL) as slug')
                    ->selectRaw('SUM(order_items.quantity) as total_sold')
                    ->selectRaw('MAX(COALESCE(products.updated_at, orders.created_at)) as last_activity')
                    ->groupBy('order_items.product_id', 'products.name', 'products.image', 'products.slug', 'order_items.product_name')
                    ->orderByDesc('total_sold')
                    ->limit($limit)
                    ->get()
                    ->map(fn ($row) => [
                        'product_id' => $row->product_id,
                        'name' => $row->name,
                        'image' => $row->image ? asset('storage/'.$row->image) : asset('images/store-logo.svg'),
                        'url' => $row->slug ? route('products.show', $row->slug) : null,
                        'total_sold' => (int) $row->total_sold,
                        'last_activity' => Carbon::parse($row->last_activity),
                    ]);
            }
        );
    }

    /** محصولات پربازدید (بر اساس شمارنده views) */
    public function topViewedProducts(int $limit = self::TOP_VIEWED_PRODUCTS_LIMIT): Collection
    {
        return Cache::remember("admin.analytics.top_viewed_products.{$limit}", now()->addMinutes(5), function () use ($limit) {
            return Product::query()
                ->where('is_active', true)
                ->where('views', '>', 0)
                ->orderByDesc('views')
                ->limit($limit)
                ->get(['id', 'name', 'slug', 'image', 'views', 'updated_at']);
        });
    }

    // ═══════════════════════════════════════════════════════════
    //  تب ۳ — آمار مقالات
    // ═══════════════════════════════════════════════════════════

    /** مقالات پربازدید */
    public function topViewedPosts(int $limit = self::TOP_VIEWED_POSTS_LIMIT): Collection
    {
        return Cache::remember("admin.analytics.top_viewed_posts.{$limit}", now()->addMinutes(5), function () use ($limit) {
            return BlogPost::query()
                ->where('is_published', true)
                ->where('views', '>', 0)
                ->orderByDesc('views')
                ->limit($limit)
                ->get(['id', 'title', 'slug', 'views', 'updated_at']);
        });
    }

    // ═══════════════════════════════════════════════════════════
    //  ابزارهای داخلی
    // ═══════════════════════════════════════════════════════════

    /** ساخت سری روزانه با مقادیر صفر برای روزهای بدون داده */
    private function dailySeries(Carbon $from, Carbon $to, Collection $counts): Collection
    {
        $result = collect();

        for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
            $result->push([
                'label' => format_jalali($date, 'm/d', false),
                'value' => (int) ($counts[$date->toDateString()] ?? 0),
            ]);
        }

        return $result->values();
    }

    /** آمار یک بُعد (os / browser) بر اساس بازدید یکتا */
    private function dimensionStats(string $column, Carbon $from, Carbon $to): Collection
    {
        return SiteVisit::query()
            ->selectRaw("COALESCE({$column}, 'نامشخص') as label, COUNT(*) as value")
            ->whereBetween('visit_date', [$from->toDateString(), $to->toDateString()])
            ->groupBy($column)
            ->orderByDesc('value')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->label,
                'value' => (int) $row->value,
            ]);
    }

    /**
     * بازه زمانی فیلتر پرفروش‌ها.
     * خروجی: [?Carbon $from, ?Carbon $to] — برای 'all' هر دو null
     */
    private function periodRange(string $period): array
    {
        return match ($period) {
            'today' => [today()->startOfDay(), today()->endOfDay()],
            'week' => [today()->subDays(6)->startOfDay(), today()->endOfDay()],
            'month' => [today()->subDays(29)->startOfDay(), today()->endOfDay()],
            default => [null, null],
        };
    }
}