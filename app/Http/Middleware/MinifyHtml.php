<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MinifyHtml
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $response->headers->has('Content-Type') || ! str_contains($response->headers->get('Content-Type'), 'text/html')) {
            return $response;
        }

        $content = $response->getContent();

        if (! $content) {
            return $response;
        }

        // Remove HTML comments (except IE conditionals)
        $content = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content);

        // Remove extra whitespace between tags
        $content = preg_replace('/>\s+</', '><', $content);

        // Remove leading/trailing whitespace in lines
        $content = preg_replace('/^\s+|\s+$/m', '', $content);

        // Remove multiple spaces
        $content = preg_replace('/\s{2,}/', ' ', $content);

        $response->setContent($content);

        return $response;
    }
}