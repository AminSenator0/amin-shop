@extends('layouts.admin')

@section('header', 'ویرایش برند')

@section('content')
<form method="POST" action="{{ route('admin.brands.update', $brand) }}" enctype="multipart/form-data" class="admin-form-card">
    @csrf @method('PUT')
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">ویرایش: {{ $brand->name }}</h2>
            <p class="mt-0.5 text-xs text-zinc-500">اطلاعات برند را به‌روزرسانی کنید</p>
        </div>
        <a href="{{ route('admin.brands.index') }}" class="admin-btn-secondary text-xs">بازگشت به لیست</a>
    </div>
    <div class="space-y-6 p-6">
        @include('admin.brands._form')
        <div class="flex flex-wrap gap-3 border-t border-zinc-100 pt-6">
            <button type="submit" class="admin-btn-primary">به‌روزرسانی</button>
            <a href="{{ route('admin.brands.index') }}" class="admin-btn-secondary">انصراف</a>
        </div>
    </div>
</form>
@endsection
