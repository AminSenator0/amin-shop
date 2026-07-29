@extends('layouts.admin')

@section('header', 'بنرها')

@section('content')
<div class="admin-page-header">
    <x-admin.search-form placeholder="جستجوی بنر..." />
    <a href="{{ route('admin.banners.create') }}" class="admin-btn-primary">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
        بنر جدید
    </a>
</div>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="text-right px-4 py-3">عنوان</th>
                <th class="text-right px-4 py-3">موقعیت</th>
                <th class="text-right px-4 py-3">وضعیت</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($banners as $banner)
                <tr class="border-t">
                    <td class="px-4 py-3 font-medium">{{ $banner->title }}</td>
                    <td class="px-4 py-3">{{ $banner->position }}</td>
                    <td class="px-4 py-3">{{ $banner->is_active ? 'فعال' : 'غیرفعال' }}</td>
                    <td class="px-4 py-3">
                        <x-admin.table-actions>
                            <x-admin.table-action type="edit" :href="route('admin.banners.edit', $banner)" />
                            <x-admin.table-action type="delete" :action="route('admin.banners.destroy', $banner)" />
                        </x-admin.table-actions>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<x-admin.pagination :paginator="$banners" />
@endsection
