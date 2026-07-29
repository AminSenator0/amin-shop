@extends('layouts.admin')

@section('header', 'سوالات متداول')

@section('content')
<div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
    <a href="{{ route('admin.homepage.index') }}" class="text-zinc-500 hover:text-indigo-600">صفحه اصلی</a>
    <span class="text-zinc-300">/</span>
    <span class="font-bold text-zinc-700">سوالات متداول</span>
</div>

<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <x-admin.stat-card
        label="کل سوالات"
        :value="$stats['total']"
        color="default"
        :href="route('admin.faqs.index', request()->only('search'))"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg>'
    />
    <x-admin.stat-card
        label="فعال"
        :value="$stats['active']"
        color="emerald"
        :href="route('admin.faqs.index', array_merge(request()->only('search'), ['status' => 'active']))"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
    />
    <x-admin.stat-card
        label="غیرفعال"
        :value="$stats['inactive']"
        color="amber"
        :href="route('admin.faqs.index', array_merge(request()->only('search'), ['status' => 'inactive']))"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>'
    />
</div>

<div class="admin-page-header">
    <x-admin.search-form placeholder="جستجوی سوال...">
        @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
    </x-admin.search-form>
    <a href="{{ route('admin.faqs.create') }}" class="admin-btn-primary">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
        سوال جدید
    </a>
</div>

@if(request('status'))
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <span class="text-sm text-zinc-500">فیلتر:</span>
        <span class="admin-badge-info">
            {{ request('status') === 'active' ? 'فعال' : 'غیرفعال' }}
        </span>
        <a href="{{ route('admin.faqs.index', request()->only('search')) }}" class="text-xs font-bold text-indigo-600 hover:underline">حذف فیلتر</a>
    </div>
@endif

<div class="space-y-3">
    @forelse($faqs as $faq)
        <article class="admin-card transition-shadow hover:shadow-md {{ ! $faq->is_active ? 'opacity-80' : '' }}">
            <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex min-w-0 flex-1 gap-3.5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100">
                        <span class="text-sm font-black">{{ to_persian_digits((string) $faq->sort_order) }}</span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                            <h3 class="font-bold text-zinc-900">{{ $faq->question }}</h3>

                            @if($faq->is_active)
                                <span class="admin-badge-success">فعال</span>
                            @else
                                <span class="admin-badge-neutral">غیرفعال</span>
                            @endif
                        </div>

                        <div class="mt-3 rounded-xl border border-zinc-100 bg-zinc-50/80 px-3.5 py-3">
                            <x-expandable-text :text="$faq->answer" :limit="160" class="min-w-0" />
                        </div>
                    </div>
                </div>

                <div class="flex shrink-0 items-center justify-end gap-1 border-t border-zinc-100 pt-4 sm:border-t-0 sm:pt-0">
                    <x-admin.table-actions>
                        <x-admin.table-action type="edit" :href="route('admin.faqs.edit', $faq)" />
                        <x-admin.table-action type="delete" :action="route('admin.faqs.destroy', $faq)" />
                    </x-admin.table-actions>
                </div>
            </div>
        </article>
    @empty
        <div class="admin-card flex flex-col items-center justify-center px-6 py-16 text-center">
            <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                </svg>
            </div>
            <p class="font-bold text-zinc-700">سوالی ثبت نشده است</p>
            <p class="mt-1 text-sm text-zinc-500">اولین سوال متداول را اضافه کنید تا در صفحه اصلی و صفحه FAQ نمایش داده شود.</p>
            <a href="{{ route('admin.faqs.create') }}" class="admin-btn-primary mt-5 text-xs">افزودن سوال جدید</a>
        </div>
    @endforelse
</div>

<x-admin.pagination :paginator="$faqs" class="mt-6 rounded-2xl border border-zinc-200/80 bg-white px-4 py-4 shadow-sm sm:px-6" />
@endsection
