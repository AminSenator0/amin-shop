@extends('layouts.admin')

@section('title', 'فعالیت کاربر: ' . $user->name)

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ url()->previous() }}" class="text-slate-400 hover:text-slate-600">←</a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">👤 فعالیت کاربر</h1>
                <p class="text-indigo-600 mt-1">{{ $user->name }} <span class="text-slate-400 text-sm">(#{{ $user->id }})</span> — {{ $user->email }}</p>
            </div>
        </div>
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5 text-center">
            <p class="text-slate-500 text-xs font-medium">کل فعالیت‌ها</p>
            <p class="text-3xl font-bold text-slate-800 mt-2">{{ number_format($summary['total']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5 text-center">
            <p class="text-slate-500 text-xs font-medium">ورودهای موفق</p>
            <p class="text-3xl font-bold text-emerald-600 mt-2">{{ number_format($summary['login_count']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5 text-center">
            <p class="text-slate-500 text-xs font-medium">سفارش‌ها</p>
            <p class="text-3xl font-bold text-indigo-600 mt-2">{{ number_format($summary['order_count']) }}</p>
        </div>
    </div>

    <!-- Timeline -->
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
                        <th class="text-right px-4 py-3 font-medium">دسته</th>
                        <th class="text-right px-4 py-3 font-medium">Action</th>
                        <th class="text-right px-4 py-3 font-medium">IP</th>
                        <th class="text-right px-4 py-3 font-medium">توضیحات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-500 text-xs whitespace-nowrap">{{ verta($log->created_at)->format('Y/m/d H:i:s') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $log->severity->badgeClass() }}">
                                {{ $log->severity->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs">{{ $log->category->icon() }} {{ $log->category->label() }}</td>
                        <td class="px-4 py-3 text-slate-700 text-xs">{{ $log->action->label() }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.audit-logs.ip-detail', $log->ip_address) }}" class="text-indigo-600 hover:underline font-mono text-xs">{{ $log->masked_ip }}</a>
                        </td>
                        <td class="px-4 py-3 text-slate-600 text-xs truncate max-w-xs">{{ $log->description ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">فعالیتی یافت نشد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection