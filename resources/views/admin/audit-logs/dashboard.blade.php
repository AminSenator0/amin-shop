@extends('layouts.admin')

@section('title', 'داشبورد لاگ‌ها')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">🛡️ داشبورد لاگ‌ها</h1>
            <p class="text-slate-500 text-sm mt-1">نظارت لحظه‌ای بر فعالیت‌ها و تهدیدات امنیتی</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.audit-logs.alerts') }}" class="relative bg-white border border-slate-200 rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                🔔 هشدارها
                @if($alertCount > 0)
                <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ $alertCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.audit-logs.index') }}" class="bg-indigo-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-indigo-700 transition">
                مشاهده همه لاگ‌ها
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-medium">لاگ‌های امروز</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($todayCount) }}</p>
                </div>
                <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center text-lg">📊</div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-medium">۷ روز اخیر</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($weekCount) }}</p>
                </div>
                <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center text-lg">📈</div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-medium">هشدارهای CRITICAL امروز</p>
                    <p class="text-2xl font-bold text-rose-600 mt-1">{{ number_format($criticalCount) }}</p>
                </div>
                <div class="w-10 h-10 bg-rose-50 rounded-lg flex items-center justify-center text-lg">🚨</div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-500 text-xs font-medium">هشدارهای فعال</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($alertCount) }}</p>
                </div>
                <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center text-lg">⚠️</div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="font-bold text-slate-800 mb-4">📈 نمودار رویدادها (۷ روز اخیر)</h3>
            <canvas id="eventsChart" height="100"></canvas>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="font-bold text-slate-800 mb-4">🥧 توزیع دسته‌بندی‌ها (امروز)</h3>
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    <!-- Recent Critical -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800">🔴 آخرین رویدادهای بحرانی</h3>
            <a href="{{ route('admin.audit-logs.index', ['severity' => 'critical']) }}" class="text-indigo-600 text-sm hover:underline">مشاهده همه</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-right px-4 py-3 font-medium">زمان</th>
                        <th class="text-right px-4 py-3 font-medium">دسته</th>
                        <th class="text-right px-4 py-3 font-medium">اکشن</th>
                        <th class="text-right px-4 py-3 font-medium">کاربر</th>
                        <th class="text-right px-4 py-3 font-medium">IP</th>
                        <th class="text-right px-4 py-3 font-medium">توضیحات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentCritical as $log)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ verta($log->created_at)->format('Y/m/d H:i') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">
                                {{ $log->category->icon() }} {{ $log->category->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $log->severity->badgeClass() }}">
                                {{ $log->action->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($log->user)
                            <a href="{{ route('admin.audit-logs.user-activity', $log->user) }}" class="text-indigo-600 hover:underline">{{ $log->user->name }}</a>
                            @else
                            <span class="text-slate-400">Guest</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.audit-logs.ip-detail', $log->ip_address) }}" class="text-indigo-600 hover:underline font-mono text-xs">{{ $log->masked_ip }}</a>
                        </td>
                        <td class="px-4 py-3 text-slate-600 truncate max-w-xs">{{ $log->description ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">هیچ رویداد بحرانی یافت نشد ✅</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    new Chart(document.getElementById('eventsChart'), {
        type: 'line',
        data: {
            labels: @json($chartData['labels']),
            datasets: [
                { label: 'Login Success', data: @json($chartData['login_success']), borderColor: '#10b981', backgroundColor: '#10b98120', tension: 0.4, fill: true },
                { label: 'Login Failed', data: @json($chartData['login_failed']), borderColor: '#f43f5e', backgroundColor: '#f43f5e20', tension: 0.4, fill: true },
                { label: 'Security Alerts', data: @json($chartData['security']), borderColor: '#f59e0b', backgroundColor: '#f59e0b20', tension: 0.4, fill: true },
                { label: 'Payment Errors', data: @json($chartData['payment_errors']), borderColor: '#8b5cf6', backgroundColor: '#8b5cf620', tension: 0.4, fill: true },
                { label: 'Admin Actions', data: @json($chartData['admin_actions']), borderColor: '#3b82f6', backgroundColor: '#3b82f620', tension: 0.4, fill: true },
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top', rtl: true } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: @json($categories['labels']),
            datasets: [{
                data: @json($categories['data']),
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#f43f5e', '#8b5cf6', '#06b6d4', '#64748b']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom', rtl: true } }
        }
    });
</script>
@endsection