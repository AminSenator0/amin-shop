@extends('layouts.admin')

@section('header', 'نظرات')

@section('content')
@php
    $activeStatus = request('status', request('pending') ? 'pending' : '');
    $filterParams = array_filter([
        'search' => request('search'),
        'rating' => request('rating'),
    ]);
@endphp

<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <x-admin.stat-card
        label="کل نظرات"
        :value="$stats['total']"
        color="violet"
        :href="route('admin.reviews.index', $filterParams)"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>'
    />
    <x-admin.stat-card
        label="در انتظار تایید"
        :value="$stats['pending']"
        color="amber"
        :href="route('admin.reviews.index', array_merge($filterParams, ['status' => 'pending']))"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
    />
    <x-admin.stat-card
        label="تایید شده"
        :value="$stats['approved']"
        color="emerald"
        :href="route('admin.reviews.index', array_merge($filterParams, ['status' => 'approved']))"
        icon='<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
    />
</div>

<x-admin.review-filters :stats="$stats" :active-status="$activeStatus" :filter-params="$filterParams" />

<div class="space-y-3">
    @forelse($reviews as $review)
        <article class="admin-card !overflow-visible transition-shadow hover:shadow-md {{ ! $review->is_approved ? 'ring-1 ring-amber-200' : '' }}">
            <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex min-w-0 flex-1 gap-3.5">
                    <div class="admin-user-avatar shrink-0">{{ mb_substr($review->user->name, 0, 1) }}</div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                            <a href="{{ route('admin.users.show', $review->user) }}" class="font-bold text-zinc-900 transition hover:text-indigo-600">
                                {{ $review->user->name }}
                            </a>

                            <x-admin.star-rating :rating="$review->rating" />

                            @if($review->is_approved)
                                <span class="admin-badge-success inline-flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    تایید شده
                                </span>
                            @else
                                <span class="admin-badge-warning inline-flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    در انتظار
                                </span>
                            @endif
                        </div>

                        <a
                            href="{{ route('products.show', $review->product->slug) }}"
                            target="_blank"
                            class="mt-1.5 inline-flex max-w-full items-center gap-1.5 text-sm text-zinc-500 transition hover:text-indigo-600"
                        >
                            <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span class="truncate">{{ $review->product->name }}</span>
                        </a>

                        @if($review->comment)
                            <div class="mt-3 flex gap-2.5 rounded-xl border border-zinc-100 bg-zinc-50/80 px-3.5 py-3">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.707-3.228C21.75 9.294 18.294 6 14.25 6h-2.25c-4.044 0-7.5 3.294-7.5 7.5z" />
                                </svg>
                                <x-expandable-text :text="$review->comment" :limit="200" class="min-w-0 flex-1" />
                            </div>
                        @else
                            <p class="mt-2 text-sm italic text-zinc-400">بدون متن نظر</p>
                        @endif
                    </div>
                </div>

                <div class="flex shrink-0 items-center justify-between gap-4 border-t border-zinc-100 pt-4 sm:flex-col sm:items-end sm:border-t-0 sm:pt-0">
                    <time class="inline-flex items-center gap-1.5 text-xs text-zinc-400" datetime="{{ $review->created_at->toIso8601String() }}">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        {{ format_jalali($review->created_at, 'Y/m/d H:i') }}
                    </time>

                    <x-admin.review-actions :review="$review" />
                </div>
            </div>
        </article>
    @empty
        <div class="admin-card flex flex-col items-center justify-center px-6 py-16 text-center">
            <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-400">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </div>
            <p class="font-bold text-zinc-700">نظری با این فیلترها یافت نشد</p>
            <p class="mt-1 text-sm text-zinc-500">فیلترها را تغییر دهید یا جستجوی دیگری انجام دهید.</p>
        </div>
    @endforelse
</div>

<x-admin.pagination :paginator="$reviews" class="mt-6 rounded-2xl border border-zinc-200/80 bg-white px-4 py-4 shadow-sm sm:px-6" />
@endsection
