<?php

namespace App\Http\Middleware;

use App\Enums\LogAction;
use App\Enums\LogSeverity;
use App\Models\AuditLog;
use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (AuditLogService::isIpBlocked()) {
            abort(403, 'Access denied.');
        }

        $path = $request->path();
        $method = $request->method();

        // Capture logout user BEFORE processing
        $isLogout = $path === 'logout' && $method === 'POST';
        $logoutUserId = $isLogout ? Auth::id() : null;
        $logoutUserEmail = $isLogout ? (Auth::user()?->email ?? 'unknown') : null;

        $response = $next($request);

        // ═══════════════════════════════════════════════
        // LOGIN / LOGOUT
        // ═══════════════════════════════════════════════
        if (($path === 'login' || $path === 'admin/login') && $method === 'POST') {
            $this->handleLogin($request, $response);
        }

        if ($isLogout && $logoutUserId) {
            $this->logLogout($logoutUserId, $logoutUserEmail, $request);
        }

        // ═══════════════════════════════════════════════
        // PASSWORD CHANGE (when logged in)
        // ═══════════════════════════════════════════════
        if ($method === 'PUT' && str_contains($path, 'password') && Auth::check()) {
            if ($response->isRedirect() || $response->isSuccessful()) {
                $this->logPasswordChanged(Auth::user(), $request);
            }
        }

        // ═══════════════════════════════════════════════
        // FORGOT PASSWORD REQUEST
        // ═══════════════════════════════════════════════
        if ($method === 'POST' && str_contains($path, 'forgot-password')) {
            $email = $request->input('email', 'unknown');
            AuditLogService::log(
                LogAction::FORGOT_PASSWORD_REQUESTED,
                null,
                ['email' => $email],
                [],
                [],
                "درخواست Forgot Password — ایمیل: {$email} — IP: " . $request->ip(),
                severity: LogSeverity::WARNING
            );
        }


        // ═══════════════════════════════════════════════
        // CHECKOUT / PAYMENT ROUTES
        // ═══════════════════════════════════════════════
        if ($path === 'checkout' && $method === 'POST') {
            // Order created (will also be caught by OrderObserver)
            // This is just a backup
        }

        if (str_starts_with($path, 'checkout/payment/') && $method === 'POST') {
            $orderId = basename($path);
            AuditLogService::log(
                LogAction::PAYMENT_STARTED,
                Auth::id(),
                ['order_id' => $orderId, 'gateway' => 'zarinpal'],
                severity: LogSeverity::INFO
            );
        }

        if ($path === 'payment/callback') {
            $authority = $request->get('Authority');
            $status = $request->get('Status');
            $orderId = $request->get('order_id'); // یا از session بخون
            
            // چک کردن mismatch مبلغ (اگه order_id داریم)
            if ($orderId) {
                $order = \App\Models\Order::find($orderId);
                $callbackAmount = $request->get('amount'); // مبلغ callback
                
                if ($order && $callbackAmount && $callbackAmount != $order->total) {
                    AuditLogService::log(
                        LogAction::PAYMENT_CALLBACK_MISMATCH,
                        Auth::id(),
                        [
                            'order_id' => $orderId,
                            'order_amount' => $order->total,
                            'callback_amount' => $callbackAmount,
                            'authority' => $authority,
                        ],
                        severity: LogSeverity::CRITICAL
                    );
                }
            }

            if ($status === 'OK') {
                AuditLogService::log(
                    LogAction::PAYMENT_CALLBACK,
                    Auth::id(),
                    ['authority' => $authority, 'status' => 'success'],
                    severity: LogSeverity::INFO
                );
            } else {
                AuditLogService::log(
                    LogAction::PAYMENT_CALLBACK,
                    Auth::id(),
                    ['authority' => $authority, 'status' => 'failed'],
                    severity: LogSeverity::WARNING
                );
            }
        }

        // ═══════════════════════════════════════════════
        // ADMIN ORDER ACTIONS
        // ═══════════════════════════════════════════════
        if (str_starts_with($path, 'admin/orders/') && $method === 'PATCH') {
            if (str_contains($path, '/status')) {
                $orderId = explode('/', $path)[2] ?? null;
                AuditLogService::log(
                    LogAction::ORDER_STATUS_CHANGED,
                    Auth::id(),
                    ['order_id' => $orderId, 'new_status' => $request->input('status')],
                    severity: LogSeverity::INFO
                );
            }
            if (str_contains($path, '/payment-status')) {
                $orderId = explode('/', $path)[2] ?? null;
                AuditLogService::log(
                    LogAction::PAYMENT_STATUS_MANUAL,
                    Auth::id(),
                    ['order_id' => $orderId, 'new_payment_status' => $request->input('payment_status')],
                    severity: LogSeverity::HIGH
                );
            }
        }

        if (str_starts_with($path, 'admin/orders/') && str_contains($path, '/cancel') && $method === 'POST') {
            $orderId = explode('/', $path)[2] ?? null;
            AuditLogService::log(
                LogAction::ORDER_CANCELLED,
                Auth::id(),
                ['order_id' => $orderId],
                severity: LogSeverity::HIGH
            );
        }


        // ═══════════════════════════════════════════════
        // RESET PASSWORD
        // ═══════════════════════════════════════════════
        if ($method === 'POST' && str_contains($path, 'reset-password')) {
            $email = $request->input('email', 'unknown');
            if ($response->isRedirect() || $response->isSuccessful()) {
                AuditLogService::log(
                    LogAction::RESET_PASSWORD_SUCCESS,
                    null,
                    ['email' => $email],
                    [],
                    [],
                    "استفاده موفق Reset Password — ایمیل: {$email} — IP: " . $request->ip()
                );
            }
        }

        // ═══════════════════════════════════════════════
        // STORE SETTINGS UPDATE
        // ═══════════════════════════════════════════════
        if ($method === 'PUT' && $path === 'admin/settings' && Auth::check()) {
            if ($response->isRedirect() || $response->isSuccessful()) {
                $this->logSettingsUpdate($request);
            }
        }

        // ═══════════════════════════════════════════════
        // LOGIN SUCCESS (via PostAuthAuditMiddleware)
        // ═══════════════════════════════════════════════
        if (Auth::check() && session()->has('audit_login_pending')) {
            $loginData = session()->pull('audit_login_pending');
            $this->logLoginSuccessFromSession($loginData);
        }

        if ($response->getStatusCode() >= 400) {
            $this->logErrorResponse($request, $response);
        }

        return $response;
    }

    private function handleLogin(Request $request, Response $response): void
    {
        $email = $request->input('email', 'unknown');
        $ip = $request->ip();
        $location = $response->headers->get('Location', '');
        $isFailed = str_contains($location, 'login');

        if (!$isFailed && $response->isRedirect()) {
            session(['audit_login_pending' => [
                'email' => $email,
                'ip' => $ip,
            ]]);
        } else {
            $this->logLoginFailed($email, $ip);
        }
    }

    private function logLoginSuccessFromSession(array $data): void
    {
        try {
            $user = Auth::user();
            if (!$user) return;
            
            $isAdmin = method_exists($user, 'isAdmin') && $user->isAdmin();
            $action = $isAdmin ? LogAction::ADMIN_LOGIN_SUCCESS : LogAction::LOGIN_SUCCESS;

            AuditLog::create([
                'user_id' => $user->id,
                'action' => $action,
                'category' => $action->category(),
                'severity' => $action->defaultSeverity(),
                'ip_address' => $data['ip'] ?? request()->ip(),
                'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
                'device_fingerprint' => hash('sha256', request()->userAgent() . request()->ip()),
                'url' => substr(request()->fullUrl(), 0, 500),
                'method' => 'POST',
                'payload' => ['email' => $data['email'] ?? $user->email],
                'description' => "ورود موفق از IP: " . ($data['ip'] ?? request()->ip()),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('[AuditLog] logLoginSuccess failed: ' . $e->getMessage());
        }
    }

    private function logLoginFailed(string $email, string $ip): void
    {
        try {
            AuditLog::create([
                'user_id' => null,
                'action' => LogAction::LOGIN_FAILED,
                'category' => LogAction::LOGIN_FAILED->category(),
                'severity' => LogSeverity::WARNING,
                'ip_address' => $ip,
                'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
                'device_fingerprint' => hash('sha256', request()->userAgent() . $ip),
                'url' => substr(request()->fullUrl(), 0, 500),
                'method' => 'POST',
                'payload' => ['email' => $email],
                'description' => "ورود ناموفق — ایمیل: {$email} — IP: {$ip}",
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('[AuditLog] logLoginFailed failed: ' . $e->getMessage());
        }
    }

    private function logLogout(int $userId, string $email, Request $request): void
    {
        try {
            AuditLog::create([
                'user_id' => $userId,
                'action' => LogAction::LOGOUT,
                'category' => LogAction::LOGOUT->category(),
                'severity' => LogAction::LOGOUT->defaultSeverity(),
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                'device_fingerprint' => hash('sha256', $request->userAgent() . $request->ip()),
                'url' => substr($request->fullUrl(), 0, 500),
                'method' => 'POST',
                'payload' => ['email' => $email],
                'description' => "خروج از حساب",
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('[AuditLog] logLogout failed: ' . $e->getMessage());
        }
    }

    private function logPasswordChanged($user, Request $request): void
    {
        try {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => LogAction::PASSWORD_CHANGED,
                'category' => LogAction::PASSWORD_CHANGED->category(),
                'severity' => LogAction::PASSWORD_CHANGED->defaultSeverity(),
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                'device_fingerprint' => hash('sha256', $request->userAgent() . $request->ip()),
                'url' => substr($request->fullUrl(), 0, 500),
                'method' => 'PUT',
                'payload' => ['email' => $user->email],
                'description' => "تغییر رمز عبور توسط کاربر {$user->name}",
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('[AuditLog] logPasswordChanged failed: ' . $e->getMessage());
        }
    }

    /**
     * لاگ‌نویسی تغییرات تنظیمات سایت/پرداخت/SMTP
     */
    /**
     * لاگ‌نویسی تغییرات تنظیمات — فقط فیلدهای واقعاً تغییرکرده
     */
    private function logSettingsUpdate(Request $request): void
    {
        try {
            $user = Auth::user();
            if (!$user) return;

            $all = $request->all();

            // ═══════════════════════════════════════════════
            // خواندن مقادیر فعلی از دیتابیس برای مقایسه
            // ═══════════════════════════════════════════════
            $currentSettings = [];
            try {
                if (\Schema::hasTable('store_settings')) {
                    $currentSettings = \App\Models\StoreSetting::pluck('value', 'key')->toArray();
                }
            } catch (\Throwable) {
                // اگه table نبود، skip comparison
            }

            $siteFields = ['site_name', 'site_title', 'logo', 'favicon', 'description', 'address', 'phone', 'footer_text', 'currency', 'tax_rate'];
            $paymentFields = ['zarinpal_merchant', 'payment_gateway', 'merchant_id', 'payment_callback_url', 'zarinpal_sandbox'];
            $smtpFields = ['mail_driver', 'smtp_host', 'smtp_port', 'mail_username', 'mail_password', 'mail_from_address', 'mail_from_name', 'mail_encryption'];

            // ═══════════════════════════════════════════════
            // فقط فیلدهایی که واقعاً تغییر کردن (مقدار جدید != مقدار قبلی)
            // ═══════════════════════════════════════════════
            $changedSite = [];
            $changedPayment = [];
            $changedSmtp = [];

            foreach ($siteFields as $field) {
                if (isset($all[$field]) && $all[$field] != ($currentSettings[$field] ?? null)) {
                    $changedSite[] = $field;
                }
            }
            foreach ($paymentFields as $field) {
                if (isset($all[$field]) && $all[$field] != ($currentSettings[$field] ?? null)) {
                    $changedPayment[] = $field;
                }
            }
            foreach ($smtpFields as $field) {
                if (isset($all[$field]) && $all[$field] != ($currentSettings[$field] ?? null)) {
                    $changedSmtp[] = $field;
                }
            }

            // ═══════════════════════════════════════════════
            // اگه هیچ تغییری تشخیص ندادیم، fallback: فیلدهای non-empty
            // ═══════════════════════════════════════════════
            if (empty($changedSite) && empty($changedPayment) && empty($changedSmtp)) {
                foreach ($siteFields as $field) {
                    if (isset($all[$field]) && $all[$field] !== '' && $all[$field] !== null) {
                        $changedSite[] = $field;
                    }
                }
                foreach ($paymentFields as $field) {
                    if (isset($all[$field]) && $all[$field] !== '' && $all[$field] !== null) {
                        $changedPayment[] = $field;
                    }
                }
                foreach ($smtpFields as $field) {
                    if (isset($all[$field]) && $all[$field] !== '' && $all[$field] !== null) {
                        $changedSmtp[] = $field;
                    }
                }
            }

            // ═══════════════════════════════════════════════
            // Site Settings
            // ═══════════════════════════════════════════════
            if (!empty($changedSite)) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => LogAction::SITE_SETTINGS_CHANGED,
                    'category' => LogAction::SITE_SETTINGS_CHANGED->category(),
                    'severity' => LogAction::SITE_SETTINGS_CHANGED->defaultSeverity(),
                    'ip_address' => $request->ip(),
                    'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                    'device_fingerprint' => hash('sha256', $request->userAgent() . $request->ip()),
                    'url' => substr($request->fullUrl(), 0, 500),
                    'method' => 'PUT',
                    'payload' => ['fields' => $changedSite],
                    'description' => "تغییر تنظیمات سایت توسط {$user->name}",
                    'created_at' => now(),
                ]);
            }

            // ═══════════════════════════════════════════════
            // Payment Settings
            // ═══════════════════════════════════════════════
            if (!empty($changedPayment)) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => LogAction::PAYMENT_SETTINGS_CHANGED,
                    'category' => LogAction::PAYMENT_SETTINGS_CHANGED->category(),
                    'severity' => LogAction::PAYMENT_SETTINGS_CHANGED->defaultSeverity(),
                    'ip_address' => $request->ip(),
                    'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                    'device_fingerprint' => hash('sha256', $request->userAgent() . $request->ip()),
                    'url' => substr($request->fullUrl(), 0, 500),
                    'method' => 'PUT',
                    'payload' => ['fields' => $changedPayment],
                    'description' => "تغییر تنظیمات پرداخت توسط {$user->name}",
                    'created_at' => now(),
                ]);
            }

            // ═══════════════════════════════════════════════
            // SMTP Settings
            // ═══════════════════════════════════════════════
            if (!empty($changedSmtp)) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => LogAction::SMTP_SETTINGS_CHANGED,
                    'category' => LogAction::SMTP_SETTINGS_CHANGED->category(),
                    'severity' => LogAction::SMTP_SETTINGS_CHANGED->defaultSeverity(),
                    'ip_address' => $request->ip(),
                    'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                    'device_fingerprint' => hash('sha256', $request->userAgent() . $request->ip()),
                    'url' => substr($request->fullUrl(), 0, 500),
                    'method' => 'PUT',
                    'payload' => ['fields' => $changedSmtp],
                    'description' => "تغییر تنظیمات SMTP توسط {$user->name}",
                    'created_at' => now(),
                ]);
            }

        } catch (\Throwable $e) {
            \Log::error('[AuditLog] logSettingsUpdate failed: ' . $e->getMessage());
        }
    }

    private function logErrorResponse(Request $request, Response $response): void
    {
        try {
            $action = match($response->getStatusCode()) {
                403 => LogAction::MASS_403_ERRORS,
                404 => LogAction::ABNORMAL_HTTP_REQUESTS,
                500, 502, 503 => LogAction::EXCEPTION_THROWN,
                default => LogAction::ABNORMAL_HTTP_REQUESTS,
            };

            AuditLogService::log(
                $action,
                Auth::id(),
                [
                    'status_code' => $response->getStatusCode(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                ],
                severity: $response->getStatusCode() >= 500 ? LogSeverity::CRITICAL : LogSeverity::WARNING
            );
        } catch (\Throwable $e) {
            \Log::error('[AuditLogMiddleware] logErrorResponse failed: ' . $e->getMessage());
        }
    }
}