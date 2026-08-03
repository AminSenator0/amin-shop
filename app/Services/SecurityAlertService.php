<?php

namespace App\Services;

use App\Enums\LogAction;
use App\Enums\LogSeverity;
use App\Models\AuditLog;
use App\Models\BlockedIp;
use App\Models\SecurityAlert;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SecurityAlertService
{
    /**
     * قوانین هشدار: [action_pattern => [threshold, window_minutes, severity, auto_block_minutes]]
     */
    private static array $rules = [
        'login_failed' => [
            'threshold' => 10,
            'window' => 5,
            'severity' => LogSeverity::CRITICAL,
            'alert_type' => 'mass_login_failed',
            'message' => ':count تلاش ورود ناموفق از IP :ip در :window دقیقه',
            'auto_block' => 60,
        ],
        'forgot_password' => [
            'threshold' => 5,
            'window' => 5,
            'severity' => LogSeverity::HIGH,
            'alert_type' => 'mass_forgot_password',
            'message' => ':count درخواست فراموشی رمز از IP :ip در :window دقیقه',
            'auto_block' => 30,
        ],
        'reset_password' => [
            'threshold' => 10,
            'window' => 10,
            'severity' => LogSeverity::CRITICAL,
            'alert_type' => 'mass_reset_requests',
            'message' => ':count درخواست ریست رمز برای کاربران مختلف از IP :ip',
            'auto_block' => 60,
        ],
        'endpoint_flood' => [
            'threshold' => 100,
            'window' => 1,
            'severity' => LogSeverity::CRITICAL,
            'alert_type' => 'endpoint_flood',
            'message' => ':count درخواست به :endpoint از IP :ip در :window دقیقه',
            'auto_block' => 30,
        ],
        'admin_access_attempt' => [
            'threshold' => 1,
            'window' => 0,
            'severity' => LogSeverity::HIGH,
            'alert_type' => 'admin_access_attempt',
            'message' => 'تلاش دسترسی به پنل ادمین توسط کاربر عادی :user از IP :ip',
            'auto_block' => null,
        ],
        'user_hit_admin_endpoints' => [
            'threshold' => 3,
            'window' => 5,
            'severity' => LogSeverity::HIGH,
            'alert_type' => 'user_hit_admin_endpoints',
            'message' => 'کاربر عادی :user :count بار endpoint ادمین را فراخوانی کرد',
            'auto_block' => null,
        ],
        'rapid_price_changes' => [
            'threshold' => 10,
            'window' => 10,
            'severity' => LogSeverity::WARNING,
            'alert_type' => 'rapid_price_changes',
            'message' => ':count تغییر قیمت توسط ادمین :admin در :window دقیقه',
            'auto_block' => null,
        ],
        'coupon_reuse' => [
            'threshold' => 5,
            'window' => 10,
            'severity' => LogSeverity::HIGH,
            'alert_type' => 'coupon_reuse_attempt',
            'message' => ':count تلاش استفاده چندباره کوپن از IP :ip',
            'auto_block' => null,
        ],
        'order_amount_tampered' => [
            'threshold' => 1,
            'window' => 0,
            'severity' => LogSeverity::CRITICAL,
            'alert_type' => 'order_amount_tampered',
            'message' => 'تغییر مشکوک مبلغ سفارش :order توسط کاربر :user',
            'auto_block' => null,
        ],
        'payment_callback_mismatch' => [
            'threshold' => 1,
            'window' => 0,
            'severity' => LogSeverity::CRITICAL,
            'alert_type' => 'payment_callback_mismatch',
            'message' => 'عدم تطابق مبلغ در callback پرداخت سفارش :order',
            'auto_block' => null,
        ],
        'abnormal_http' => [
            'threshold' => 20,
            'window' => 5,
            'severity' => LogSeverity::HIGH,
            'alert_type' => 'abnormal_http_requests',
            'message' => ':count درخواست HTTP غیرعادی از IP :ip در :window دقیقه',
            'auto_block' => 15,
        ],
        'mass_403' => [
            'threshold' => 20,
            'window' => 5,
            'severity' => LogSeverity::HIGH,
            'alert_type' => 'mass_403_errors',
            'message' => ':count خطای 403 از IP :ip در :window دقیقه',
            'auto_block' => 15,
        ],
    ];

    /**
     * بررسی Real-time پس از هر لاگ
     */
    public static function checkRealtime(LogAction $action, ?string $ip, ?int $userId, array $payload = []): void
    {
        if (!$ip) return;

        $actionKey = match($action) {
            LogAction::LOGIN_FAILED => 'login_failed',
            LogAction::FORGOT_PASSWORD_REQUESTED => 'forgot_password',
            LogAction::RESET_PASSWORD_SUCCESS => 'reset_password',
            LogAction::ADMIN_ACCESS_ATTEMPT => 'admin_access_attempt',
            LogAction::USER_HIT_ADMIN_ENDPOINTS => 'user_hit_admin_endpoints',
            LogAction::PAYMENT_CALLBACK_MISMATCH => 'payment_callback_mismatch',
            LogAction::ORDER_AMOUNT_TAMPERED => 'order_amount_tampered',
            LogAction::MASS_403_ERRORS => 'mass_403',
            LogAction::ABNORMAL_HTTP_REQUESTS => 'abnormal_http',
            LogAction::COUPON_REUSE_ATTEMPT => 'coupon_reuse',
            default => null,
        };

        if (!$actionKey || !isset(self::$rules[$actionKey])) return;

        $rule = self::$rules[$actionKey];

        // برخی‌ها فوراً هشدار (threshold=1)
        if ($rule['threshold'] === 1) {
            self::createAlert($rule, $ip, $userId, $payload, 1);
            return;
        }

        // شمارش با cache
        $cacheKey = "security_count:{$actionKey}:{$ip}";
        $count = Cache::increment($cacheKey);
        Cache::put($cacheKey, $count, now()->addMinutes($rule['window']));

        if ($count >= $rule['threshold']) {
            self::createAlert($rule, $ip, $userId, $payload, $count);
            Cache::forget($cacheKey); // Reset after alert
        }
    }

    /**
     * بررسی Batch patterns (فراخوانی از Command یا Scheduler)
     */
    public static function checkBatchPatterns(): void
    {
        self::checkRapidPriceChanges();
        self::checkEndpointFloods();
        self::checkMassResetRequests();
        self::checkCouponReusePatterns();
    }

    /**
     * تغییرات سریع قیمت
     */
    private static function checkRapidPriceChanges(): void
    {
        $rule = self::$rules['rapid_price_changes'];
        $since = now()->subMinutes($rule['window']);

        $admins = AuditLog::where('action', LogAction::PRICE_CHANGED)
            ->where('created_at', '>=', $since)
            ->selectRaw('user_id, COUNT(*) as count')
            ->groupBy('user_id')
            ->having('count', '>=', $rule['threshold'])
            ->get();

        foreach ($admins as $admin) {
            $logs = AuditLog::where('action', LogAction::PRICE_CHANGED)
                ->where('user_id', $admin->user_id)
                ->where('created_at', '>=', $since)
                ->pluck('id');

            self::createAlert(
                $rule,
                null,
                $admin->user_id,
                ['log_ids' => $logs->toArray(), 'count' => $admin->count],
                $admin->count
            );
        }
    }

    /**
     * Endpoint Flood (از cache هم چک می‌کنیم، این backup است)
     */
    private static function checkEndpointFloods(): void
    {
        $rule = self::$rules['endpoint_flood'];
        $since = now()->subMinutes($rule['window']);

        // بررسی از دیتابیس برای مواردی که cache miss شده
        $floods = AuditLog::where('created_at', '>=', $since)
            ->selectRaw('ip_address, url, COUNT(*) as count')
            ->groupBy('ip_address', 'url')
            ->having('count', '>=', $rule['threshold'])
            ->get();

        foreach ($floods as $flood) {
            self::createAlert(
                $rule,
                $flood->ip_address,
                null,
                ['endpoint' => $flood->url, 'count' => $flood->count],
                $flood->count
            );
        }
    }

    /**
     * Reset Password برای کاربران مختلف
     */
    private static function checkMassResetRequests(): void
    {
        $rule = self::$rules['reset_password'];
        $since = now()->subMinutes($rule['window']);

        $ips = AuditLog::where('action', LogAction::RESET_PASSWORD_SUCCESS)
            ->where('created_at', '>=', $since)
            ->selectRaw('ip_address, COUNT(DISTINCT user_id) as unique_users, COUNT(*) as count')
            ->groupBy('ip_address')
            ->having('unique_users', '>=', 3) // حداقل ۳ کاربر مختلف
            ->get();

        foreach ($ips as $ip) {
            self::createAlert(
                $rule,
                $ip->ip_address,
                null,
                ['unique_users' => $ip->unique_users, 'count' => $ip->count],
                $ip->count
            );
        }
    }

    /**
     * الگوی استفاده چندباره کوپن
     */
    private static function checkCouponReusePatterns(): void
    {
        $rule = self::$rules['coupon_reuse'];
        $since = now()->subMinutes($rule['window']);

        $ips = AuditLog::where('action', LogAction::COUPON_REUSE_ATTEMPT)
            ->where('created_at', '>=', $since)
            ->selectRaw('ip_address, COUNT(*) as count')
            ->groupBy('ip_address')
            ->having('count', '>=', $rule['threshold'])
            ->get();

        foreach ($ips as $ip) {
            self::createAlert(
                $rule,
                $ip->ip_address,
                null,
                ['count' => $ip->count],
                $ip->count
            );
        }
    }

    /**
     * ساخت هشدار
     */
    private static function createAlert(array $rule, ?string $ip, ?int $userId, array $payload, int $count): void
    {
        $alertType = $rule['alert_type'];

        // Deduplication: اگر هشدار فعال (unresolved) برای همین IP/type وجود داشت، آپدیت کن
        $existing = SecurityAlert::unresolved()
            ->where('alert_type', $alertType)
            ->when($ip, fn($q) => $q->where('ip_address', $ip))
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->where('created_at', '>=', now()->subHours(1))
            ->first();

        if ($existing) {
            $evidence = $existing->evidence ?? [];
            $evidence['occurrences'] = ($evidence['occurrences'] ?? 1) + 1;
            $evidence['last_count'] = $count;
            $evidence['last_seen'] = now()->toDateTimeString();
            $existing->update(['evidence' => $evidence]);
            return;
        }

        // ساخت پیام
        $message = str_replace(
            [':count', ':ip', ':window', ':endpoint', ':user', ':admin', ':order'],
            [
                $count,
                $ip ?? 'unknown',
                $rule['window'] ?? 0,
                $payload['endpoint'] ?? 'unknown',
                $payload['user_name'] ?? ($userId ? "User#{$userId}" : 'unknown'),
                $payload['admin_name'] ?? ($userId ? "Admin#{$userId}" : 'unknown'),
                $payload['order_code'] ?? ($payload['order_id'] ?? 'unknown'),
            ],
            $rule['message']
        );

        // جمع‌آوری evidence
        $evidence = array_merge($payload, [
            'triggered_at' => now()->toDateTimeString(),
            'threshold' => $rule['threshold'],
            'actual_count' => $count,
            'window_minutes' => $rule['window'] ?? 0,
        ]);

        $alert = SecurityAlert::create([
            'alert_type' => $alertType,
            'severity' => $rule['severity'],
            'ip_address' => $ip,
            'user_id' => $userId,
            'message' => $message,
            'evidence' => $evidence,
            'is_resolved' => false,
        ]);

        // Auto-block IP
        if ($ip && !empty($rule['auto_block'])) {
            AuditLogService::blockIp(
                $ip,
                "Auto-blocked by security alert: {$alertType}",
                until: now()->addMinutes($rule['auto_block'])
            );
        }

        // لاگ در system log
        Log::warning("[SECURITY ALERT] {$message}", [
            'alert_id' => $alert->id,
            'type' => $alertType,
            'severity' => $rule['severity']->value,
            'ip' => $ip,
        ]);
    }

    /**
     * شمارش هشدارهای فعال (برای badge پنل)
     */
    public static function getUnresolvedCount(): int
    {
        return Cache::remember('security_alerts_unresolved', 30, function () {
            return SecurityAlert::unresolved()->count();
        });
    }

    /**
     * شمارش هشدارهای critical فعال
     */
    public static function getCriticalCount(): int
    {
        return Cache::remember('security_alerts_critical', 30, function () {
            return SecurityAlert::unresolved()->critical()->count();
        });
    }

    /**
     * خلاصه آمار برای داشبورد
     */
    public static function getDashboardSummary(): array
    {
        return [
            'total_unresolved' => self::getUnresolvedCount(),
            'critical' => self::getCriticalCount(),
            'high' => SecurityAlert::unresolved()->where('severity', LogSeverity::HIGH)->count(),
            'warning' => SecurityAlert::unresolved()->where('severity', LogSeverity::WARNING)->count(),
            'today' => SecurityAlert::whereDate('created_at', today())->count(),
            'recent' => SecurityAlert::unresolved()
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];
    }
}