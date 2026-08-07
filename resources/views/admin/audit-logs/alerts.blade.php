@extends('layouts.admin')

@section('title', 'هشدارهای امنیتی')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800">هشدارهای امنیتی</h1>
                <p class="text-slate-500 text-sm mt-0.5">رویدادهای مشکوک شناسایی‌شده توسط سیستم</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.audit-logs.dashboard') }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-700 rounded-lg px-4 py-2 text-sm font-medium hover:bg-slate-50 hover:border-slate-300 transition-all">
                <svg class="h-[18px] w-[18px] text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
                بازگشت به داشبورد
            </a>
        </div>
    </div>
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">CRITICAL</p>
                <p class="text-lg font-bold text-slate-800">{{ $alerts->where('is_resolved', false)->where('severity.value', 'critical')->count() }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-50 text-orange-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">HIGH</p>
                <p class="text-lg font-bold text-slate-800">{{ $alerts->where('is_resolved', false)->where('severity.value', 'high')->count() }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">WARNING</p>
                <p class="text-lg font-bold text-slate-800">{{ $alerts->where('is_resolved', false)->where('severity.value', 'warning')->count() }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">کل فعالیت ها</p>
                <p class="text-lg font-bold text-slate-800">{{ $alerts->where('is_resolved', false)->count() }}</p>
            </div>
        </div>
    </div>
    <!-- Filter Panel -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Severity</label>
                <select name="severity" class="w-40 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition bg-white">
                    <option value="">همه</option>
                    <option value="critical" @selected(request('severity')=='critical')>CRITICAL</option>
                    <option value="high" @selected(request('severity')=='high')>HIGH</option>
                    <option value="warning" @selected(request('severity')=='warning')>WARNING</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">وضعیت</label>
                <select name="status" class="w-40 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition bg-white">
                    <option value="">همه</option>
                    <option value="unresolved" @selected(request('status')=='unresolved')>فعال</option>
                    <option value="resolved" @selected(request('status')=='resolved')>برطرف‌شده</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-indigo-700 transition shadow-sm shadow-indigo-200">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    اعمال
                </button>
                <a href="{{ route('admin.audit-logs.alerts') }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-700 rounded-lg px-4 py-2 text-sm font-medium hover:bg-slate-50 hover:border-slate-300 transition">
                    <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    پاک کردن
                </a>
            </div>
        </form>
    </div>

    <!-- Alerts Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-600">
                    <tr>
                        <th class="text-right px-4 py-3 font-semibold text-xs w-24">وضعیت</th>
                        <th class="text-right px-4 py-3 font-semibold text-xs w-24">Severity</th>
                        <th class="text-right px-4 py-3 font-semibold text-xs w-36">نوع</th>
                        <th class="text-right px-4 py-3 font-semibold text-xs">پیام</th>
                        <th class="text-right px-4 py-3 font-semibold text-xs w-20">جزئیات</th>
                        <th class="text-right px-4 py-3 font-semibold text-xs w-36">IP</th>
                        <th class="text-right px-4 py-3 font-semibold text-xs w-32">کاربر</th>
                        <th class="text-right px-4 py-3 font-semibold text-xs w-36">زمان</th>
                        <th class="text-right px-4 py-3 font-semibold text-xs w-28">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($alerts as $alert)
                    <tr class="hover:bg-slate-50/60 transition-colors {{ $alert->is_resolved ? 'opacity-60' : '' }}">
                        <td class="px-4 py-3">
                            @if($alert->is_resolved)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    برطرف
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-100">
                                    <svg class="h-3.5 w-3.5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                    فعال
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-bold {{ $alert->severity->badgeClass() }}">
                                <svg class="h-3 w-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    @if($alert->severity->value === 'critical')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                    @elseif($alert->severity->value === 'high')
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                    @endif
                                </svg>
                                {{ $alert->severity->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-700 text-xs font-medium">{{ $alert->alert_type }}</td>
                        <td class="px-4 py-3 text-slate-700 text-xs max-w-xs truncate" title="{{ $alert->message }}">{{ $alert->message }}</td>
                        <td class="px-4 py-3 relative">
                            @if(!empty($alert->evidence) && is_array($alert->evidence))
                            <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')" class="inline-flex items-center gap-1 text-slate-500 hover:text-indigo-600 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                                <span class="text-xs">نمایش</span>
                            </button>
                            <div class="hidden absolute z-10 right-0 mt-2 w-80 bg-white rounded-lg border border-slate-200 shadow-xl p-3 text-xs">
                                <div class="font-semibold text-slate-700 mb-2 pb-1 border-b border-slate-100">Evidence</div>
                                <pre class="bg-slate-50 rounded p-2 overflow-auto max-h-48 text-slate-600 font-mono text-[10px] leading-relaxed">{{ json_encode($alert->evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                            @else
                            <span class="text-slate-300 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($alert->ip_address)
                            <a href="{{ route('admin.audit-logs.ip-detail', $alert->ip_address) }}" class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-700 font-mono text-xs transition">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>
                                {{ $alert->ip_address }}
                            </a>
                            @else
                            <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-700 text-xs">
                            @if($alert->user)
                            <div class="flex items-center gap-1.5">
                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                {{ $alert->user->name }}
                            </div>
                            @else
                            <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-xs whitespace-nowrap font-mono">{{ verta($alert->created_at)->format('Y/m/d H:i') }}</td>
                        <td class="px-4 py-3">
                            @if(!$alert->is_resolved)
                            <form method="POST" action="{{ route('admin.audit-logs.alerts.resolve', $alert) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center gap-1.5 text-emerald-600 hover:text-emerald-700 text-xs font-semibold transition hover:underline">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    برطرف کردن
                                </button>
                            </form>
                            @else
                            <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                    <td colspan="9" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-500 mb-3">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-slate-500">هیچ هشداری یافت نشد</p>
                                <p class="text-xs mt-1">وضعیت سیستم امنیتی پایدار است</p>
                            </div>
                        </td>
                    </tr>
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