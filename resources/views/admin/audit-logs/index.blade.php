@extends('layouts.admin')

@section('title', 'لاگ‌های سیستم')

@section('content')
<div class="p-6" x-data="{ showFilters: false }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V19.5a2.25 2.25 0 002.25 2.25h.75m-5.801 0c.065.21.1.433.1.664 0 .414-.336.75-.75.75h-4.5A.75.75 0 012 19.5V6.108c0-1.135.845-2.098 1.976-2.192a48.424 48.424 0 011.123-.08"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800">لاگ‌های سیستم</h1>
                <p class="text-slate-500 text-sm mt-0.5">جستجو و فیلتر پیشرفته رویدادها</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button @click="showFilters = !showFilters" class="inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-700 rounded-lg px-4 py-2 text-sm font-medium hover:bg-slate-50 hover:border-slate-300 transition-all">
                <svg class="h-[18px] w-[18px] text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/>
                </svg>
                فیلترها
            </button>
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="inline-flex items-center gap-2 bg-indigo-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-indigo-700 transition shadow-sm shadow-indigo-200">
                    <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    Export
                </button>
                <div x-show="open" @click.away="open = false" x-transition class="absolute left-0 mt-2 w-44 bg-white border border-slate-200 rounded-lg shadow-lg z-50 overflow-hidden">
                    <a href="{{ route('admin.audit-logs.export', array_merge(request()->all(), ['format' => 'csv'])) }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition">
                        <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        CSV
                    </a>
                    <a href="{{ route('admin.audit-logs.export', array_merge(request()->all(), ['format' => 'json'])) }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition">
                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"/></svg>
                        JSON
                    </a>
                    <a href="{{ route('admin.audit-logs.export', array_merge(request()->all(), ['format' => 'pdf'])) }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition">
                        <svg class="h-4 w-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Panel -->
    <div x-show="showFilters" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-6">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <!-- Severity -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Severity</label>
                    <select name="severity" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition bg-white">
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
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">دسته‌بندی</label>
                    <select name="category" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition bg-white">
                        <option value="">همه</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->value }}" @selected(request('category') == $cat->value)>
                            {{ $cat->label() }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Action</label>
                    <select name="action" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition bg-white">
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
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">کاربر (نام/ایمیل/ID)</label>
                    <div class="relative">
                        <input type="text" name="user_search" value="{{ request('user_search') }}" 
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 pr-9 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
                            placeholder="جستجو...">
                        <svg class="h-4 w-4 text-slate-400 absolute right-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    </div>
                </div>

                <!-- IP -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">IP Address</label>
                    <input type="text" name="ip" value="{{ request('ip') }}" 
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-mono placeholder:font-sans"
                        placeholder="185.xxx.xxx.xxx">
                </div>

                <!-- Date Range -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">بازه زمانی</label>
                    <select name="date_range" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition bg-white">
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
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">تاریخ سفارشی</label>
                    <div class="flex gap-2 items-center">
                        <input type="date" name="from" value="{{ request('from') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                        <span class="text-slate-400 text-sm">تا</span>
                        <input type="date" name="to" value="{{ request('to') }}" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 mt-5 pt-4 border-t border-slate-100">
                <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-indigo-700 transition shadow-sm shadow-indigo-200">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    اعمال فیلتر
                </button>
                <a href="{{ route('admin.audit-logs.index') }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-700 rounded-lg px-4 py-2 text-sm font-medium hover:bg-slate-50 hover:border-slate-300 transition">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    پاک کردن
                </a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-600">
                    <tr>
                        <th class="text-right px-4 py-3 font-semibold text-xs w-12">#</th>
                        <th class="text-right px-4 py-3 font-semibold text-xs w-28">Severity</th>
                        <th class="text-right px-4 py-3 font-semibold text-xs w-32">دسته</th>
                        <th class="text-right px-4 py-3 font-semibold text-xs">Action</th>
                        <th class="text-right px-4 py-3 font-semibold text-xs">کاربر</th>
                        <th class="text-right px-4 py-3 font-semibold text-xs w-32">IP</th>
                        <th class="text-right px-4 py-3 font-semibold text-xs w-36">زمان</th>
                        <th class="text-right px-4 py-3 font-semibold text-xs">توضیحات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/60 transition-colors cursor-pointer group" @click="$dispatch('open-log-detail', {{ $log->id }})">
                        <td class="px-4 py-3 text-slate-400 text-xs font-mono">{{ $log->id }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold {{ $log->severity->badgeClass() }}">
                                <svg class="h-3 w-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    @if($log->severity->value === 'critical')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                    @elseif($log->severity->value === 'high')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                                    @elseif($log->severity->value === 'warning')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                                    @endif
                                </svg>
                                {{ $log->severity->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 text-slate-700">
                                <span class="text-slate-400">{{ $log->category->icon() }}</span>
                                <span class="text-xs font-medium">{{ $log->category->label() }}</span>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-700 text-xs font-medium">{{ $log->action->label() }}</td>
                        <td class="px-4 py-3">
                            @if($log->user)
                            <a href="{{ route('admin.audit-logs.user-activity', $log->user) }}" class="inline-flex items-center gap-1.5 text-indigo-600 hover:text-indigo-700 text-xs font-medium transition" @click.stop>
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                {{ $log->user->name }}
                            </a>
                            @else
                            <span class="inline-flex items-center gap-1.5 text-slate-400 text-xs">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                Guest
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.audit-logs.ip-detail', $log->ip_address) }}" class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-700 font-mono text-xs transition" @click.stop>
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>
                                {{ $log->masked_ip }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-xs whitespace-nowrap font-mono">{{ verta($log->created_at)->format('Y/m/d H:i') }}</td>
                        <td class="px-4 py-3 text-slate-600 text-xs truncate max-w-xs" title="{{ $log->description }}">{{ $log->description ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <svg class="h-12 w-12 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V19.5a2.25 2.25 0 002.25 2.25h.75m-5.801 0c.065.21.1.433.1.664 0 .414-.336.75-.75.75h-4.5A.75.75 0 012 19.5V6.108c0-1.135.845-2.098 1.976-2.192a48.424 48.424 0 011.123-.08"/>
                                </svg>
                                <p class="text-sm font-medium">هیچ لاگی یافت نشد</p>
                                <p class="text-xs mt-1">فیلترها را تغییر دهید یا جستجو کنید</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<!-- Log Detail Modal -->
<div x-data="logDetailModal()" 
     x-cloak
     @open-log-detail.window="loadLog($event.detail)"
     @keydown.escape.window="open = false">

    <!-- Wrapper -->
    <div x-show="open" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <!-- Backdrop (click to close) -->
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="open = false"></div>

        <!-- Modal Card -->
        <div @click.stop
             class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col border border-slate-200"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4">

            <!-- Header (ثابت) -->
            <div class="shrink-0 px-6 py-4 border-b border-slate-200 rounded-t-2xl bg-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">جزئیات رویداد</h3>
                        <p class="text-xs text-slate-500 font-mono mt-0.5" x-text="'Log #' + log.id"></p>
                    </div>
                </div>
                <button @click="open = false" class="text-slate-400 hover:text-slate-600 transition p-2 hover:bg-slate-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Body (اسکرول‌دار) -->
            <div class="flex-1 overflow-y-auto p-6 space-y-5" x-show="!loading" x-cloak>

                <!-- Severity & Action Badge -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span :class="log.severity?.badge_class" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold" x-text="log.severity?.label"></span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 text-xs font-semibold">
                        <span x-text="log.category?.icon"></span>
                        <span x-text="log.category?.label"></span>
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 text-xs font-semibold" x-text="log.action?.label"></span>
                </div>

                <!-- User Section -->
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">کاربر</h4>
                    </div>
                    <template x-if="log.user">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm" x-text="log.user.name.charAt(0)"></div>
                            <div>
                                <a :href="log.user.link" class="font-bold text-slate-800 hover:text-indigo-600 transition text-sm" x-text="log.user.name"></a>
                                <p class="text-xs text-slate-500" x-text="log.user.email"></p>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    ID: <span x-text="log.user.id"></span>
                                    <span x-show="log.user.is_admin" class="text-amber-600 font-medium mr-1">(ادمین)</span>
                                </p>
                            </div>
                        </div>
                    </template>
                    <template x-if="!log.user">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            </div>
                            <span class="text-slate-500 font-medium text-sm">مهمان (Guest)</span>
                        </div>
                    </template>
                </div>

                <!-- Reference Section -->
                <template x-if="log.reference">
                    <div class="bg-amber-50 rounded-xl p-4 border border-amber-200">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                            <h4 class="text-xs font-bold text-amber-600 uppercase tracking-wider">مرجع مرتبط</h4>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-amber-900 text-sm" x-text="log.reference.label"></span>
                            <span class="text-xs text-amber-700 bg-amber-100 px-2 py-0.5 rounded font-medium" x-text="log.reference.type"></span>
                        </div>
                    </div>
                </template>

                <!-- Time & IP -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">زمان</h4>
                        </div>
                        <p class="font-mono text-slate-800 font-medium text-sm" x-text="log.created_at"></p>
                        <p class="text-xs text-slate-400 mt-1" x-text="log.created_at_diff"></p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>
                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">IP Address</h4>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-slate-800 font-medium text-sm" x-text="log.masked_ip"></span>
                            <span class="text-xs text-slate-400 font-mono" x-text="log.ip_address"></span>
                        </div>
                        <a :href="'/admin/audit-logs/ip/' + log.ip_address" 
                           class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-700 mt-2 transition font-medium">
                            مشاهده جزئیات IP
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Browser & Device -->
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"/></svg>
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">مرورگر و دستگاه</h4>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center bg-white rounded-lg p-3 border border-slate-100">
                            <svg class="h-6 w-6 text-slate-400 mx-auto mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>
                            <div class="text-xs text-slate-500 mb-0.5">مرورگر</div>
                            <div class="font-bold text-slate-800 text-sm" x-text="log.browser?.name"></div>
                        </div>
                        <div class="text-center bg-white rounded-lg p-3 border border-slate-100">
                            <svg class="h-6 w-6 text-slate-400 mx-auto mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25z"/></svg>
                            <div class="text-xs text-slate-500 mb-0.5">سیستم‌عامل</div>
                            <div class="font-bold text-slate-800 text-sm" x-text="log.browser?.os"></div>
                        </div>
                        <div class="text-center bg-white rounded-lg p-3 border border-slate-100">
                            <svg class="h-6 w-6 text-slate-400 mx-auto mb-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>
                            <div class="text-xs text-slate-500 mb-0.5">دستگاه</div>
                            <div class="font-bold text-slate-800 text-sm" x-text="log.browser?.device"></div>
                        </div>
                    </div>
                    <details class="mt-3">
                        <summary class="text-xs text-slate-500 cursor-pointer hover:text-slate-700 transition select-none inline-flex items-center gap-1">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                            User-Agent کامل
                        </summary>
                        <p class="mt-2 text-xs text-slate-600 font-mono bg-white p-2.5 rounded-lg border border-slate-200 break-all leading-relaxed" x-text="log.browser?.raw"></p>
                    </details>
                </div>

                <!-- Request Details -->
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">درخواست</h4>
                    </div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-700 font-mono" x-text="log.method"></span>
                        <span class="text-sm text-slate-600 font-mono break-all" x-text="log.url"></span>
                    </div>
                    <div x-show="log.session_id" class="flex items-center gap-1.5 text-xs text-slate-400 font-mono mt-2">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                        Session: <span x-text="log.session_id"></span>
                    </div>
                </div>

                <!-- Description -->
                <div x-show="log.description" class="bg-indigo-50 rounded-xl p-4 border border-indigo-100">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                        <h4 class="text-xs font-bold text-indigo-600 uppercase tracking-wider">توضیحات</h4>
                    </div>
                    <p class="text-slate-800 text-sm leading-relaxed" x-text="log.description"></p>
                </div>

                <!-- Payload (JSON) -->
                <template x-if="log.payload && Object.keys(log.payload).length > 0">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"/></svg>
                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Payload</h4>
                        </div>
                        <pre class="bg-slate-900 text-emerald-400 p-4 rounded-xl text-xs overflow-x-auto font-mono leading-relaxed"><code x-text="JSON.stringify(log.payload, null, 2)"></code></pre>
                    </div>
                </template>

                <!-- Old vs New Values -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="(log.old_values && Object.keys(log.old_values).length) || (log.new_values && Object.keys(log.new_values).length)">
                    <template x-if="log.old_values && Object.keys(log.old_values).length > 0">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="h-4 w-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <h4 class="text-xs font-bold text-rose-600 uppercase tracking-wider">مقادیر قبلی</h4>
                            </div>
                            <pre class="bg-rose-50 text-rose-900 p-3 rounded-xl text-xs overflow-x-auto font-mono border border-rose-200 leading-relaxed"><code x-text="JSON.stringify(log.old_values, null, 2)"></code></pre>
                        </div>
                    </template>
                    <template x-if="log.new_values && Object.keys(log.new_values).length > 0">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <h4 class="text-xs font-bold text-emerald-600 uppercase tracking-wider">مقادیر جدید</h4>
                            </div>
                            <pre class="bg-emerald-50 text-emerald-900 p-3 rounded-xl text-xs overflow-x-auto font-mono border border-emerald-200 leading-relaxed"><code x-text="JSON.stringify(log.new_values, null, 2)"></code></pre>
                        </div>
                    </template>
                </div>

                <!-- Device Fingerprint -->
                <div x-show="log.device_fingerprint" class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Device Fingerprint</h4>
                    </div>
                    <p class="font-mono text-xs text-slate-600 break-all bg-white p-2.5 rounded-lg border border-slate-200" x-text="log.device_fingerprint"></p>
                </div>

            </div>

            <!-- Loading -->
            <div x-show="loading" class="flex-1 flex flex-col items-center justify-center p-12">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-[3px] border-indigo-200 border-t-indigo-600"></div>
                <p class="mt-3 text-sm text-slate-500">در حال بارگذاری...</p>
            </div>

        </div>
    </div>
</div>

<script>
function logDetailModal() {
    return {
        open: false,
        loading: false,
        log: {},

        async loadLog(logId) {
            this.open = true;
            this.loading = true;

            try {
                const response = await fetch(`/admin/audit-logs/${logId}/show`);
                if (!response.ok) throw new Error('Failed to load');
                this.log = await response.json();
            } catch (error) {
                console.error('Error loading log:', error);
                alert('خطا در بارگذاری اطلاعات');
                this.open = false;
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection