<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // جلوگیری از Clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // جلوگیری از MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // form-action: سایت خودم + درگاه زرین‌پال (سندباکس و واقعی)
        // چرا زرین‌پال؟ چون فرم POST بعدش 302 به درگاه می‌شه و مرورگر مقصد نهایی رو با form-action چک می‌کنه
        $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?: $request->getHost();
        $appPort = parse_url(config('app.url'), PHP_URL_PORT);
        $appPort = $appPort ? ':'.$appPort : '';

        // Content Security Policy
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
               "style-src 'self' 'unsafe-inline'; " .
               "img-src 'self' data: blob: https:; " .
               "font-src 'self'; " .
               "connect-src 'self'; " .
               "frame-ancestors 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self' http://{$appHost}{$appPort} https://{$appHost}{$appPort} https://*.zarinpal.com;";

        $response->headers->set('Content-Security-Policy', $csp);

        // X-XSS-Protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}