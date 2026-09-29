<?php

namespace App\Services;

use App\Models\SiteVisit;
use App\Support\UserAgentParser;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SiteVisitService
{
    public function record(Request $request): void
    {
        $key = $this->visitorKey($request);
        $today = today()->toDateString();

        // اگر بازدیدکننده امروز قبلاً ثبت شده → فقط شمارنده صفحه را زیاد کن
        $updated = SiteVisit::query()
            ->where('visit_date', $today)
            ->where('visitor_key', $key)
            ->increment('page_views');

        if (! $updated) {
            SiteVisit::query()->insertOrIgnore([
                'visit_date' => $today,
                'visitor_key' => $key,
                'page_views' => 1,
                'os' => UserAgentParser::os($request->userAgent()),
                'browser' => UserAgentParser::browser($request->userAgent()),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function shouldTrack(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->ajax() || $request->prefetch() || $request->header('Purpose') === 'prefetch') {
            return false;
        }

        if ($request->is('admin', 'admin/*', 'login', 'register', 'password/*', 'up', 'storage/*')) {
            return false;
        }

        return true;
    }

    public function todayCount(): int
    {
        return SiteVisit::query()
            ->whereDate('visit_date', today())
            ->count();
    }

    public function dailyChart(int $days = 30): Collection
    {
        $counts = SiteVisit::query()
            ->selectRaw('visit_date, COUNT(*) as visitors')
            ->where('visit_date', '>=', today()->subDays($days - 1))
            ->groupBy('visit_date')
            ->pluck('visitors', 'visit_date');

        return collect(range($days - 1, 0))->map(function (int $daysAgo) use ($counts) {
            $date = today()->subDays($daysAgo);

            return [
                'label' => format_jalali($date, 'm/d', false),
                'visitors' => (int) ($counts[$date->toDateString()] ?? 0),
            ];
        })->values();
    }

    private function visitorKey(Request $request): string
    {
        return hash('sha256', implode('|', [
            $request->ip(),
            $request->userAgent() ?? '',
            $request->session()->getId(),
        ]));
    }
}