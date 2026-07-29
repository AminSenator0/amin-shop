@extends('layouts.admin')

@section('header', 'ویرایش کد تخفیف')

@section('content')
<form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="admin-form-card">
    @csrf
    @method('PUT')
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">ویرایش: <span dir="ltr" class="font-mono">{{ $coupon->code }}</span></h2>
            <p class="mt-0.5 text-xs text-zinc-500">
                {{ to_persian_digits((string) $coupon->used_count) }} بار استفاده شده
                @if($coupon->max_uses)
                    از {{ to_persian_digits((string) $coupon->max_uses) }}
                @endif
            </p>
        </div>
        <a href="{{ route('admin.coupons.index') }}" class="admin-btn-secondary text-xs">بازگشت به لیست</a>
    </div>
    <div class="space-y-6 p-6">
        @include('admin.coupons._form', ['coupon' => $coupon])
        <div class="flex flex-wrap gap-3 border-t border-zinc-100 pt-6">
            <button type="submit" class="admin-btn-primary">به‌روزرسانی</button>
            <a href="{{ route('admin.coupons.index') }}" class="admin-btn-secondary">انصراف</a>
        </div>
    </div>
</form>
@endsection
