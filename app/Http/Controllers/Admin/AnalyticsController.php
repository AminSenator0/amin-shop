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
    public function visits(Request $request)
    {
        [$from, $to, $rangeKey, $rangeTitle] = $this->resolveVisitRange($request);

        $summary         = $this->analytics->visitsSummary($from, $to);
        $pageViews       = $this->analytics->pageViewsChart($from, $to);
        $uniqueVisitors  = $this->analytics->uniqueVisitorsChart($from, $to);
        $osStats         = $this->analytics->osStats($from, $to);
        $browserStats    = $this->analytics->browserStats($from, $to);

        return view('admin.analytics.visits', compact(
            'summary', 'pageViews', 'uniqueVisitors', 'osStats', 'browserStats', 'rangeKey', 'rangeTitle'
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
        /**
     * تشخیص بازه زمانی از کوئری‌استرینگ.
     * خروجی: [Carbon $from, Carbon $to, string $rangeKey, string $rangeTitle]
     */
    private function resolveVisitRange(Request $request): array
    {
        $range = (string) $request->query('range', '15');
        $to = today();

        if ($range === 'custom') {
            $fromC = $this->parseJalaliDate($request->query('from'));
            $toC   = $this->parseJalaliDate($request->query('to'));

            if ($fromC && $toC) {
                // جابه‌جایی اشتباه کاربر (to قبل از from) رو خودکار اصلاح کن
                [$fromC, $toC] = $fromC->lte($toC) ? [$fromC, $toC] : [$toC, $fromC];

                return [
                    $fromC->copy()->startOfDay(),
                    $toC->copy()->startOfDay(),
                    'custom',
                    'از '.format_jalali($fromC, 'Y/m/d', false).' تا '.format_jalali($toC, 'Y/m/d', false),
                ];
            }

            $range = '15'; // تاریخ نامعتبر → برگرد به پیش‌فرض
        }

        return match ($range) {
            'today' => [$to->copy(), $to->copy(), 'today', 'امروز'],
            'week'  => [$to->copy()->subDays(6), $to->copy(), 'week', '۷ روز اخیر'],
            '30'    => [$to->copy()->subDays(29), $to->copy(), '30', '۳۰ روز اخیر'],
            'all'   => [
                $this->analytics->firstVisitDate() ?? $to->copy()->subDays(29),
                $to->copy(),
                'all',
                'از ابتدا',
            ],
            default => [$to->copy()->subDays(14), $to->copy(), '15', '۱۵ روز اخیر'],
        };
    }

    /**
     * پارس تاریخ شمسی با فرمت 1403/05/01 → Carbon میلادی.
     * نامعتبر → null
     */
    private function parseJalaliDate(mixed $value): ?\Illuminate\Support\Carbon
    {
        if (! is_string($value)) {
            return null;
        }

        if (! preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/u', trim($value), $m)) {
            return null;
        }

        [$jy, $jm, $jd] = [(int) $m[1], (int) $m[2], (int) $m[3]];

        if ($jm < 1 || $jm > 12 || $jd < 1 || $jd > 31) {
            return null;
        }

        [$gy, $gm, $gd] = $this->jalaliToGregorian($jy, $jm, $jd);

        return \Illuminate\Support\Carbon::createFromDate($gy, $gm, $gd)->startOfDay();
    }

    /**
     * الگوریتم استاندارد تبدیل تاریخ جلالی به میلادی (jalaali).
     */
    private function jalaliToGregorian(int $jy, int $jm, int $jd): array
    {
        $jy += 1595;
        $days = -355668
            + (365 * $jy)
            + (((int) ($jy / 33)) * 8)
            + ((int) ((($jy % 33) + 3) / 4))
            + $jd
            + (($jm < 7) ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186);

        $gy = 400 * ((int) ($days / 146097));
        $days %= 146097;

        if ($days > 36524) {
            $gy += 100 * ((int) (--$days / 36524));
            $days %= 36524;
            if ($days >= 365) {
                $days++;
            }
        }

        $gy += 4 * ((int) ($days / 1461));
        $days %= 1461;

        if ($days > 365) {
            $gy += (int) (($days - 1) / 365);
            $days = ($days - 1) % 365;
        }

        $gd = $days + 1;

        $monthLengths = [0, 31, (($gy % 4 === 0 && $gy % 100 !== 0) || ($gy % 400 === 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        $gm = 1;
        while ($gm <= 12 && $gd > $monthLengths[$gm]) {
            $gd -= $monthLengths[$gm];
            $gm++;
        }

        return [$gy, $gm, $gd];
    }
}