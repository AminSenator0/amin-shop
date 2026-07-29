@extends('layouts.admin')

@section('header', 'برند جدید')

@section('content')
<form method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data" class="admin-form-card">
    @csrf
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">افزودن برند جدید</h2>
            <p class="mt-0.5 text-xs text-zinc-500">نام و لوگوی برند را وارد کنید</p>
        </div>
        <a href="{{ route('admin.brands.index') }}" class="admin-btn-secondary text-xs">بازگشت به لیست</a>
    </div>
    <div class="space-y-6 p-6">
        @include('admin.brands._form')
        <div class="flex flex-wrap gap-3 border-t border-zinc-100 pt-6">
            <button type="submit" class="admin-btn-primary">ذخیره برند</button>
            <a href="{{ route('admin.brands.index') }}" class="admin-btn-secondary">انصراف</a>
        </div>
    </div>
</form>
@endsection
