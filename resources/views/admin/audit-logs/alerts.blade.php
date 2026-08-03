@extends('layouts.admin')

@section('title', 'هشدارهای امنیتی')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">🚨 هشدارهای امنیتی</h1>
            <p class="text-slate-500 text-sm mt-1">رویدادهای مشکوک شناسایی‌شده توسط سیستم</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.audit-logs.dashboard') }}" class="bg-white border border-slate-200 text-slate-700 rounded-lg px-4 py-2 text-sm font-medium hover:bg-slate-50 transition">
                بازگشت به داشبورد
            </a>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Severity</label>
                <select name="severity" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
                    <option value="">همه</option>
                    <option value="critical" @selected(request('severity')=='critical')>🔴 CRITICAL</option>
                    <option value="high" @selected(request('severity')=='high')>🟠 HIGH</option>
                    <option value="warning" @selected(request('severity')=='warning')>🟡 WARNING</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">وضعیت</label>
                <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
                    <option value="">همه</option>
                    <option value="unresolved" @selected(request('status')=='unresolved')>⏳ فعال</option>
                    <option value="resolved" @selected(request('status')=='resolved')>✅ برطرف‌شده</option>
                </select>
            </div>
            <button type="submit" class="bg-indigo-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-indigo-700">اعمال</button>
            <a href="{{ route('admin.audit-logs.alerts') }}" class="bg-white border border-slate-200 text-slate-700 rounded-lg px-4 py-2 text-sm font-medium hover:bg-slate-50">پاک کردن</a>
        </form>
    </div>

    <!-- Alerts Table -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-right px-4 py-3 font-medium">وضعیت</th>
                        <th class="text-right px-4 py-3 font-medium">Severity</th>
                        <th class="text-right px-4 py-3 font-medium">نوع</th>
                        <th class="text-right px-4 py-3 font-medium">پیام</th>
                        <th class="text-right px-4 py-3 font-medium">IP</th>
                        <th class="text-right px-4 py-3 font-medium">کاربر</th>
                        <th class="text-right px-4 py-3 font-medium">زمان</th>
                        <th class="text-right px-4 py-3 font-medium">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($alerts as $alert)
                    <tr class="hover:bg-slate-50 {{ $alert->is_resolved ? 'opacity-60' : '' }}">
                        <td class="px-4 py-3">
                            @if($alert->is_resolved)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-700">✅ برطرف</span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-rose-100 text-rose-700">⏳ فعال</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $alert->severity->badgeClass() }}">
                                {{ $alert->severity->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600 text-xs">{{ $alert->alert_type }}</td>
                        <td class="px-4 py-3 text-slate-700 text-xs max-w-xs truncate" title="{{ $alert->message }}">{{ $alert->message }}</td>
                        <td class="px-4 py-3">
                            @if($alert->ip_address)
                            <a href="{{ route('admin.audit-logs.ip-detail', $alert->ip_address) }}" class="text-indigo-600 hover:underline font-mono text-xs">{{ $alert->ip_address }}</a>
                            @else
                            <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs">{{ $alert->user?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-500 text-xs whitespace-nowrap">{{ verta($alert->created_at)->format('Y/m/d H:i') }}</td>
                        <td class="px-4 py-3">
                            @if(!$alert->is_resolved)
                            <form method="POST" action="{{ route('admin.audit-logs.alerts.resolve', $alert) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-emerald-600 hover:text-emerald-700 text-xs font-medium">✓ برطرف کردن</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-slate-400">هیچ هشداری یافت نشد 🎉</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $alerts->links() }}
        </div>
    </div>
</div>
@endsection