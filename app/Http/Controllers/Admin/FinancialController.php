<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FinancialReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialController extends Controller
{
    public function index(Request $request, FinancialReportService $financialReport)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $paymentStatus = $request->input('payment_status');

        $summary = $financialReport->summary($dateFrom, $dateTo);
        $paymentBreakdown = $financialReport->paymentBreakdown($dateFrom, $dateTo);
        $dailyChart = $financialReport->dailyChart($dateFrom, $dateTo);
        $monthlyChart = $financialReport->monthlyChart();

        $transactions = $financialReport
            ->transactionsQuery($dateFrom, $dateTo, $paymentStatus)
            ->paginate(15)
            ->withQueryString();

        return view('admin.financial.index', compact(
            'summary',
            'paymentBreakdown',
            'dailyChart',
            'monthlyChart',
            'transactions',
        ));
    }

    public function export(Request $request, FinancialReportService $financialReport): StreamedResponse
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $paymentStatus = $request->input('payment_status');

        $summary = $financialReport->summary($dateFrom, $dateTo);
        $orders = $financialReport
            ->transactionsQuery($dateFrom, $dateTo, $paymentStatus)
            ->get();

        $filename = 'financial-'.$summary['date_from'].'-'.$summary['date_to'].'.csv';

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['شماره سفارش', 'مشتری', 'جمع جزء', 'تخفیف', 'ارسال', 'مبلغ کل', 'وضعیت پرداخت', 'کد پیگیری', 'تاریخ پرداخت']);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->user?->name ?? '—',
                    $order->subtotal,
                    $order->discount_amount,
                    $order->shipping_cost,
                    $order->total,
                    $order->payment_status->label(),
                    $order->payment_ref ?: '—',
                    $order->paid_at ? format_jalali($order->paid_at, 'Y/m/d H:i', false) : '—',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
