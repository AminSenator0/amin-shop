@extends('layouts.admin')

@section('header', 'کد تخفیف جدید')

@section('content')
<form method="POST" action="{{ route('admin.coupons.store') }}" class="admin-form-card">
    @csrf
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">ساخت کد تخفیف جدید</h2>
            <p class="mt-0.5 text-xs text-zinc-500">کد، نوع تخفیف و محدودیت‌های استفاده را مشخص کنید</p>
        </div>
        <a href="{{ route('admin.coupons.index') }}" class="admin-btn-secondary text-xs">بازگشت به لیست</a>
    </div>
    <div class="space-y-6 p-6">
        @include('admin.coupons._form')
        <div class="flex flex-wrap gap-3 border-t border-zinc-100 pt-6">
            <button type="submit" class="admin-btn-primary">ذخیره کد تخفیف</button>
            <a href="{{ route('admin.coupons.index') }}" class="admin-btn-secondary">انصراف</a>
        </div>
    </div>
</form>
@endsection
