@extends('layouts.admin')

@section('title', 'لاگ‌های سیستم')

@section('content')
<div class="p-6" x-data="{ showFilters: false }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">📋 لاگ‌های سیستم</h1>
            <p class="text-slate-500 text-sm mt-1">جستجو و فیلتر پیشرفته رویدادها</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="showFilters = !showFilters" class="bg-white border border-slate-200 text-slate-700 rounded-lg px-4 py-2 text-sm font-medium hover:bg-slate-50 transition">
                🔍 فیلترها
            </button>
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="bg-indigo-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-indigo-700 transition">
                    📥 Export
                </button>
                <div x-show="open" @click.away="open = false" class="absolute left-0 mt-2 w-40 bg-white border border-slate-200 rounded-lg shadow-lg z-50 overflow-hidden">
                    <a href="{{ route('admin.audit-logs.export', array_merge(request()->all(), ['format' => 'csv'])) }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">☑️ CSV</a>
                    <a href="{{ route('admin.audit-logs.export', array_merge(request()->all(), ['format' => 'json'])) }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">☑️ JSON</a>
                    <a href="{{ route('admin.audit-logs.export', array_merge(request()->all(), ['format' => 'pdf'])) }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">☑️ PDF</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Panel -->
    <div x-show="showFilters" x-transition class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <!-- Severity -->
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Severity</label>
                    <select name="severity" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">همه</option>
                        @foreach($severities as $sev)
                        <option value="{{ $sev->value }}" @selected(request('severity') == $sev->value)>
                            {{ $sev->label() }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">دسته‌بندی</label>
                    <select name="category" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">همه</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->value }}" @selected(request('category') == $cat->value)>
                            {{ $cat->icon() }} {{ $cat->label() }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action -->
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Action</label>
                    <select name="action" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">همه</option>
                        @foreach($actions as $act)
                        <option value="{{ $act->value }}" @selected(request('action') == $act->value)>
                            {{ $act->label() }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- User Search -->
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">کاربر (نام/ایمیل/ID)</label>
                    <input type="text" name="user_search" value="{{ request('user_search') }}" 
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="جستجو...">
                </div>

                <!-- IP -->
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">IP Address</label>
                    <input type="text" name="ip" value="{{ request('ip') }}" 
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono"
                        placeholder="185.xxx.xxx.xxx">
                </div>

                <!-- Date Range -->
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">بازه زمانی</label>
                    <select name="date_range" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">همه زمان‌ها</option>
                        <option value="today" @selected(request('date_range') == 'today')>امروز</option>
                        <option value="yesterday" @selected(request('date_range') == 'yesterday')>دیروز</option>
                        <option value="week" @selected(request('date_range') == 'week')>۷ روز اخیر</option>
                        <option value="month" @selected(request('date_range') == 'month')>۳۰ روز اخیر</option>
                        <option value="custom" @selected(request('date_range') == 'custom')>سفارشی</option>
                    </select>
                </div>

                <!-- Custom Date -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">تاریخ سفارشی</label>
                    <div class="flex gap-2">
                        <input type="date" name="from" value="{{ request('from') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                        <span class="text-slate-400 self-center">تا</span>
                        <input type="date" name="to" value="{{ request('to') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 mt-4">
                <button type="submit" class="bg-indigo-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-indigo-700 transition">
                    اعمال فیلتر
                </button>
                <a href="{{ route('admin.audit-logs.index') }}" class="bg-white border border-slate-200 text-slate-700 rounded-lg px-4 py-2 text-sm font-medium hover:bg-slate-50 transition">
                    پاک کردن
                </a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-right px-4 py-3 font-medium w-10">#</th>
                        <th class="text-right px-4 py-3 font-medium">Severity</th>
                        <th class="text-right px-4 py-3 font-medium">دسته</th>
                        <th class="text-right px-4 py-3 font-medium">Action</th>
                        <th class="text-right px-4 py-3 font-medium">کاربر</th>
                        <th class="text-right px-4 py-3 font-medium">IP</th>
                        <th class="text-right px-4 py-3 font-medium">زمان</th>
                        <th class="text-right px-4 py-3 font-medium">توضیحات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3 text-slate-400 text-xs">{{ $log->id }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $log->severity->badgeClass() }}">
                                {{ $log->severity->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 text-slate-700">
                                <span>{{ $log->category->icon() }}</span>
                                <span class="text-xs">{{ $log->category->label() }}</span>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-700 text-xs">{{ $log->action->label() }}</td>
                        <td class="px-4 py-3">
                            @if($log->user)
                            <a href="{{ route('admin.audit-logs.user-activity', $log->user) }}" class="text-indigo-600 hover:underline text-xs">{{ $log->user->name }}</a>
                            @else
                            <span class="text-slate-400 text-xs">Guest</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.audit-logs.ip-detail', $log->ip_address) }}" class="text-indigo-600 hover:underline font-mono text-xs direction-ltr inline-block">{{ $log->masked_ip }}</a>
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-xs whitespace-nowrap">{{ verta($log->created_at)->format('Y/m/d H:i') }}</td>
                        <td class="px-4 py-3 text-slate-600 text-xs truncate max-w-xs" title="{{ $log->description }}">{{ $log->description ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-slate-400">هیچ لاگی یافت نشد</td></tr>
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