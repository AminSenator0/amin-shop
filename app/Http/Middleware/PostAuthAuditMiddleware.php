<?php

namespace App\Http\Middleware;

use App\Enums\LogAction;
use App\Enums\LogSeverity;
use App\Models\AuditLog;
use App\Services\AuditLogService;
use App\Services\SecurityAlertService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class PostAuthAuditMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Auth::check() && session()->has('audit_login_pending')) {
            $loginData = session()->pull('audit_login_pending');
            $user = Auth::user();
            $isAdmin = method_exists($user, 'isAdmin') && $user->isAdmin();
            $action = $isAdmin ? LogAction::ADMIN_LOGIN_SUCCESS : LogAction::LOGIN_SUCCESS;

            try {
                // ═══════════════════════════════════════════════
                // ۱. Log Login Success
                // ═══════════════════════════════════════════════
                AuditLogService::log(
                    $action,
                    $user->id,
                    ['email' => $loginData['email'] ?? $user->email],
                    severity: $action->defaultSeverity()
                );

                // ═══════════════════════════════════════════════
                // ۲. Correlation: ورود موفق ادمین بعد از تلاش ناموفق
                // ═══════════════════════════════════════════════
                if ($isAdmin) {
                    SecurityAlertService::checkAdminLoginAfterFailures(
                        $user->id,
                        $request->ip(),
                        $user->name
                    );
                }

                // ═══════════════════════════════════════════════
                // ۳. Check New Device/IP
                // ═══════════════════════════════════════════════
                $this->checkNewDevice($user, $request);

            } catch (\Throwable $e) {
                \Log::error('[PostAuthAudit] failed: ' . $e->getMessage());
            }
        }

        return $response;
    }

    /**
     * بررسی IP/دستگاه جدید
     */
    private function checkNewDevice($user, Request $request): void
    {
        try {
            $fingerprint = hash('sha256', ($request->userAgent() ?? '') . $request->ip());
            $cacheKey = "user_device:{$user->id}:{$fingerprint}";

            if (!Cache::has($cacheKey)) {
                // اولین بار از این IP/دستگاه → لاگ CRITICAL
                // اولین بار از این IP/دستگاه → لاگ CRITICAL
                AuditLogService::log(
                    LogAction::NEW_DEVICE_OR_IP,
                    $user->id,
                    [
                        'fingerprint' => $fingerprint,
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ],
                    severity: LogSeverity::CRITICAL
                );

                // ذخیره برای ۳۰ روز
                Cache::put($cacheKey, true, now()->addDays(30));

                \Log::info("[AUDIT] New device/IP detected for user {$user->id}");
            }
        } catch (\Throwable $e) {
            \Log::error('[PostAuthAudit] checkNewDevice failed: ' . $e->getMessage());
        }
    }
}