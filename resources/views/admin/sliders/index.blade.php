@extends('layouts.admin')

@section('header', 'اسلایدرها')

@section('content')
<div class="admin-page-header">
    <x-admin.search-form placeholder="جستجوی اسلاید..." />
    <a href="{{ route('admin.sliders.create') }}" class="admin-btn-primary">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
        اسلاید جدید
    </a>
</div>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="text-right px-4 py-3">عنوان</th>
                <th class="text-right px-4 py-3">ترتیب</th>
                <th class="text-right px-4 py-3">وضعیت</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($sliders as $slider)
                <tr class="border-t">
                    <td class="px-4 py-3 font-medium">{{ $slider->title }}</td>
                    <td class="px-4 py-3">{{ $slider->sort_order }}</td>
                    <td class="px-4 py-3">{{ $slider->is_active ? 'فعال' : 'غیرفعال' }}</td>
                    <td class="px-4 py-3">
                        <x-admin.table-actions>
                            <x-admin.table-action type="edit" :href="route('admin.sliders.edit', $slider)" />
                            <x-admin.table-action type="delete" :action="route('admin.sliders.destroy', $slider)" />
                        </x-admin.table-actions>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<x-admin.pagination :paginator="$sliders" />
@endsection
