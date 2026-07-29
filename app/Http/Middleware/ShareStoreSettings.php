<?php

namespace App\Http\Middleware;

use App\Support\StoreSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareStoreSettings
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('admin', 'admin/*')) {
            View::share('store', StoreSettings::forShop());
        }

        return $next($request);
    }
}
