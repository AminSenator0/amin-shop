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
        $path = $request->path();
        $location = $response->headers->get('Location', '');
        $isFailed = str_contains($location, 'login');
        $isAdmin = $path === 'admin/login';

        if (!$isFailed && $response->isRedirect()) {
            session(['audit_login_pending' => [
                'email' => $email,
                'ip' => $ip,
                'is_admin' => $isAdmin,
            ]]);
        } else {
            $this->logLoginFailed($email, $ip, $isAdmin);
        }
    }

    private function logLoginSuccessFromSession(array $data): void
    {
        try {
            $user = Auth::user();
            if (!$user) return;
            
            $isAdmin = $data['is_admin'] ?? (method_exists($user, 'isAdmin') && $user->isAdmin());
            $action = $isAdmin ? LogAction::ADMIN_LOGIN_SUCCESS : LogAction::LOGIN_SUCCESS;

            AuditLogService::log(
                $action,
                $user->id,
                ['email' => $data['email'] ?? $user->email],
                severity: $action->defaultSeverity()
            );
        } catch (\Throwable $e) {
            \Log::error('[AuditLog] logLoginSuccess failed: ' . $e->getMessage());
        }
    }

    private function logLoginFailed(string $email, string $ip, bool $isAdmin = false): void
    {
        try {
            $action = $isAdmin ? LogAction::ADMIN_LOGIN_FAILED : LogAction::LOGIN_FAILED;

            AuditLogService::log(
                $action,
                null,
                ['email' => $email],
                severity: $action->defaultSeverity()
            );

            // تلاش لاگین برای چندین حساب مختلف از یک IP
            SecurityAlertService::checkMultiAccountLogin($ip, $email);
        } catch (\Throwable $e) {
            \Log::error('[AuditLog] logLoginFailed failed: ' . $e->getMessage());
        }
    }

    private function logLogout(int $userId, string $email, Request $request): void
    {
        try {
            AuditLogService::log(
                LogAction::LOGOUT,
                $userId,
                ['email' => $email],
                severity: LogSeverity::INFO
            );
        } catch (\Throwable $e) {
            \Log::error('[AuditLog] logLogout failed: ' . $e->getMessage());
        }
    }

    private function logPasswordChanged($user, Request $request): void
    {
        try {
            AuditLogService::log(
                LogAction::PASSWORD_CHANGED,
                $user->id,
                ['email' => $user->email],
                severity: LogSeverity::HIGH
            );
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
                AuditLogService::log(
                    LogAction::SITE_SETTINGS_CHANGED,
                    $user->id,
                    ['fields' => $changedSite],
                    severity: LogSeverity::HIGH
                );
            }

            // ═══════════════════════════════════════════════
            // Payment Settings
            // ═══════════════════════════════════════════════
            if (!empty($changedPayment)) {
                AuditLogService::log(
                    LogAction::PAYMENT_SETTINGS_CHANGED,
                    $user->id,
                    ['fields' => $changedPayment],
                    severity: LogSeverity::CRITICAL
                );
            }

            // ═══════════════════════════════════════════════
            // SMTP Settings
            // ═══════════════════════════════════════════════
            if (!empty($changedSmtp)) {
                AuditLogService::log(
                    LogAction::SMTP_SETTINGS_CHANGED,
                    $user->id,
                    ['fields' => $changedSmtp],
                    severity: LogSeverity::CRITICAL
                );
            }

        } catch (\Throwable $e) {
            \Log::error('[AuditLog] logSettingsUpdate failed: ' . $e->getMessage());
        }
    }

// app/Http/Controllers/Admin/AuditLogController.php

public function show(AuditLog $auditLog)
{
    $auditLog->load('user');
    
    return response()->json([
        'id' => $auditLog->id,
        'severity' => [
            'value' => $auditLog->severity->value,
            'label' => $auditLog->severity->label(),
            'badge_class' => $auditLog->severity->badgeClass(),
        ],
        'category' => [
            'value' => $auditLog->category->value,
            'label' => $auditLog->category->label(),
            'icon' => $auditLog->category->icon(),
        ],
        'action' => [
            'value' => $auditLog->action->value,
            'label' => $auditLog->action->label(),
        ],
        'user' => $auditLog->user ? [
            'id' => $auditLog->user->id,
            'name' => $auditLog->user->name,
            'email' => $auditLog->user->email,
            'avatar' => $auditLog->user->avatar ?? null,
            'is_admin' => $auditLog->user->isAdmin(),
            'link' => route('admin.audit-logs.user-activity', $auditLog->user),
        ] : null,
        'ip_address' => $auditLog->ip_address,
        'masked_ip' => $auditLog->masked_ip,
        'user_agent' => $auditLog->user_agent,
        'browser' => $this->parseBrowser($auditLog->user_agent),
        'device_fingerprint' => $auditLog->device_fingerprint,
        'url' => $auditLog->url,
        'method' => $auditLog->method,
        'payload' => $auditLog->payload,
        'old_values' => $auditLog->old_values,
        'new_values' => $auditLog->new_values,
        'description' => $auditLog->description,
        'reference' => $auditLog->reference_type && $auditLog->reference_id ? [
            'type' => $auditLog->reference_type,
            'id' => $auditLog->reference_id,
            'label' => class_basename($auditLog->reference_type) . ' #' . $auditLog->reference_id,
        ] : null,
        'session_id' => $auditLog->session_id ? substr($auditLog->session_id, 0, 16) . '...' : null,
        'created_at' => verta($auditLog->created_at)->format('Y/m/d H:i:s'),
        'created_at_diff' => $auditLog->created_at->diffForHumans(),
    ]);
}

private function parseBrowser(?string $userAgent): array
{
    if (!$userAgent) return ['name' => 'Unknown', 'os' => 'Unknown', 'device' => 'Unknown'];
    
    $browser = 'Unknown';
    $os = 'Unknown';
    
    if (str_contains($userAgent, 'Chrome')) $browser = 'Chrome';
    elseif (str_contains($userAgent, 'Firefox')) $browser = 'Firefox';
    elseif (str_contains($userAgent, 'Safari')) $browser = 'Safari';
    elseif (str_contains($userAgent, 'Edge')) $browser = 'Edge';
    
    if (str_contains($userAgent, 'Windows')) $os = 'Windows';
    elseif (str_contains($userAgent, 'Mac')) $os = 'macOS';
    elseif (str_contains($userAgent, 'Linux')) $os = 'Linux';
    elseif (str_contains($userAgent, 'Android')) $os = 'Android';
    elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) $os = 'iOS';
    
    return [
        'name' => $browser,
        'os' => $os,
        'device' => str_contains($userAgent, 'Mobile') ? 'Mobile' : 'Desktop',
        'raw' => substr($userAgent, 0, 120) . (strlen($userAgent) > 120 ? '...' : ''),
    ];
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