<?php

namespace App\Http\Middleware;

use App\Support\StoreSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StoreMaintenanceMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! StoreSettings::bool('maintenance_mode')) {
            return $next($request);
        }

        if ($request->is('admin', 'admin/*') || $request->is('up')) {
            return $next($request);
        }

        if ($request->user()?->isAdmin()) {
            return $next($request);
        }

        return response()->view('shop.maintenance', [
            'message' => StoreSettings::get('maintenance_message'),
            'storeName' => StoreSettings::get('store_name', config('app.name')),
        ], 503);
    }
}
