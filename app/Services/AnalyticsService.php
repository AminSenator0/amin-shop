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
    //  تب ۱ — نمودار بازدیدها
    // ═══════════════════════════════════════════════════════════

    /** خلاصه کارت‌های بالای صفحه */
    public function visitsSummary(int $days = self::VISITS_DAYS): array
    {
        return Cache::remember("admin.analytics.visits.summary.{$days}", now()->addMinutes(5), function () use ($days) {
            $today = today();

            $pageViewsToday = (int) SiteVisit::query()
                ->whereDate('visit_date', $today)
                ->sum('page_views');

            $uniquesToday = SiteVisit::query()
                ->whereDate('visit_date', $today)
                ->count();

            $from = $today->copy()->subDays($days - 1)->startOfDay();

            $avgPageViews = (int) round(SiteVisit::query()
                ->where('visit_date', '>=', $from->toDateString())
                ->sum('page_views') / $days);

            $avgUniques = (int) round(SiteVisit::query()
                ->where('visit_date', '>=', $from->toDateString())
                ->count() / $days);

            return [
                'page_views_today' => $pageViewsToday,
                'uniques_today' => $uniquesToday,
                'avg_page_views' => $avgPageViews,
                'avg_uniques' => $avgUniques,
            ];
        });
    }

    /** نمودار خطی: بازدید صفحه — N روز اخیر */
    public function pageViewsChart(int $days = self::VISITS_DAYS): Collection
    {
        return Cache::remember("admin.analytics.page_views.{$days}", now()->addMinutes(5), function () use ($days) {
            $counts = SiteVisit::query()
                ->selectRaw('visit_date, SUM(page_views) as total')
                ->where('visit_date', '>=', today()->subDays($days - 1)->toDateString())
                ->groupBy('visit_date')
                ->pluck('total', 'visit_date');

            return $this->dailySeries($days, $counts);
        });
    }

    /** نمودار خطی: بازدید یکتا — N روز اخیر */
    public function uniqueVisitorsChart(int $days = self::VISITS_DAYS): Collection
    {
        return Cache::remember("admin.analytics.uniques.{$days}", now()->addMinutes(5), function () use ($days) {
            $counts = SiteVisit::query()
                ->selectRaw('visit_date, COUNT(*) as total')
                ->where('visit_date', '>=', today()->subDays($days - 1)->toDateString())
                ->groupBy('visit_date')
                ->pluck('total', 'visit_date');

            return $this->dailySeries($days, $counts);
        });
    }

    /** نمودار دایره‌ای: سیستم‌عامل کاربران */
    public function osStats(int $days = self::VISITS_DAYS): Collection
    {
        return Cache::remember("admin.analytics.os.{$days}", now()->addMinutes(5), function () use ($days) {
            return $this->dimensionStats('os', $days);
        });
    }

    /** نمودار دایره‌ای: مرورگر کاربران */
    public function browserStats(int $days = self::VISITS_DAYS): Collection
    {
        return Cache::remember("admin.analytics.browser.{$days}", now()->addMinutes(5), function () use ($days) {
            return $this->dimensionStats('browser', $days);
        });
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
    private function dailySeries(int $days, Collection $counts): Collection
    {
        return collect(range($days - 1, 0))->map(function (int $daysAgo) use ($counts) {
            $date = today()->subDays($daysAgo);

            return [
                'label' => format_jalali($date, 'm/d', false),
                'value' => (int) ($counts[$date->toDateString()] ?? 0),
            ];
        })->values();
    }

    /** آمار یک بُعد (os / browser) بر اساس بازدید یکتا */
    private function dimensionStats(string $column, int $days): Collection
    {
        return SiteVisit::query()
            ->selectRaw("COALESCE({$column}, 'نامشخص') as label, COUNT(*) as value")
            ->where('visit_date', '>=', today()->subDays($days - 1)->toDateString())
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