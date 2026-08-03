<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LogAction;
use App\Enums\LogCategory;
use App\Enums\LogSeverity;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BlockedIp;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\SecurityAlertService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AuditLogController extends Controller
{
    public function dashboard()
    {
        $todayCount = AuditLog::today()->count();
        $weekCount = AuditLog::recent(7)->count();
        $criticalCount = AuditLog::today()->where('severity', LogSeverity::CRITICAL)->count();
        $alertCount = SecurityAlertService::getUnresolvedCount();

        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartData['labels'][] = verta($date)->format('l');
            $chartData['login_success'][] = AuditLog::whereDate('created_at', $date)
                ->where('action', LogAction::LOGIN_SUCCESS)->count();
            $chartData['login_failed'][] = AuditLog::whereDate('created_at', $date)
                ->where('action', LogAction::LOGIN_FAILED)->count();
            $chartData['security'][] = AuditLog::whereDate('created_at', $date)
                ->whereIn('severity', [LogSeverity::HIGH, LogSeverity::CRITICAL])->count();
            $chartData['payment_errors'][] = AuditLog::whereDate('created_at', $date)
                ->where('category', LogCategory::PAYMENT)
                ->where('severity', '!=', LogSeverity::INFO)->count();
            $chartData['admin_actions'][] = AuditLog::whereDate('created_at', $date)
                ->where('category', LogCategory::ADMIN)->count();
        }

        $categories = [];
        foreach (LogCategory::cases() as $cat) {
            $categories['labels'][] = $cat->label();
            $categories['data'][] = AuditLog::today()->where('category', $cat)->count();
        }

        $recentCritical = AuditLog::suspicious()->with('user')
            ->orderBy('created_at', 'desc')->limit(10)->get();

        return view('admin.audit-logs.dashboard', compact(
            'todayCount', 'weekCount', 'criticalCount', 'alertCount',
            'chartData', 'categories', 'recentCritical'
        ));
    }

    public function index(Request $request)
    {
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');
        $this->applyFilters($query, $request);

        $logs = $query->paginate(50)->withQueryString();
        $categories = LogCategory::cases();
        $severities = LogSeverity::cases();
        $actions = LogAction::cases();

        return view('admin.audit-logs.index', compact(
            'logs', 'categories', 'severities', 'actions'
        ));
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');
        $this->applyFilters($query, $request);
        $logs = $query->limit(10000)->get();

        return match($format) {
            'csv' => $this->exportCsv($logs),
            'json' => $this->exportJson($logs),
            'pdf' => $this->exportPdf($logs),
            default => $this->exportCsv($logs),
        };
    }

    public function ipDetail(Request $request, $ip)
    {
        $stats = [
            'total_requests' => AuditLog::forIp($ip)->count(),
            'login_failed' => AuditLog::forIp($ip)->where('action', LogAction::LOGIN_FAILED)->count(),
            'login_success' => AuditLog::forIp($ip)->where('action', LogAction::LOGIN_SUCCESS)->count(),
            'errors_403' => AuditLog::forIp($ip)->where('action', LogAction::MASS_403_ERRORS)->orWhere('action', LogAction::ADMIN_ACCESS_ATTEMPT)->count(),
        ];

        $recentLogs = AuditLog::forIp($ip)->with('user')
            ->orderBy('created_at', 'desc')->limit(50)->get();
        $isBlocked = BlockedIp::active()->where('ip_address', $ip)->exists();

        return view('admin.audit-logs.ip-detail', compact('ip', 'stats', 'recentLogs', 'isBlocked'));
    }

    public function userActivity(Request $request, User $user)
    {
        $logs = AuditLog::forUser($user->id)->orderBy('created_at', 'desc')->paginate(50);
        $summary = [
            'total' => AuditLog::forUser($user->id)->count(),
            'login_count' => AuditLog::forUser($user->id)->where('action', LogAction::LOGIN_SUCCESS)->count(),
            'order_count' => AuditLog::forUser($user->id)->where('category', LogCategory::ORDER)->count(),
        ];

        return view('admin.audit-logs.user-activity', compact('user', 'logs', 'summary'));
    }

    public function adminActivity(Request $request, User $admin)
    {
        if (!$admin->isAdmin()) abort(404);

        $todayLogs = AuditLog::forUser($admin->id)->today()
            ->where('category', LogCategory::ADMIN)->get();

        $summary = [];
        foreach ($todayLogs as $log) {
            $action = $log->action->label();
            $summary[$action] = ($summary[$action] ?? 0) + 1;
        }

        $recentLogs = AuditLog::forUser($admin->id)
            ->orderBy('created_at', 'desc')->limit(50)->get();

        return view('admin.audit-logs.admin-activity', compact('admin', 'summary', 'recentLogs'));
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('user_search')) {
            $search = $request->user_search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }
        if ($request->filled('ip')) {
            $query->where('ip_address', 'like', "%{$request->ip}%");
        }
        if ($request->filled('date_range')) {
            match($request->date_range) {
                'today' => $query->whereDate('created_at', today()),
                'yesterday' => $query->whereDate('created_at', today()->subDay()),
                'week' => $query->where('created_at', '>=', now()->subDays(7)),
                'month' => $query->where('created_at', '>=', now()->subDays(30)),
                'custom' => $query->when($request->from && $request->to, function ($q) use ($request) {
                    $q->whereBetween('created_at', [$request->from . ' 00:00:00', $request->to . ' 23:59:59']);
                }),
                default => null,
            };
        }
    }

    private function exportCsv($logs)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="audit-logs-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['ID', 'Date', 'Severity', 'Category', 'Action', 'User', 'IP', 'Description']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    verta($log->created_at)->format('Y/m/d H:i:s'),
                    $log->severity->label(),
                    $log->category->label(),
                    $log->action->label(),
                    $log->user?->name ?? 'Guest',
                    $log->ip_address,
                    $log->description,
                ]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function exportJson($logs)
    {
        $data = $logs->map(fn($log) => [
            'id' => $log->id,
            'date' => $log->created_at->toDateTimeString(),
            'severity' => $log->severity->value,
            'category' => $log->category->value,
            'action' => $log->action->value,
            'user' => $log->user?->name,
            'ip' => $log->ip_address,
            'description' => $log->description,
        ]);

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="audit-logs-' . now()->format('Y-m-d') . '.json"'
        ]);
    }

    private function exportPdf($logs)
    {
        $pdf = Pdf::loadView('admin.audit-logs.export-pdf', ['logs' => $logs]);
        return $pdf->download('audit-logs-' . now()->format('Y-m-d') . '.pdf');
    }
}