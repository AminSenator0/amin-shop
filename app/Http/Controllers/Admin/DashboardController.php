<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboard)
    {
        $salesDays = (int) $request->input('sales_days', 7);
        $visitorsDays = (int) $request->input('visitors_days', 30);

        $data = $dashboard->data($salesDays, $visitorsDays);

        return view('admin.dashboard', $data);
    }

    public function export(Request $request, DashboardService $dashboard): StreamedResponse
    {
        $from = parse_jalali($request->input('date_from')) ?? today()->subDays(29)->startOfDay();
        $to = parse_jalali($request->input('date_to')) ?? today()->endOfDay();
    
        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }
    
        $orders = $dashboard->exportOrders($from, $to);
    
        $filename = 'dashboard-orders-'.$from->format('Y-m-d').'-'.$to->format('Y-m-d').'.csv';
    
        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['شماره سفارش', 'مشتری', 'مبلغ', 'وضعیت سفارش', 'وضعیت پرداخت', 'تاریخ']);
    
            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->user?->name ?? '—',
                    $order->total,
                    $order->status->label(),
                    $order->payment_status->label(),
                    format_jalali($order->created_at, 'Y/m/d H:i', false),
                ]);
            }
    
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}