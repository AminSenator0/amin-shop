<?php

namespace App\Http\Middleware;

use App\Services\SiteVisitService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackSiteVisit
{
    public function __construct(private SiteVisitService $siteVisits) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->siteVisits->shouldTrack($request)) {
            try {
                $this->siteVisits->record($request);
            } catch (\Throwable) {
                // Do not block storefront traffic if visit tracking is unavailable.
            }
        }

        return $next($request);
    }
}
