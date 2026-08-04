<?php

namespace App\Http\Middleware;

use App\Enums\LogAction;
use App\Enums\LogSeverity;
use App\Models\AuditLog;
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
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => $action,
                    'category' => $action->category(),
                    'severity' => $action->defaultSeverity(),
                    'ip_address' => $request->ip(),
                    'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                    'device_fingerprint' => hash('sha256', $request->userAgent() . $request->ip()),
                    'url' => substr($request->fullUrl(), 0, 500),
                    'method' => 'POST',
                    'payload' => ['email' => $loginData['email'] ?? $user->email],
                    'description' => "ورود موفق از IP: " . $request->ip(),
                    'created_at' => now(),
                ]);

                // ═══════════════════════════════════════════════
                // ۲. Check New Device/IP
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
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => LogAction::NEW_DEVICE_OR_IP,
                    'category' => LogAction::NEW_DEVICE_OR_IP->category(),
                    'severity' => LogSeverity::HIGH,
                    'ip_address' => $request->ip(),
                    'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                    'device_fingerprint' => $fingerprint,
                    'url' => substr($request->fullUrl(), 0, 500),
                    'method' => 'POST',
                    'payload' => [
                        'fingerprint' => $fingerprint,
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ],
                    'description' => "ورود از IP/دستگاه جدید: " . $request->ip(),
                    'created_at' => now(),
                ]);

                // ذخیره برای ۳۰ روز
                Cache::put($cacheKey, true, now()->addDays(30));

                \Log::info("[AUDIT] New device/IP detected for user {$user->id}");
            }
        } catch (\Throwable $e) {
            \Log::error('[PostAuthAudit] checkNewDevice failed: ' . $e->getMessage());
        }
    }
}