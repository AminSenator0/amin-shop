<?php

namespace App\Http\Middleware;

use App\Enums\LogAction;
use App\Enums\LogSeverity;
use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (AuditLogService::isIpBlocked()) {
            abort(403, 'Access denied.');
        }

        $response = $next($request);

        if ($response->getStatusCode() >= 400) {
            $this->logErrorResponse($request, $response);
        }

        return $response;
    }

    private function logErrorResponse(Request $request, Response $response): void
    {
        $action = match($response->getStatusCode()) {
            403 => LogAction::MASS_403_ERRORS,
            404 => LogAction::ABNORMAL_HTTP_REQUESTS,
            500, 502, 503 => LogAction::EXCEPTION_THROWN,
            default => LogAction::ABNORMAL_HTTP_REQUESTS,
        };

        AuditLogService::log(
            $action,
            auth()->id(),
            [
                'status_code' => $response->getStatusCode(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ],
            severity: $response->getStatusCode() >= 500 ? LogSeverity::CRITICAL : LogSeverity::WARNING
        );
    }
}