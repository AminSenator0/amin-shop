@extends('layouts.admin')

@section('title', 'نشست‌های فعال')
@section('header', 'نشست‌های فعال')

@section('content')
    @php
        $totalSessions = $sessions->total();
        $currentSessionCount = $sessions->getCollection()
            ->filter(fn ($session) => $session->session_id === $currentSessionId)
            ->count();
    @endphp

    <div dir="rtl" class="space-y-6">

        {{-- Page heading --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2">
                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="h-5 w-5"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor"
                             stroke-width="1.8">
                            <rect x="3" y="4" width="18" height="13" rx="2"/>
                            <path stroke-linecap="round" d="M8 21h8m-4-4v4"/>
                        </svg>
                    </span>

                    <span class="text-xs font-semibold text-indigo-600">
                        امنیت حساب کاربری
                    </span>
                </div>

                <h1 class="text-xl font-extrabold tracking-tight text-zinc-900 sm:text-2xl">
                    نشست‌های فعال
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-500">
                    دستگاه‌هایی که با حساب شما وارد شده‌اند را بررسی و در صورت نیاز از حساب خارج کنید.
                </p>
            </div>

            <div class="inline-flex w-fit items-center gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3.5 py-2.5">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-50"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                </span>

                <span class="text-xs font-bold text-emerald-700">
                    {{ $totalSessions }} نشست ثبت‌شده
                </span>
            </div>
        </div>

        {{-- Security notice --}}
        <div class="flex gap-3 rounded-2xl border border-amber-200/70 bg-amber-50/70 p-4 sm:p-5">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="h-5 w-5"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor"
                     stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v3m0 4h.01M10.3 3.9 2.9 17a2 2 0 0 0 1.7 3h14.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/>
                </svg>
            </div>

            <div>
                <h3 class="text-sm font-bold text-amber-900">
                    مراقب ورودهای ناشناس باشید
                </h3>

                <p class="mt-1 text-xs leading-6 text-amber-800/80 sm:text-sm">
                    اگر دستگاه یا موقعیت جغرافیایی ناشناسی مشاهده کردید، نشست مربوطه را خاتمه دهید.
                    در صورت مشاهده ورود مشکوک، رمز عبور خود را نیز تغییر دهید.
                </p>
            </div>
        </div>

        {{-- Sessions card --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm">

            {{-- Card header --}}
            <div class="flex flex-col gap-3 border-b border-zinc-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="text-base font-extrabold text-zinc-900">
                        دستگاه‌های متصل
                    </h2>

                    <p class="mt-1 text-xs leading-5 text-zinc-500">
                        فهرست دستگاه‌ها و آخرین زمان فعالیت آن‌ها
                    </p>
                </div>

                <div class="inline-flex w-fit items-center gap-2 rounded-lg bg-zinc-50 px-3 py-2 text-xs text-zinc-500 ring-1 ring-inset ring-zinc-200/70">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-4 w-4 text-zinc-400"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>

                    <span>
                        زمان‌ها به وقت محلی
                    </span>
                </div>
            </div>

            {{-- Desktop table --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[850px] text-right">
                    <thead>
                        <tr class="border-b border-zinc-100 bg-zinc-50/70">
                            <th class="px-6 py-4 text-xs font-bold text-zinc-500">
                                دستگاه
                            </th>

                            <th class="px-5 py-4 text-xs font-bold text-zinc-500">
                                موقعیت
                            </th>

                            <th class="px-5 py-4 text-xs font-bold text-zinc-500">
                                آدرس IP
                            </th>

                            <th class="px-5 py-4 text-xs font-bold text-zinc-500">
                                تاریخ ورود
                            </th>

                            <th class="px-5 py-4 text-xs font-bold text-zinc-500">
                                آخرین فعالیت
                            </th>

                            <th class="px-5 py-4 text-center text-xs font-bold text-zinc-500">
                                وضعیت
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-100">
                        @forelse($sessions as $session)
                            @php
                                $isCurrent = $session->session_id === $currentSessionId;
                            @endphp

                            <tr class="group transition-colors duration-150 hover:bg-zinc-50/70 {{ $isCurrent ? 'bg-emerald-50/30' : '' }}">

                                {{-- Device --}}
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl
                                            {{ $isCurrent ? 'bg-emerald-100 text-emerald-700' : 'bg-zinc-100 text-zinc-600' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 class="h-5 w-5"
                                                 fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke="currentColor"
                                                 stroke-width="1.7">
                                                <rect x="3" y="4" width="18" height="13" rx="2"/>
                                                <path stroke-linecap="round" d="M8 21h8m-4-4v4"/>
                                            </svg>
                                        </div>

                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="text-sm font-bold text-zinc-800">
                                                    {{ $session->browser ?? 'مرورگر نامشخص' }}
                                                </p>

                                                @if($isCurrent)
                                                    <span class="inline-flex items-center gap-1 rounded-md bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                        این دستگاه
                                                    </span>
                                                @endif
                                            </div>

                                            <p class="mt-1 text-xs text-zinc-500">
                                                {{ $session->os ?? 'سیستم‌عامل نامشخص' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Location --}}
                                <td class="px-5 py-5">
                                    @if($session->country)
                                        <div class="flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 class="h-4 w-4 shrink-0 text-zinc-400"
                                                 fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke="currentColor"
                                                 stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M12 21s7-5.2 7-12a7 7 0 1 0-14 0c0 6.8 7 12 7 12Z"/>
                                                <circle cx="12" cy="9" r="2.2"/>
                                            </svg>

                                            <div>
                                                <p class="text-sm font-semibold text-zinc-800">
                                                    {{ $session->country }}
                                                </p>

                                                <p class="mt-1 text-xs text-zinc-500">
                                                    {{ $session->city ?? 'شهر نامشخص' }}
                                                </p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-xs text-zinc-400">
                                            <span class="h-1.5 w-1.5 rounded-full bg-zinc-300"></span>
                                            موقعیت نامشخص
                                        </span>
                                    @endif
                                </td>

                                {{-- IP --}}
                                <td class="px-5 py-5">
                                    <code dir="ltr"
                                          class="inline-block rounded-lg border border-zinc-200/80 bg-zinc-50 px-2.5 py-1.5 font-mono text-xs text-zinc-700">
                                        {{ $session->ip_address }}
                                    </code>
                                </td>

                                {{-- Created at --}}
                                <td class="px-5 py-5">
                                    <p class="whitespace-nowrap text-xs font-medium text-zinc-700">
                                        {{ format_jalali($session->created_at, 'Y/m/d') }}
                                    </p>

                                    <p class="mt-1 whitespace-nowrap text-[11px] text-zinc-400">
                                        {{ format_jalali($session->created_at, 'H:i') }}
                                    </p>
                                </td>

                                {{-- Last active --}}
                                <td class="px-5 py-5">
                                    <p class="whitespace-nowrap text-xs font-medium text-zinc-700">
                                        {{ format_jalali($session->last_active_at, 'Y/m/d') }}
                                    </p>

                                    <p class="mt-1 whitespace-nowrap text-[11px] text-zinc-400">
                                        {{ format_jalali($session->last_active_at, 'H:i') }}
                                    </p>
                                </td>

                                {{-- Actions --}}
                                <td class="px-5 py-5 text-center">
                                    @if($isCurrent)
                                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 class="h-4 w-4"
                                                 fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke="currentColor"
                                                 stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="m5 12 4 4L19 6"/>
                                            </svg>
                                            نشست جاری
                                        </span>
                                    @else
                                        <form method="POST"
                                              action="{{ route('admin.account-sessions.destroy', $session) }}"
                                              onsubmit="return confirm('آیا از خاتمه این نشست اطمینان دارید؟ کاربر از آن دستگاه خارج خواهد شد.')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 rounded-lg border border-red-100 bg-white px-3 py-2 text-xs font-semibold text-red-600 transition-all duration-200 hover:border-red-200 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-200">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="h-4 w-4"
                                                     fill="none"
                                                     viewBox="0 0 24 24"
                                                     stroke="currentColor"
                                                     stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M16 11V7a4 4 0 0 0-8 0v4m-3 0h14l-1 10H6L5 11Z"/>
                                                </svg>

                                                خاتمه نشست
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-20 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="h-8 w-8"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke="currentColor"
                                             stroke-width="1.5">
                                            <rect x="3" y="4" width="18" height="13" rx="2"/>
                                            <path stroke-linecap="round" d="M8 21h8m-4-4v4"/>
                                        </svg>
                                    </div>

                                    <h3 class="mt-5 text-sm font-bold text-zinc-800">
                                        هنوز نشستی ثبت نشده است
                                    </h3>

                                    <p class="mt-2 text-xs leading-6 text-zinc-500">
                                        پس از ورود به حساب، اطلاعات دستگاه شما در این قسمت نمایش داده می‌شود.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile cards --}}
            <div class="divide-y divide-zinc-100 md:hidden">
                @forelse($sessions as $session)
                    @php
                        $isCurrent = $session->session_id === $currentSessionId;
                    @endphp

                    <div class="p-4 transition-colors {{ $isCurrent ? 'bg-emerald-50/30' : 'bg-white' }}">
                        <div class="flex items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl
                                {{ $isCurrent ? 'bg-emerald-100 text-emerald-700' : 'bg-zinc-100 text-zinc-600' }}">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-5 w-5"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="1.7">
                                    <rect x="3" y="4" width="18" height="13" rx="2"/>
                                    <path stroke-linecap="round" d="M8 21h8m-4-4v4"/>
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-bold text-zinc-800">
                                        {{ $session->browser ?? 'مرورگر نامشخص' }}
                                    </h3>

                                    @if($isCurrent)
                                        <span class="rounded-md bg-emerald-100 px-2 py-1 text-[10px] font-bold text-emerald-700">
                                            دستگاه فعلی
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-1 text-xs text-zinc-500">
                                    {{ $session->os ?? 'سیستم‌عامل نامشخص' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-zinc-50 p-3">
                                <p class="text-[11px] font-medium text-zinc-400">
                                    موقعیت جغرافیایی
                                </p>

                                <p class="mt-2 text-xs font-semibold text-zinc-700">
                                    @if($session->country)
                                        {{ $session->country }}
                                        <span class="text-zinc-400">،</span>
                                        {{ $session->city ?? 'نامشخص' }}
                                    @else
                                        نامشخص
                                    @endif
                                </p>
                            </div>

                            <div class="rounded-xl bg-zinc-50 p-3">
                                <p class="text-[11px] font-medium text-zinc-400">
                                    آدرس IP
                                </p>

                                <p dir="ltr" class="mt-2 break-all text-left font-mono text-xs text-zinc-700">
                                    {{ $session->ip_address }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-zinc-50 p-3">
                                <p class="text-[11px] font-medium text-zinc-400">
                                    تاریخ ورود
                                </p>

                                <p class="mt-2 text-xs font-semibold text-zinc-700">
                                    {{ format_jalali($session->created_at, 'Y/m/d') }}
                                </p>

                                <p class="mt-1 text-[11px] text-zinc-500">
                                    {{ format_jalali($session->created_at, 'H:i') }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-zinc-50 p-3">
                                <p class="text-[11px] font-medium text-zinc-400">
                                    آخرین فعالیت
                                </p>

                                <p class="mt-2 text-xs font-semibold text-zinc-700">
                                    {{ format_jalali($session->last_active_at, 'Y/m/d') }}
                                </p>

                                <p class="mt-1 text-[11px] text-zinc-500">
                                    {{ format_jalali($session->last_active_at, 'H:i') }}
                                </p>
                            </div>
                        </div>

                        @if(!$isCurrent)
                            <form method="POST"
                                  action="{{ route('admin.account-sessions.destroy', $session) }}"
                                  class="mt-4"
                                  onsubmit="return confirm('آیا از خاتمه این نشست اطمینان دارید؟ کاربر از آن دستگاه خارج خواهد شد.')">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-3 text-xs font-bold text-red-600 transition-colors hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-200">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="h-4 w-4"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke="currentColor"
                                         stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M16 11V7a4 4 0 0 0-8 0v4m-3 0h14l-1 10H6L5 11Z"/>
                                    </svg>

                                    خاتمه این نشست
                                </button>
                            </form>
                        @else
                            <div class="mt-4 flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-bold text-emerald-700">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-4 w-4"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor"
                                     stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="m5 12 4 4L19 6"/>
                                </svg>

                                نشست جاری شما
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="px-6 py-16 text-center">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="h-8 w-8"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke="currentColor"
                                 stroke-width="1.5">
                                <rect x="3" y="4" width="18" height="13" rx="2"/>
                                <path stroke-linecap="round" d="M8 21h8m-4-4v4"/>
                            </svg>
                        </div>

                        <h3 class="mt-4 text-sm font-bold text-zinc-800">
                            هنوز نشستی ثبت نشده است
                        </h3>

                        <p class="mt-2 text-xs leading-6 text-zinc-500">
                            نشست‌های حساب شما در این قسمت نمایش داده می‌شوند.
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($sessions->hasPages())
                <div class="border-t border-zinc-100 bg-white px-4 py-4 sm:px-6">
                    {{ $sessions->links() }}
                </div>
            @endif
        </div>

        {{-- Footer hint --}}
        <div class="flex items-start gap-2 px-1 text-xs leading-6 text-zinc-400">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="mt-0.5 h-4 w-4 shrink-0"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor"
                 stroke-width="1.8">
                <circle cx="12" cy="12" r="9"/>
                <path stroke-linecap="round" d="M12 11v5m0-8h.01"/>
            </svg>

            <p>
                ورود مجدد با یک IP یکسان، طبق تنظیمات سیستم شما، نشست جدیدی ایجاد نمی‌کند
                و زمان آخرین فعالیت را به‌روزرسانی می‌کند.
            </p>
        </div>

    </div>
@endsection