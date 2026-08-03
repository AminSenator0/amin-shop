@extends('layouts.admin')

@section('title', 'جزئیات IP: ' . $ip)

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ url()->previous() }}" class="text-slate-400 hover:text-slate-600">←</a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">🌐 جزئیات IP</h1>
                <p class="font-mono text-lg text-indigo-600 mt-1 direction-ltr">{{ $ip }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($isBlocked)
                <span class="bg-rose-100 text-rose-700 px-3 py-1.5 rounded-lg text-sm font-medium">🚫 IP مسدود است</span>
            @else
                <form method="POST" action="{{ route('admin.audit-logs.blocked-ips.store') }}" class="inline">
                    @csrf
                    <input type="hidden" name="ip_address" value="{{ $ip }}">
                    <input type="hidden" name="reason" value="Manual block from IP detail page">
                    <button type="submit" class="bg-rose-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-rose-700 transition">
                        مسدود کردن IP
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5 text-center">
            <p class="text-slate-500 text-xs font-medium">تعداد درخواست‌ها</p>
            <p class="text-3xl font-bold text-slate-800 mt-2">{{ number_format($stats['total_requests']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5 text-center">
            <p class="text-slate-500 text-xs font-medium">Login Failed</p>
            <p class="text-3xl font-bold text-rose-600 mt-2">{{ number_format($stats['login_failed']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5 text-center">
            <p class="text-slate-500 text-xs font-medium">Login Success</p>
            <p class="text-3xl font-bold text-emerald-600 mt-2">{{ number_format($stats['login_success']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5 text-center">
            <p class="text-slate-500 text-xs font-medium">خطاهای ۴۰۳</p>
            <p class="text-3xl font-bold text-amber-600 mt-2">{{ number_format($stats['errors_403']) }}</p>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-800">📋 آخرین فعالیت‌ها</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-right px-4 py-3 font-medium">زمان</th>
                        <th class="text-right px-4 py-3 font-medium">Severity</th>
                        <th class="text-right px-4 py-3 font-medium">Action</th>
                        <th class="text-right px-4 py-3 font-medium">کاربر</th>
                        <th class="text-right px-4 py-3 font-medium">توضیحات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentLogs as $log)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-500 text-xs whitespace-nowrap">{{ verta($log->created_at)->format('Y/m/d H:i:s') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $log->severity->badgeClass() }}">
                                {{ $log->severity->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-700 text-xs">{{ $log->action->label() }}</td>
                        <td class="px-4 py-3 text-xs">{{ $log->user?->name ?? 'Guest' }}</td>
                        <td class="px-4 py-3 text-slate-600 text-xs truncate max-w-xs">{{ $log->description ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">فعالیتی یافت نشد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection