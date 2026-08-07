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

        // Content Security Policy
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
               "style-src 'self' 'unsafe-inline'; " .
               "img-src 'self' data: blob: https:; " .
               "font-src 'self'; " .
               "connect-src 'self'; " .
               "frame-ancestors 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self';";

        $response->headers->set('Content-Security-Policy', $csp);

        // X-XSS-Protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}