<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsService $analytics,
    ) {}

    /** تب ۱ — نمودار بازدیدها */
    public function visits()
    {
        $summary = $this->analytics->visitsSummary();
        $pageViews = $this->analytics->pageViewsChart();
        $uniqueVisitors = $this->analytics->uniqueVisitorsChart();
        $osStats = $this->analytics->osStats();
        $browserStats = $this->analytics->browserStats();

        return view('admin.analytics.visits', compact(
            'summary', 'pageViews', 'uniqueVisitors', 'osStats', 'browserStats'
        ));
    }

    /** تب ۲ — آمار محصولات */
    public function products(Request $request)
    {
        $period = $request->query('period', 'all');
        if (! in_array($period, AnalyticsService::PERIODS, true)) {
            $period = 'all';
        }

        $topSelling = $this->analytics->topSellingProducts($period);
        $topViewed = $this->analytics->topViewedProducts();

        return view('admin.analytics.products', compact('topSelling', 'topViewed', 'period'));
    }

    /** تب ۳ — آمار مقالات */
    public function blog()
    {
        $topPosts = $this->analytics->topViewedPosts();

        return view('admin.analytics.blog', compact('topPosts'));
    }
}