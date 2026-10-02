@extends('layouts.admin')

@section('header', 'مجوزهای فوتر')

@section('content') <div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-zinc-400 mb-2">
                <a href="{{ route('admin.dashboard') }}"
                   class="transition-colors hover:text-zinc-700">
                    داشبورد
                </a>

                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                          d="M7.21 14.77a.75.75 0 01.02-1.06L10.94 10 7.23 6.29a.75.75 0 111.06-1.06l4.24 4.24a.75.75 0 010 1.06l-4.24 4.24a.75.75 0 01-1.06 0z"
                          clip-rule="evenodd"/>
                </svg>

                <span class="text-zinc-600">مجوزهای فوتر</span>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-teal-50 text-teal-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="1.7">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                    </svg>
                </div>

                <div>
                    <h1 class="text-lg font-black tracking-tight text-zinc-900">
                        مجوزهای فوتر
                    </h1>
                    <p class="mt-0.5 text-sm text-zinc-500">
                        نمادها و مجوزهایی که در بخش پایینی سایت نمایش داده می‌شوند.
                    </p>
                </div>
            </div>
        </div>

        <a href="{{ route('admin.footer-licenses.create') }}"
           class="admin-btn-primary inline-flex items-center justify-center gap-2 shadow-sm">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 4v16m8-8H4"/>
            </svg>
            <span>افزودن مجوز</span>
        </a>
    </div>


    {{-- Main Card --}}
    <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm">

        {{-- Card Header --}}
        <div class="flex flex-col gap-3 border-b border-zinc-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-black text-zinc-900">
                        لیست مجوزها
                    </h2>

                    <span class="inline-flex min-w-7 items-center justify-center rounded-full bg-zinc-100 px-2 py-1 text-[11px] font-bold text-zinc-600">
                        {{ $licenses->total() }}
                    </span>
                </div>

                <p class="mt-1 text-xs text-zinc-400">
                    مدیریت، ترتیب نمایش و وضعیت مجوزهای فوتر
                </p>
            </div>

            @if($licenses->isNotEmpty())
                <a href="{{ route('admin.footer-licenses.create') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-bold text-teal-600 transition-colors hover:text-teal-700">
                    <span>مجوز جدید</span>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 4v16m8-8H4"/>
                    </svg>
                </a>
            @endif
        </div>


        @if($licenses->isNotEmpty())

            {{-- Desktop / Tablet Table --}}
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-right">
                    <thead>
                        <tr class="border-b border-zinc-100 bg-zinc-50/70">
                            <th class="px-5 py-3 text-xs font-bold text-zinc-500">مجوز</th>
                            <th class="px-5 py-3 text-xs font-bold text-zinc-500">عنوان</th>
                            <th class="px-5 py-3 text-xs font-bold text-zinc-500">لینک مقصد</th>
                            <th class="px-5 py-3 text-center text-xs font-bold text-zinc-500">ترتیب</th>
                            <th class="px-5 py-3 text-center text-xs font-bold text-zinc-500">وضعیت</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-zinc-500">عملیات</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-zinc-100">
                        @foreach($licenses as $license)
                            <tr class="group transition-colors hover:bg-zinc-50/60">

                                {{-- Image --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center">
                                        <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50 p-2 transition-all group-hover:border-zinc-300 group-hover:bg-white">
                                            @if($license->imageUrl())
                                                <img
                                                    src="{{ $license->imageUrl() }}"
                                                    alt="{{ $license->title ?? 'مجوز' }}"
                                                    class="h-full w-full object-contain"
                                                >
                                            @else
                                                <svg class="h-6 w-6 text-zinc-300" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor"
                                                     stroke-width="1.5">
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l2.409 2.409m-4.5 4.5l2.25-2.25a2.25 2.25 0 013.182 0l2.409 2.409m0 0l3.409-3.409a2.25 2.25 0 013.182 0l1.159 1.159M3.75 20.25h16.5a1.5 1.5 0 001.5-1.5V5.25a1.5 1.5 0 00-1.5-1.5H3.75a1.5 1.5 0 00-1.5 1.5v13.5a1.5 1.5 0 001.5 1.5z"/>
                                                </svg>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Title --}}
                                <td class="px-5 py-4">
                                    <div class="max-w-[220px]">
                                        <p class="truncate text-sm font-bold text-zinc-900">
                                            {{ $license->title ?: 'بدون عنوان' }}
                                        </p>

                                        @if(!$license->title)
                                            <p class="mt-1 text-[11px] text-zinc-400">
                                                عنوانی ثبت نشده است
                                            </p>
                                        @endif
                                    </div>
                                </td>

                                {{-- Link --}}
                                <td class="px-5 py-4">
                                    @if($license->link)
                                        <div class="flex max-w-[250px] items-center gap-2">
                                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-400">
                                                <svg class="h-3.5 w-3.5" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor"
                                                     stroke-width="1.8">
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 11-5.656-5.656l1.5-1.5m7.328-7.328l1.5-1.5a4 4 0 015.656 5.656l-3 3a4 4 0 01-5.656 0"/>
                                                </svg>
                                            </span>

                                            <span dir="ltr"
                                                  class="truncate text-xs text-zinc-500">
                                                {{ $license->link }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs text-zinc-400">
                                            بدون لینک
                                        </span>
                                    @endif
                                </td>

                                {{-- Sort --}}
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg bg-zinc-100 px-2 text-xs font-bold text-zinc-600">
                                        {{ $license->sort_order }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-4 text-center">
                                    @if($license->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            فعال
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-2.5 py-1 text-[11px] font-bold text-zinc-500">
                                            <span class="h-1.5 w-1.5 rounded-full bg-zinc-400"></span>
                                            غیرفعال
                                        </span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1.5">

                                        <a href="{{ route('admin.footer-licenses.edit', $license) }}"
                                           title="ویرایش"
                                           class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 transition-all hover:border-teal-200 hover:bg-teal-50 hover:text-teal-600">
                                            <svg class="h-4 w-4" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor"
                                                 stroke-width="1.8">
                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 15.07a4.5 4.5 0 01-1.897 1.13L6 17l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      d="M19.5 7.5L16.5 4.5"/>
                                            </svg>
                                        </a>

                                        <form method="POST"
                                              action="{{ route('admin.footer-licenses.destroy', $license) }}"
                                              onsubmit="return confirm('این مجوز حذف شود؟')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    title="حذف"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-400 transition-all hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600">
                                                <svg class="h-4 w-4" fill="none"
                                                     viewBox="0 0 24 24" stroke="currentColor"
                                                     stroke-width="1.8">
                                                    <path stroke-linecap="round"
                                                          stroke-linejoin="round"
                                                          d="M6 7h12m-9 0v10m6-10v10M9 7V4.75A.75.75 0 019.75 4h4.5a.75.75 0 01.75.75V7m3 0v12.25a.75.75 0 01-.75.75h-12a.75.75 0 01-.75-.75V7"/>
                                                </svg>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>


            {{-- Pagination --}}
            <div class="border-t border-zinc-100 bg-zinc-50/30 px-5 py-4">
                {{ $licenses->links() }}
            </div>

        @else

            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center px-6 py-20 text-center">

                <div class="relative mb-5">
                    <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-zinc-50 text-zinc-300 ring-8 ring-zinc-50/70">
                        <svg class="h-9 w-9" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="1.4">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                        </svg>
                    </div>
                </div>

                <h3 class="text-sm font-black text-zinc-800">
                    هنوز مجوزی اضافه نشده است
                </h3>

                <p class="mt-2 max-w-md text-sm leading-6 text-zinc-500">
                    نماد اعتماد، ساماندهی، مجوزها و سایر نشان‌های اعتبار سایت را
                    اضافه کنید تا در فوتر نمایش داده شوند.
                </p>

                <a href="{{ route('admin.footer-licenses.create') }}"
                   class="admin-btn-primary mt-6 inline-flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 4v16m8-8H4"/>
                    </svg>
                    افزودن اولین مجوز
                </a>
            </div>

        @endif

    </div>
</div>

@endsection
