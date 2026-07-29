@extends('layouts.admin')

@section('header', 'دسته‌بندی‌ها')

@section('content')
<div class="admin-page-header">
    <x-admin.search-form placeholder="جستجوی دسته‌بندی..." />
    <a href="{{ route('admin.categories.create') }}" class="admin-btn-primary">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
        دسته جدید
    </a>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>نام</th>
                <th>ترتیب</th>
                <th>تعداد محصول</th>
                <th>وضعیت</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-sm font-bold text-indigo-600 ring-1 ring-indigo-100">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <span class="font-bold text-zinc-900">{{ $category->name }}</span>
                                @if($category->description)
                                    <p class="truncate text-xs text-zinc-500 max-w-xs">{{ $category->description }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="inline-flex items-center rounded-lg bg-zinc-100 px-2.5 py-1 text-xs font-bold text-zinc-600" dir="ltr">
                            {{ to_persian_digits((string) $category->sort_order) }}
                        </span>
                    </td>
                    <td>{{ to_persian_digits((string) $category->products_count) }}</td>
                    <td>
                        @if($category->is_active)
                            <span class="admin-badge-success">فعال</span>
                        @else
                            <span class="admin-badge-neutral">غیرفعال</span>
                        @endif
                    </td>
                    <td>
                        <x-admin.table-actions>
                            <x-admin.table-action type="edit" :href="route('admin.categories.edit', $category)" />
                            <x-admin.table-action type="delete" :action="route('admin.categories.destroy', $category)" />
                        </x-admin.table-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-sm text-zinc-500">
                        هنوز دسته‌بندی‌ای ثبت نشده است.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<x-admin.pagination :paginator="$categories" />
@endsection
