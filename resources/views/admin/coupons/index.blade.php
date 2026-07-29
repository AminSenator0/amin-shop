@extends('layouts.admin')

@section('header', 'کدهای تخفیف')

@section('content')
<div class="admin-page-header">
    <x-admin.search-form placeholder="جستجوی کد تخفیف..." />
    <a href="{{ route('admin.coupons.create') }}" class="admin-btn-primary">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
        کد جدید
    </a>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>کد</th>
                <th>نوع</th>
                <th>مقدار</th>
                <th>استفاده</th>
                <th>انقضا</th>
                <th>وضعیت</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($coupons as $coupon)
                <tr>
                    <td>
                        <span class="inline-flex items-center rounded-lg bg-zinc-100 px-2.5 py-1 font-mono text-xs font-bold text-zinc-800" dir="ltr">
                            {{ $coupon->code }}
                        </span>
                    </td>
                    <td>{{ $coupon->typeLabel() }}</td>
                    <td>
                        @if($coupon->type === 'fixed')
                            {{ format_price($coupon->value) }}
                        @else
                            {{ format_number($coupon->value) }}٪
                        @endif
                    </td>
                    <td dir="ltr">
                        {{ to_persian_digits((string) $coupon->used_count) }}{{ $coupon->max_uses ? '/'.to_persian_digits((string) $coupon->max_uses) : '' }}
                    </td>
                    <td>
                        @if($coupon->expires_at)
                            {{ format_jalali($coupon->expires_at) }}
                        @else
                            <span class="text-zinc-400">—</span>
                        @endif
                    </td>
                    <td>
                        @if($coupon->is_active)
                            <span class="admin-badge-success">فعال</span>
                        @else
                            <span class="admin-badge-neutral">غیرفعال</span>
                        @endif
                    </td>
                    <td>
                        <x-admin.table-actions>
                            <x-admin.table-action type="edit" :href="route('admin.coupons.edit', $coupon)" />
                            <x-admin.table-action type="delete" :action="route('admin.coupons.destroy', $coupon)" />
                        </x-admin.table-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-zinc-500">
                        هنوز کد تخفیفی ثبت نشده است.
                        <a href="{{ route('admin.coupons.create') }}" class="mr-1 font-bold text-indigo-600 hover:underline">ساخت اولین کد</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<x-admin.pagination :paginator="$coupons" />
@endsection
