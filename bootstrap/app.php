<?php

use App\Http\Middleware\AuditLogMiddleware;
use App\Http\Middleware\SuspiciousActivityMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\ShareStoreSettings::class,
            \App\Http\Middleware\NormalizePersianInput::class,
            \App\Http\Middleware\StoreMaintenanceMiddleware::class,
            \App\Http\Middleware\TrackSiteVisit::class,
            \App\Http\Middleware\PostAuthAuditMiddleware::class, // ← اضافه شد
            AuditLogMiddleware::class,
            SuspiciousActivityMiddleware::class,
            
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }
            return route('login');
        });

        $middleware->redirectUsersTo(function ($request) {
            $user = $request->user();
            if ($request->is('admin/login') && $user?->isAdmin()) {
                return route('admin.dashboard');
            }
            if ($user?->isAdmin()) {
                return route('admin.dashboard');
            }
            return route('user.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (Throwable $e) {
            try {
                \App\Services\AuditLogService::log(
                    \App\Enums\LogAction::EXCEPTION_THROWN,
                    auth()->id(),
                    [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'class' => get_class($e),
                    ],
                    severity: \App\Enums\LogSeverity::CRITICAL
                );
            } catch (\Throwable) {
                // Silent fail
            }
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'نشست شما منقضی شده است. لطفاً صفحه را رفرش کنید.',
                ], 419);
            }

            $redirectTo = $request->is('logout') ? route('home') : url()->previous();

            return redirect($redirectTo)
                ->with('error', 'نشست شما منقضی شده است. لطفاً صفحه را رفرش کنید و دوباره تلاش کنید.');
        });
    })->create();