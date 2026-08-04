<?php

namespace App\Http\Middleware;

use App\Enums\LogAction;
use App\Enums\LogSeverity;
use App\Services\AuditLogService;
use App\Services\SecurityAlertService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SuspiciousActivityMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $user = auth()->user();

        // ۱. شناسایی تلاش دسترسی به /admin توسط کاربر عادی
        if ($request->is('admin/*') && $user && !$user->isAdmin()) {
            AuditLogService::log(
                LogAction::ADMIN_ACCESS_ATTEMPT,
                $user->id,
                ['url' => $request->fullUrl()],
                severity: LogSeverity::HIGH
            );

            SecurityAlertService::checkRealtime(
                LogAction::ADMIN_ACCESS_ATTEMPT,
                $ip,
                $user->id,
                ['user_name' => $user->name, 'url' => $request->fullUrl()]
            );

            $this->trackAdminEndpointHits($ip, $user);
        }

        // ۲. شناسایی flood روی endpoint
        $endpoint = $request->path();
        $this->trackEndpointFlood($ip, $endpoint, $user?->id);

        // ۳. شناسایی درخواست‌های forgot password زیاد
        if ($request->is('*/forgot-password') && $request->isMethod('post')) {
            $this->trackForgotPassword($ip);
        }

        // ۴. شناسایی درخواست‌های reset password
        if ($request->is('*/reset-password') && $request->isMethod('post')) {
            $this->trackResetPassword($ip, $user?->id);
        }

        // ۵. شناسایی درخواست‌های پرداخت ناموفق متعدد
        if ($request->is('*/payment/*') && $request->isMethod('post')) {
            $this->trackPaymentAttempts($ip, $user?->id);
        }

        $response = $next($request);

        // ۶. شمارش خطاهای 403 پس از پاسخ
        if ($response->getStatusCode() === 403) {
            $this->track403Errors($ip, $user?->id);
        }

        return $response;
    }

    private function trackAdminEndpointHits(string $ip, $user): void
    {
        $cacheKey = "admin_hits:{$ip}:{$user->id}";
        $count = Cache::increment($cacheKey);
        Cache::put($cacheKey, $count, now()->addMinutes(5));

        if ($count >= 3) {
            AuditLogService::log(
                LogAction::USER_HIT_ADMIN_ENDPOINTS,
                $user->id,
                ['count' => $count, 'ip' => $ip],
                severity: LogSeverity::HIGH
            );

            SecurityAlertService::checkRealtime(
                LogAction::USER_HIT_ADMIN_ENDPOINTS,
                $ip,
                $user->id,
                ['user_name' => $user->name, 'count' => $count]
            );
        }
    }

    private function trackEndpointFlood(string $ip, string $endpoint, ?int $userId): void
    {
        $cacheKey = "endpoint_hits:{$ip}:{$endpoint}";
        $hits = Cache::increment($cacheKey);
        Cache::put($cacheKey, $hits, now()->addMinute());

        if ($hits >= 100) {
            AuditLogService::log(
                LogAction::ENDPOINT_FLOOD,
                $userId,
                ['endpoint' => $endpoint, 'hits' => $hits],
                severity: LogSeverity::CRITICAL
            );

            SecurityAlertService::checkRealtime(
                LogAction::ENDPOINT_FLOOD,
                $ip,
                $userId,
                ['endpoint' => $endpoint, 'hits' => $hits]
            );

            AuditLogService::blockIp($ip, "100+ requests to {$endpoint} in 1 minute", until: now()->addMinutes(30));
        }
    }

    private function trackForgotPassword(string $ip): void
    {
        $cacheKey = "forgot_password:{$ip}";
        $count = Cache::increment($cacheKey);
        Cache::put($cacheKey, $count, now()->addMinutes(5));

        if ($count >= 5) {
            // ✅ هشدار امنیتی
            AuditLogService::log(
                LogAction::MASS_FORGOT_PASSWORD,
                null,
                ['ip' => $ip, 'count' => $count],
                severity: LogSeverity::HIGH
            );

            SecurityAlertService::checkRealtime(
                LogAction::FORGOT_PASSWORD_REQUESTED,
                $ip,
                null,
                ['count' => $count]
            );

            Cache::forget($cacheKey);
        }
    }

    private function trackResetPassword(string $ip, ?int $userId): void
    {
        $cacheKey = "reset_password:{$ip}";
        $count = Cache::increment($cacheKey);
        Cache::put($cacheKey, $count, now()->addMinutes(10));

        // شمارش کاربران مختلف
        $usersKey = "reset_password_users:{$ip}";
        $users = Cache::get($usersKey, []);
        if ($userId && !in_array($userId, $users)) {
            $users[] = $userId;
            Cache::put($usersKey, $users, now()->addMinutes(10));
        }

        if ($count >= 10 || count($users) >= 3) {
            // ✅ هشدار امنیتی
            AuditLogService::log(
                LogAction::MASS_RESET_REQUESTS,
                null,
                ['ip' => $ip, 'count' => $count, 'unique_users' => count($users)],
                severity: LogSeverity::CRITICAL
            );

            SecurityAlertService::checkRealtime(
                LogAction::RESET_PASSWORD_SUCCESS,
                $ip,
                null,
                ['count' => $count, 'unique_users' => count($users)]
            );

            Cache::forget($cacheKey);
            Cache::forget($usersKey);
        }
    }

    private function trackPaymentAttempts(string $ip, ?int $userId): void
    {
        $cacheKey = "payment_attempts:{$ip}";
        $count = Cache::increment($cacheKey);
        Cache::put($cacheKey, $count, now()->addMinutes(10));

        if ($count >= 5) {
            AuditLogService::log(
                LogAction::MULTIPLE_PAYMENT_ATTEMPTS,
                $userId,
                ['ip' => $ip, 'count' => $count],
                severity: LogSeverity::HIGH
            );
        }
    }

    private function track403Errors(string $ip, ?int $userId): void
    {
        $cacheKey = "403_errors:{$ip}";
        $count = Cache::increment($cacheKey);
        Cache::put($cacheKey, $count, now()->addMinutes(5));

        if ($count >= 20) {
            AuditLogService::log(
                LogAction::MASS_403_ERRORS,
                $userId,
                ['ip' => $ip, 'count' => $count],
                severity: LogSeverity::HIGH
            );

            SecurityAlertService::checkRealtime(
                LogAction::MASS_403_ERRORS,
                $ip,
                $userId,
                ['count' => $count]
            );
        }
    }
}