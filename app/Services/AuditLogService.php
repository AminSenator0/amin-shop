<?php

namespace App\Services;

use App\Enums\LogAction;
use App\Enums\LogCategory;
use App\Enums\LogSeverity;
use App\Jobs\ProcessAuditLogBatch;
use App\Models\AuditLog;
use App\Models\BlockedIp;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    private static array $buffer = [];
    private static int $bufferSize = 50;
    private static int $bufferTimeout = 5;

    /**
     * لاگ‌نویسی اصلی — همیشه از این متد استفاده کن
     */
    public static function log(
        LogAction $action,
        ?int $userId = null,
        array $payload = [],
        array $oldValues = [],
        array $newValues = [],
        ?string $description = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?LogSeverity $severity = null,
    ): void {
        try {
            $severity ??= $action->defaultSeverity();
            $category = $action->category();

            $data = [
                'user_id' => $userId,
                'action' => $action,
                'category' => $category,
                'severity' => $severity,
                'ip_address' => Request::ip(),
                'user_agent' => substr(Request::userAgent() ?? '', 0, 500),
                'device_fingerprint' => self::generateDeviceFingerprint(),
                'url' => substr(Request::fullUrl(), 0, 500),
                'method' => Request::method(),
                'payload' => self::sanitizePayload($payload),
                'old_values' => self::sanitizePayload($oldValues),
                'new_values' => self::sanitizePayload($newValues),
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'session_id' => substr(session()->getId(), 0, 128),
                'created_at' => now(),
            ];

            self::$buffer[] = $data;

            if (count(self::$buffer) >= self::$bufferSize) {
                self::flush();
            } else {
                self::scheduleFlush();
            }

            if ($severity === LogSeverity::CRITICAL) {
                self::flush();
                self::triggerSecurityAlert($data);
            }

        } catch (\Throwable $e) {
            Log::error('AuditLogService failed: ' . $e->getMessage());
        }
    }

    public static function flush(): void
    {
        if (empty(self::$buffer)) return;

        $batch = self::$buffer;
        self::$buffer = [];

        if (config('queue.default') !== 'sync') {
            ProcessAuditLogBatch::dispatch($batch);
        } else {
            self::insertBatch($batch);
        }
    }

    public static function insertBatch(array $batch): void
    {
        if (empty($batch)) return;

        try {
            AuditLog::insert($batch);
        } catch (\Throwable $e) {
            Log::error('AuditLog batch insert failed: ' . $e->getMessage());
            foreach ($batch as $record) {
                try {
                    AuditLog::create($record);
                } catch (\Throwable $e2) {
                    Log::error('AuditLog single insert failed: ' . $e2->getMessage());
                }
            }
        }
    }

    public static function isIpBlocked(?string $ip = null): bool
    {
        if (!$ip) $ip = Request::ip();
        if (!$ip) return false;

        return Cache::remember('blocked_ip:' . $ip, 60, function () use ($ip) {
            return BlockedIp::active()->where('ip_address', $ip)->exists();
        });
    }

    public static function blockIp(
        string $ip,
        string $reason,
        ?int $adminId = null,
        ?\DateTime $until = null
    ): void {
        BlockedIp::updateOrCreate(
            ['ip_address' => $ip],
            [
                'reason' => $reason,
                'blocked_by' => $adminId,
                'blocked_until' => $until,
                'failed_attempts' => \DB::raw('failed_attempts + 1'),
            ]
        );

        Cache::forget('blocked_ip:' . $ip);
        Cache::put('blocked_ip:' . $ip, true, 60);
    }

    private static function sanitizePayload(array $data): array
    {
        $sensitiveKeys = [
            'password', 'password_confirmation', 'token', 'api_token',
            'credit_card', 'card_number', 'cvv', 'secret', 'auth_code',
            'session_id', 'remember_token', 'private_key', 'merchant_id',
            'zarinpal_merchant', 'smtp_password', 'mail_password', 'api_key',
        ];

        array_walk_recursive($data, function (&$value, $key) use ($sensitiveKeys) {
            if (is_string($key)) {
                $lowerKey = strtolower($key);
                foreach ($sensitiveKeys as $sensitive) {
                    if (str_contains($lowerKey, $sensitive)) {
                        $value = '***REDACTED***';
                        return;
                    }
                }
            }

            if (is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $parts = explode('@', $value);
                $value = substr($parts[0], 0, 2) . '***@' . $parts[1];
            }

            if (is_string($value) && preg_match('/^09\d{9}$/', $value)) {
                $value = substr($value, 0, 4) . '****' . substr($value, -3);
            }
        });

        return $data;
    }

    private static function generateDeviceFingerprint(): string
    {
        $data = Request::userAgent() . Request::ip();
        return hash('sha256', $data);
    }

    /**
     * هشدار امنیتی خودکار — فقط یه بار تعریف شده
     */
    private static function triggerSecurityAlert(array $data): void
    {
        try {
            SecurityAlertService::checkRealtime(
                $data['action'],
                $data['ip_address'] ?? null,
                $data['user_id'] ?? null,
                $data['payload'] ?? []
            );
        } catch (\Throwable $e) {
            Log::error('SecurityAlertService failed: ' . $e->getMessage());
        }
    }

    private static function scheduleFlush(): void
    {
        static $scheduled = false;
        if ($scheduled) return;

        $scheduled = true;

        register_shutdown_function(function () {
            self::flush();
        });
    }

    public static function purgeOldLogs(int $days = 90): int
    {
        return AuditLog::where('created_at', '<', now()->subDays($days))->delete();
    }
}