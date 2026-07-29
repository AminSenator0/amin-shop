<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterSubscriberController extends Controller
{
    public function index(Request $request): View
    {
        $query = NewsletterSubscriber::query()->latest();

        if ($request->filled('search')) {
            $query->where('email', 'like', '%'.$request->search.'%');
        }

        $subscribers = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => NewsletterSubscriber::count(),
            'today' => NewsletterSubscriber::whereDate('created_at', today())->count(),
            'this_month' => NewsletterSubscriber::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return view('admin.newsletter.index', compact('subscribers', 'stats'));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = NewsletterSubscriber::query()->latest();

        if ($request->filled('search')) {
            $query->where('email', 'like', '%'.$request->search.'%');
        }

        $subscribers = $query->get();
        $filename = 'newsletter-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($subscribers) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['ایمیل', 'تاریخ عضویت']);

            foreach ($subscribers as $subscriber) {
                fputcsv($handle, [
                    $subscriber->email,
                    format_jalali($subscriber->created_at, 'Y/m/d H:i', false),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function destroy(NewsletterSubscriber $newsletterSubscriber): RedirectResponse
    {
        $newsletterSubscriber->delete();

        return redirect()->route('admin.newsletter.index')->with('success', 'عضو خبرنامه حذف شد.');
    }
}
