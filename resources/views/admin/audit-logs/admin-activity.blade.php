@extends('layouts.admin')

@section('title', 'فعالیت ادمین: ' . $admin->name)

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ url()->previous() }}" class="text-slate-400 hover:text-slate-600">←</a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">👨‍💼 فعالیت ادمین</h1>
                <p class="text-indigo-600 mt-1">{{ $admin->name }} <span class="text-slate-400 text-sm">(#{{ $admin->id }})</span></p>
            </div>
        </div>
    </div>

    <!-- Today's Summary -->
    <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
        <h3 class="font-bold text-slate-800 mb-4">📅 خلاصه امروز</h3>
        @if(count($summary) > 0)
        <div class="space-y-2">
            @foreach($summary as $action => $count)
            <div class="flex items-center gap-3">
                <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                <span class="text-slate-700 text-sm">{{ $count }} {{ $action }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-slate-400 text-sm">امروز فعالیتی ثبت نشده</p>
        @endif
    </div>

    <!-- Recent Logs -->
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-800">📋 آخرین فعالیت‌ها</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-right px-4 py-3 font-medium">زمان</th>
                        <th class="text-right px-4 py-3 font-medium">Action</th>
                        <th class="text-right px-4 py-3 font-medium">توضیحات</th>
                        <th class="text-right px-4 py-3 font-medium">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentLogs as $log)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-500 text-xs whitespace-nowrap">{{ verta($log->created_at)->format('Y/m/d H:i:s') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $log->severity->badgeClass() }}">
                                {{ $log->action->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600 text-xs">{{ $log->description ?? '-' }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $log->masked_ip }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">فعالیتی یافت نشد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection