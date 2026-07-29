@extends('layouts.admin')

@section('header', 'روش‌های ارسال')

@section('content')
<div class="admin-page-header">
    <x-admin.search-form placeholder="جستجوی روش ارسال..." />
    <a href="{{ route('admin.shipping.create') }}" class="admin-btn-primary">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
        روش جدید
    </a>
</div>
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="text-right px-4 py-3">نام</th>
                <th class="text-right px-4 py-3">هزینه</th>
                <th class="text-right px-4 py-3">ارسال رایگان بالای</th>
                <th class="text-right px-4 py-3">وضعیت</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($methods as $method)
                <tr class="border-t">
                    <td class="px-4 py-3 font-medium">{{ $method->name }}</td>
                    <td class="px-4 py-3">{{ format_price($method->cost) }}</td>
                    <td class="px-4 py-3">{{ $method->free_above ? format_price($method->free_above) : '—' }}</td>
                    <td class="px-4 py-3">{{ $method->is_active ? 'فعال' : 'غیرفعال' }}</td>
                    <td class="px-4 py-3">
                        <x-admin.table-actions>
                            <x-admin.table-action type="edit" :href="route('admin.shipping.edit', $method)" />
                            <x-admin.table-action type="delete" :action="route('admin.shipping.destroy', $method)" />
                        </x-admin.table-actions>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<x-admin.pagination :paginator="$methods" />
@endsection
