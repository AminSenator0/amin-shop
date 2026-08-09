<?php
// app/Http/Middleware/SellerApiAuth.php

namespace App\Http\Middleware;

use Closure;
use App\Models\SellerDevice;
use Illuminate\Http\Request;

class SellerApiAuth
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-Seller-API-Key');

        if (! $apiKey || strlen($apiKey) < 32) {
            return response()->json(['message' => 'Unauthorized: API key missing'], 401);
        }

        $device = SellerDevice::where('api_key', $apiKey)
            ->where('is_active', true)
            ->first();

        if (! $device) {
            \Log::warning('Invalid seller API key attempt', [
                'ip' => $request->ip(),
                'key_prefix' => substr($apiKey, 0, 8) . '...'
            ]);
            return response()->json(['message' => 'Forbidden: Invalid API key'], 403);
        }

        // دیوایس رو به ریکوئست متصل می‌کنیم برای استفاده بعدی
        $request->attributes->set('seller_device', $device);

        return $next($request);
    }
}