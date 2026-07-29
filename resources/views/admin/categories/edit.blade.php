@extends('layouts.admin')

@section('header', 'ویرایش دسته‌بندی')

@section('content')
<form method="POST" action="{{ route('admin.categories.update', $category) }}" class="admin-form-card">
    @csrf
    @method('PUT')
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">ویرایش: {{ $category->name }}</h2>
            <p class="mt-0.5 text-xs text-zinc-500">تغییرات را ذخیره کنید یا به لیست برگردید</p>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="admin-btn-secondary text-xs">بازگشت به لیست</a>
    </div>
    <div class="space-y-6 p-6">
        @include('admin.categories._form', ['category' => $category])
        <div class="flex flex-wrap gap-3 border-t border-zinc-100 pt-6">
            <button type="submit" class="admin-btn-primary">به‌روزرسانی</button>
            <a href="{{ route('admin.categories.index') }}" class="admin-btn-secondary">انصراف</a>
        </div>
    </div>
</form>
@endsection
