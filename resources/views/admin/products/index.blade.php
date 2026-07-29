@extends('layouts.admin')

@section('header', 'محصولات')

@section('content')
<div class="admin-page-header">
    <x-admin.search-form placeholder="جستجوی محصول...">
        @if(request('low_stock'))
            <input type="hidden" name="low_stock" value="1">
            <span class="admin-badge-danger">فقط موجودی کم</span>
        @endif
    </x-admin.search-form>
    <a href="{{ route('admin.products.create') }}" class="admin-btn-primary">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
        محصول جدید
    </a>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>نام</th>
                <th>دسته</th>
                <th>برند</th>
                <th>قیمت</th>
                <th>موجودی</th>
                <th>وضعیت</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
                <tr>
                    <td class="font-bold text-zinc-900">{{ $product->name }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td>{{ $product->brand?->name ?? '—' }}</td>
                    <td class="font-bold">{{ format_price($product->price) }}</td>
                    <td>
                        @if($product->stock < 5)
                            <span class="admin-badge-danger">{{ $product->stock }}</span>
                        @else
                            {{ $product->stock }}
                        @endif
                    </td>
                    <td>
                        @if($product->is_active)
                            <span class="admin-badge-success">فعال</span>
                        @else
                            <span class="admin-badge-neutral">غیرفعال</span>
                        @endif
                    </td>
                    <td>
                        <x-admin.table-actions>
                            <x-admin.table-action type="edit" :href="route('admin.products.edit', $product)" />
                            <x-admin.table-action type="delete" :action="route('admin.products.destroy', $product)" />
                        </x-admin.table-actions>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<x-admin.pagination :paginator="$products" />
@endsection
