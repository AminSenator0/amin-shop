@extends('layouts.admin')

@section('header', 'مقالات')

@section('content')
<div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
    <a href="{{ route('admin.homepage.index') }}" class="text-zinc-500 hover:text-indigo-600">صفحه اصلی</a>
    <span class="text-zinc-300">/</span>
    <span class="font-bold text-zinc-700">مقالات</span>
</div>
<div class="admin-page-header">
    <x-admin.search-form placeholder="جستجوی مقاله..." />
    <a href="{{ route('admin.blog-posts.create') }}" class="admin-btn-primary">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
        مقاله جدید
    </a>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>عنوان</th>
                <th>تاریخ</th>
                <th>وضعیت</th>
                <th>نمایش</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($posts as $post)
                <tr>
                    <td class="font-medium">{{ $post->title }}</td>
                    <td class="text-zinc-500">{{ $post->published_at ? format_jalali($post->published_at) : '—' }}</td>
                    <td>
                        @if($post->is_published)
                            <span class="admin-badge-success">منتشر</span>
                        @else
                            <span class="admin-badge-neutral">پیش‌نویس</span>
                        @endif
                    </td>
                    <td>
                        @if($post->is_published)
                            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener" class="text-xs font-bold text-indigo-600 hover:underline">مشاهده</a>
                        @else
                            <span class="text-xs text-zinc-400">—</span>
                        @endif
                    </td>
                    <td>
                        <x-admin.table-actions>
                            <x-admin.table-action type="edit" :href="route('admin.blog-posts.edit', $post)" />
                            <x-admin.table-action type="delete" :action="route('admin.blog-posts.destroy', $post)" />
                        </x-admin.table-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="admin-table-empty">مقاله‌ای ثبت نشده است.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<x-admin.pagination :paginator="$posts" />
@endsection
