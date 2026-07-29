@extends('layouts.admin')

@section('header', 'برندها')

@section('content')
<div class="admin-page-header">
    <x-admin.search-form placeholder="جستجوی برند..." />
    <a href="{{ route('admin.brands.create') }}" class="admin-btn-primary">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
        برند جدید
    </a>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>برند</th>
                <th>تعداد محصول</th>
                <th>وضعیت</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($brands as $brand)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            @if($brand->logo)
                                <img src="{{ asset('storage/'.$brand->logo) }}" class="h-9 w-9 rounded-lg object-contain border border-zinc-200 bg-white p-0.5" alt="">
                            @else
                                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-100 text-xs font-bold text-zinc-500">{{ mb_substr($brand->name, 0, 1) }}</span>
                            @endif
                            <span class="font-bold text-zinc-900">{{ $brand->name }}</span>
                        </div>
                    </td>
                    <td>{{ $brand->products_count }}</td>
                    <td>
                        @if($brand->is_active)
                            <span class="admin-badge-success">فعال</span>
                        @else
                            <span class="admin-badge-neutral">غیرفعال</span>
                        @endif
                    </td>
                    <td>
                        <x-admin.table-actions>
                            <x-admin.table-action type="edit" :href="route('admin.brands.edit', $brand)" />
                            <x-admin.table-action type="delete" :action="route('admin.brands.destroy', $brand)" />
                        </x-admin.table-actions>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<x-admin.pagination :paginator="$brands" />
@endsection
