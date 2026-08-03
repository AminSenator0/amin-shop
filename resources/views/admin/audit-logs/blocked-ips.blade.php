@extends('layouts.admin')

@section('title', 'IP های مسدود')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">🚫 IP های مسدود</h1>
            <p class="text-slate-500 text-sm mt-1">مدیریت IPهای مسدودشده توسط سیستم یا ادمین</p>
        </div>
    </div>

    <!-- Block Form -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
        <h3 class="font-bold text-slate-800 mb-3">➕ مسدود کردن IP جدید</h3>
        <form method="POST" action="{{ route('admin.audit-logs.blocked-ips.store') }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">IP Address</label>
                <input type="text" name="ip_address" required class="border border-slate-200 rounded-lg px-3 py-2 text-sm font-mono" placeholder="192.168.1.1">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-slate-600 mb-1">دلیل</label>
                <input type="text" name="reason" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm" placeholder="دلیل مسدودسازی...">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">تا تاریخ (اختیاری)</label>
                <input type="datetime-local" name="blocked_until" class="border border-slate-200 rounded-lg px-3 py-2 text-sm">
            </div>
            <button type="submit" class="bg-rose-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-rose-700 transition">
                مسدود کردن
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-right px-4 py-3 font-medium">IP</th>
                        <th class="text-right px-4 py-3 font-medium">دلیل</th>
                        <th class="text-right px-4 py-3 font-medium">مسدودکننده</th>
                        <th class="text-right px-4 py-3 font-medium">تعداد تلاش</th>
                        <th class="text-right px-4 py-3 font-medium">وضعیت</th>
                        <th class="text-right px-4 py-3 font-medium">انقضا</th>
                        <th class="text-right px-4 py-3 font-medium">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ips as $ip)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs text-slate-700">{{ $ip->ip_address }}</td>
                        <td class="px-4 py-3 text-slate-700 text-xs">{{ $ip->reason }}</td>
                        <td class="px-4 py-3 text-slate-600 text-xs">{{ $ip->blocker?->name ?? 'System' }}</td>
                        <td class="px-4 py-3 text-slate-600 text-xs">{{ $ip->failed_attempts }}</td>
                        <td class="px-4 py-3">
                            @if($ip->isActive())
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-rose-100 text-rose-700">🚫 مسدود</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-700">✅ آزاد</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-xs">
                            {{ $ip->blocked_until ? verta($ip->blocked_until)->format('Y/m/d H:i') : '♾️ دائمی' }}
                        </td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.audit-logs.blocked-ips.destroy', $ip) }}" class="inline" onsubmit="return confirm('آیا مطمئنید؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-emerald-600 hover:text-emerald-700 text-xs font-medium">🔓 آزاد کردن</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-slate-400">هیچ IP مسدودی یافت نشد ✅</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $ips->links() }}
        </div>
    </div>
</div>
@endsection