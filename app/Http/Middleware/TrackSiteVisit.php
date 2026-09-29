<?php

namespace App\Http\Middleware;

use App\Services\SiteVisitService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrackSiteVisit
{
    public function __construct(private SiteVisitService $siteVisits) {}

    public function handle(Request $request, Closure $next): Response
    {
        Log::info('TrackSiteVisit REACHED', [
            'url' => $request->fullUrl(),
            'track' => $this->siteVisits->shouldTrack($request),
            'db' => DB::connection()->getDatabaseName(),
        ]);

        if ($this->siteVisits->shouldTrack($request)) {
            try {
                $this->siteVisits->record($request);
                Log::info('TrackSiteVisit RECORDED');
            } catch (\Throwable $e) {
                Log::error('TrackSiteVisit FAILED: '.$e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile().':'.$e->getLine(),
                ]);
            }
        }

        return $next($request);
    }
}