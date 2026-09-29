@extends('layouts.admin')

@section('title', 'آمار مقالات')
@section('header', 'آمار مقالات')

@section('content')
    @include('admin.analytics._tabs')

    {{-- ─── ۲۰ مقاله پربازدید ────────────────────────────── --}}
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">۲۰ مقاله پربازدید</h2>
            <span class="text-xs text-zinc-500">بر اساس بازدید کاربران از صفحه مقاله</span>
        </div>

        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="w-14 text-center">رتبه</th>
                        <th>عنوان مقاله</th>
                        <th class="w-32 text-center">بازدید</th>
                        <th class="w-44">آخرین بروزرسانی</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topPosts as $index => $post)
                        @php
                            $rank = $index + 1;
                            $rankClass = match (true) {
                                $rank === 1 => 'bg-amber-400 text-amber-950',
                                $rank === 2 => 'bg-zinc-300 text-zinc-800',
                                $rank === 3 => 'bg-orange-300 text-orange-950',
                                default => 'bg-zinc-100 text-zinc-500',
                            };
                        @endphp
                        <tr>
                            <td class="text-center">
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold {{ $rankClass }}">
                                    {{ $rank }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('blog.show', $post->slug) }}" target="_blank"
                                   class="font-semibold text-zinc-800 hover:text-[var(--surface-primary)] transition">
                                    {{ $post->title }}
                                </a>
                            </td>
                            <td class="text-center">
                                <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700 ring-1 ring-indigo-200">
                                    {{ number_format($post->views) }} بازدید
                                </span>
                            </td>
                            <td class="text-xs text-zinc-500">{{ format_jalali($post->updated_at, 'Y/m/d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-sm text-zinc-400">
                                هنوز بازدیدی برای مقالات ثبت نشده است.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection