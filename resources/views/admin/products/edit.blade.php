@extends('layouts.admin')

@section('header', 'ویرایش محصول')

@section('content')
<form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="admin-card w-full">
    @csrf @method('PUT')
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">ویرایش: {{ $product->name }}</h2>
            <p class="mt-0.5 text-xs text-zinc-500">تغییرات را اعمال کنید و ذخیره نمایید</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="admin-btn-secondary text-xs">بازگشت به لیست</a>
    </div>
    <div class="space-y-6 p-6">
        @include('admin.products._form')
        <div class="flex flex-wrap gap-3 border-t border-zinc-100 pt-6">
            <button type="submit" class="admin-btn-primary">به‌روزرسانی محصول</button>
            <a href="{{ route('admin.products.index') }}" class="admin-btn-secondary">انصراف</a>
        </div>
    </div>
</form>
@endsection
