<?php

namespace App\Services;

use App\Enums\LogAction;
use App\Enums\LogSeverity;
use App\Models\AuditLog;
use App\Models\SecurityAlert;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SecurityAlertService
{
    /**
     * Rules: [threshold, window_minutes, severity, alert_type, message, auto_block_minutes]
     */
    private static array $rules = [
        // ═══════════════════════════════════════════════
        // AUTH & LOGIN
        // ═══════════════════════════════════════════════
        'login_failed' => [
            'threshold' => 5,           // ← از 10 به 5 تغییر کرد
            'window' => 1,              // ← از 5 به 1 دقیقه
            'severity' => LogSeverity::CRITICAL,
            'alert_type' => 'mass_login_failed',
            'message' => ':count تلاش ورود ناموفق از IP :ip در :window دقیقه',
            'auto_block' => 60,
        ],
        'admin_login_failed' => [
            'threshold' => 3,
            'window' => 5,
            'severity' => LogSeverity::CRITICAL,
            'alert_type' => 'admin_login_failed',
            'message' => ':count تلاش ناموفق ورود به پنل ادمین از IP :ip در :window دقیقه',
            'auto_block' => 60,
        ],
        'login_multi_account' => [
            'threshold' => 5,
            'window' => 1,
            'severity' => LogSeverity::HIGH,
            'alert_type' => 'login_multi_account',
            'message' => ':count تلاش لاگین برای حساب‌های مختلف از IP :ip در :window دقیقه',
            'auto_block' => 30,
        ],
        'suspicious_admin_login' => [
            'threshold' => 1,
            'window' => 0,
            'severity' => LogSeverity::CRITICAL,
            'alert_type' => 'suspicious_admin_login',
            'message' => 'ورود موفق ادمین :user از IP :ip بعد از :count تلاش ناموفق',
            'auto_block' => null,
        ],

        // ═══════════════════════════════════════════════
        // PASSWORD & ACCOUNT
        // ═══════════════════════════════════════════════
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
        'password_changed' => [
            'threshold' => 1,
            'window' => 0,
            'severity' => LogSeverity::HIGH,
            'alert_type' => 'password_changed',
            'message' => 'تغییر رمز عبور توسط :user از IP :ip',
            'auto_block' => null,
        ],
        'email_changed' => [
            'threshold' => 1,
            'window' => 0,
            'severity' => LogSeverity::HIGH,
            'alert_type' => 'email_changed',
            'message' => 'تغییر ایمیل/شماره توسط :user از IP :ip',
            'auto_block' => null,
        ],

        // ═══════════════════════════════════════════════
        // ADMIN ACCESS
        // ═══════════════════════════════════════════════
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

        // ═══════════════════════════════════════════════
        // SENSITIVE SETTINGS
        // ═══════════════════════════════════════════════
        'sensitive_settings_changed' => [
            'threshold' => 1,
            'window' => 0,
            'severity' => LogSeverity::CRITICAL,
            'alert_type' => 'sensitive_settings_changed',
            'message' => 'تغییر کلید/Secret حساس (:fields) توسط :admin از IP :ip',
            'auto_block' => null,
        ],

        // ═══════════════════════════════════════════════
        // FLOOD & ABUSE
        // ═══════════════════════════════════════════════
        'endpoint_flood' => [
            'threshold' => 100,
            'window' => 1,
            'severity' => LogSeverity::CRITICAL,
            'alert_type' => 'endpoint_flood',
            'message' => ':count درخواست به :endpoint از IP :ip در :window دقیقه',
            'auto_block' => 30,
        ],
        'rate_limit_bypass' => [
            'threshold' => 1,
            'window' => 0,
            'severity' => LogSeverity::CRITICAL,
            'alert_type' => 'rate_limit_bypass',
            'message' => 'عبور از Rate Limit توسط :user از IP :ip - URL: :url',
            'auto_block' => 60,
        ],
        'abnormal_http' => [
            'threshold' => 20,
            'window' => 5,
            'severity' => LogSeverity::HIGH,
            'alert_type' => 'abnormal_http_requests',
            'message' => ':count درخواست HTTP غیرعادی از IP :ip در :window دقیقه',
            'auto_block' => 15,
        ],
        'suspicious_url' => [
            'threshold' => 1,
            'window' => 0,
            'severity' => LogSeverity::HIGH,
            'alert_type' => 'suspicious_url',
            'message' => 'درخواست مشکوک به URL غیرمعمول :url از IP :ip توسط :user',
            'auto_block' => null,
        ],
        'sensitive_file_access' => [
            'threshold' => 1,
            'window' => 0,
            'severity' => LogSeverity::CRITICAL,
            'alert_type' => 'sensitive_file_access',
            'message' => 'تلاش دسترسی به فایل حساس :url از IP :ip توسط :user',
            'auto_block' => null,
        ],

        // ═══════════════════════════════════════════════
        // ERRORS & BLOCKED
        // ═══════════════════════════════════════════════
        'mass_403' => [
            'threshold' => 20,
            'window' => 5,
            'severity' => LogSeverity::HIGH,
            'alert_type' => 'mass_403_errors',
            'message' => ':count خطای 403 از IP :ip در :window دقیقه',
            'auto_block' => 15,
        ],
        'blocked_ip_activity' => [
            'threshold' => 1,
            'window' => 0,
            'severity' => LogSeverity::CRITICAL,
            'alert_type' => 'blocked_ip_activity',
            'message' => 'فعالیت مشکوک از IP مسدودشده :ip - URL: :url',
            'auto_block' => null,
        ],

        // ═══════════════════════════════════════════════
        // BUSINESS LOGIC
        // ═══════════════════════════════════════════════
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
    ];

    /**
     * بررسی Real-time پس از هر لاگ
     */
    public static function checkRealtime(LogAction $action, ?string $ip, ?int $userId, array $payload = []): void
    {
        if (!$ip) return;

        $actionKey = match($action) {
            LogAction::LOGIN_FAILED => 'login_failed',
            LogAction::ADMIN_LOGIN_FAILED => 'admin_login_failed',
            LogAction::FORGOT_PASSWORD_REQUESTED => 'forgot_password',
            LogAction::RESET_PASSWORD_SUCCESS => 'reset_password',
            LogAction::ADMIN_ACCESS_ATTEMPT => 'admin_access_attempt',
            LogAction::USER_HIT_ADMIN_ENDPOINTS => 'user_hit_admin_endpoints',
            LogAction::PAYMENT_CALLBACK_MISMATCH => 'payment_callback_mismatch',
            LogAction::ORDER_AMOUNT_TAMPERED => 'order_amount_tampered',
            LogAction::MASS_403_ERRORS => 'mass_403',
            LogAction::ABNORMAL_HTTP_REQUESTS => 'abnormal_http',
            LogAction::COUPON_REUSE_ATTEMPT => 'coupon_reuse',
            LogAction::PASSWORD_CHANGED => 'password_changed',
            LogAction::EMAIL_PHONE_CHANGED => 'email_changed',
            LogAction::SMTP_SETTINGS_CHANGED,
            LogAction::PAYMENT_SETTINGS_CHANGED => 'sensitive_settings_changed',
            LogAction::ENDPOINT_FLOOD => 'endpoint_flood',
            LogAction::RAPID_PRICE_CHANGES => 'rapid_price_changes',
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
            Cache::forget($cacheKey);
        }
    }

    /**
     * بررسی ورود موفق ادمین بعد از تلاش‌های ناموفق (Correlation)
     */
    public static function checkAdminLoginAfterFailures(int $userId, string $ip, string $userName): void
    {
        $recentFailures = AuditLog::where('ip_address', $ip)
            ->where('action', LogAction::ADMIN_LOGIN_FAILED)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count();

        if ($recentFailures >= 3) {
            $rule = self::$rules['suspicious_admin_login'];
            self::createAlert($rule, $ip, $userId, [
                'user_name' => $userName,
                'failed_count' => $recentFailures,
            ], $recentFailures);
        }
    }

    /**
     * بررسی تلاش لاگین برای چندین حساب مختلف
     */
    public static function checkMultiAccountLogin(string $ip, string $email): void
    {
        $emailsKey = "login_failed_emails:{$ip}";
        $emails = Cache::get($emailsKey, []);
        
        if (!in_array($email, $emails)) {
            $emails[] = $email;
            Cache::put($emailsKey, $emails, now()->addMinutes(1));
        }

        if (count($emails) >= 5) {
            $rule = self::$rules['login_multi_account'];
            self::createAlert($rule, $ip, null, [
                'unique_emails' => $emails,
                'count' => count($emails),
            ], count($emails));
            Cache::forget($emailsKey);
        }
    }

    /**
     * بررسی فعالیت از IP مسدود شده
     */
    public static function checkBlockedIpActivity(string $ip, ?int $userId, string $url): void
    {
        $rule = self::$rules['blocked_ip_activity'];
        self::createAlert($rule, $ip, $userId, ['url' => $url], 1);
    }

    /**
     * بررسی دسترسی به فایل/URL حساس
     */
    public static function checkSensitiveAccess(string $ip, ?int $userId, string $url, string $type = 'file'): void
    {
        $rule = $type === 'url' 
            ? self::$rules['suspicious_url'] 
            : self::$rules['sensitive_file_access'];
            
        self::createAlert($rule, $ip, $userId, ['url' => $url], 1);
    }

    /**
     * بررسی Rate Limit Bypass
     */
    public static function checkRateLimitBypass(string $ip, ?int $userId, string $url): void
    {
        $rule = self::$rules['rate_limit_bypass'];
        self::createAlert($rule, $ip, $userId, ['url' => $url], 1);
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
     * Endpoint Flood
     */
    private static function checkEndpointFloods(): void
    {
        $rule = self::$rules['endpoint_flood'];
        $since = now()->subMinutes($rule['window']);

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
            ->having('unique_users', '>=', 3)
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

        // Deduplication
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
            [':count', ':ip', ':window', ':endpoint', ':user', ':admin', ':order', ':url', ':fields'],
            [
                $count,
                $ip ?? 'unknown',
                $rule['window'] ?? 0,
                $payload['endpoint'] ?? 'unknown',
                $payload['user_name'] ?? ($userId ? "User#{$userId}" : 'unknown'),
                $payload['admin_name'] ?? ($userId ? "Admin#{$userId}" : 'unknown'),
                $payload['order_code'] ?? ($payload['order_id'] ?? 'unknown'),
                $payload['url'] ?? 'unknown',
                $payload['fields'] ?? 'unknown',
            ],
            $rule['message']
        );

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

        Log::warning("[SECURITY ALERT] {$message}", [
            'alert_id' => $alert->id,
            'type' => $alertType,
            'severity' => $rule['severity']->value,
            'ip' => $ip,
        ]);
    }

    /**
     * شمارش هشدارهای فعال
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