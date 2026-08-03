<?php

namespace App\Listeners;

use App\Enums\LogAction;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Request;

class LogAuthenticationEvents
{
    public function handleLogin(Login $event): void
    {
        $user = $event->user;
        $isAdmin = method_exists($user, 'isAdmin') && $user->isAdmin();

        AuditLogService::log(
            $isAdmin ? LogAction::ADMIN_LOGIN_SUCCESS : LogAction::LOGIN_SUCCESS,
            $user->id,
            ['email' => $user->email],
            [],
            [],
            "ورود موفق از IP: " . Request::ip()
        );

        // بررسی IP/دستگاه جدید
        $this->checkNewDevice($user);
    }

    public function handleFailed(Failed $event): void
    {
        $email = $event->credentials['email'] ?? 'unknown';

        AuditLogService::log(
            LogAction::LOGIN_FAILED,
            null,
            ['email' => $email],
            [],
            [],
            "ورود ناموفق — ایمیل: {$email} — IP: " . Request::ip(),
            severity: LogSeverity::WARNING
        );

        // بررسی چندین تلاش ناموفق
        $this->checkMultipleFailedAttempts($email);
    }

    public function handleLogout(Logout $event): void
    {
        if ($event->user) {
            AuditLogService::log(
                LogAction::LOGOUT,
                $event->user->id,
                ['email' => $event->user->email]
            );
        }
    }

    private function checkNewDevice($user): void
    {
        $fingerprint = hash('sha256', Request::userAgent() . Request::ip());
        $cacheKey = "user_device:{$user->id}:{$fingerprint}";

        if (!\Cache::has($cacheKey)) {
            AuditLogService::log(
                LogAction::NEW_DEVICE_OR_IP,
                $user->id,
                ['fingerprint' => $fingerprint],
                severity: LogSeverity::HIGH
            );
            \Cache::put($cacheKey, true, now()->addDays(30));
        }
    }

    private function checkMultipleFailedAttempts(string $email): void
    {
        $ip = Request::ip();
        $cacheKey = "failed_login:{$ip}";
        $count = Cache::increment($cacheKey);
        Cache::put($cacheKey, $count, now()->addMinutes(5));

        if ($count >= 10) {
            AuditLogService::log(
                LogAction::MULTIPLE_LOGIN_FAILED,
                null,
                ['email' => $email, 'count' => $count],
                severity: LogSeverity::CRITICAL
            );

            // هشدار امنیتی
            SecurityAlertService::checkRealtime(
                LogAction::LOGIN_FAILED,
                $ip,
                null,
                ['email' => $email, 'count' => $count]
            );

            // Auto-block IP
            AuditLogService::blockIp(
                $ip,
                "10 failed login attempts in 5 minutes",
                until: now()->addHours(1)
            );
        }
    }
}