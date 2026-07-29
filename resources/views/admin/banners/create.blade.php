@extends('layouts.admin')

@section('header', 'بنر جدید')

@section('content')
<form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data" class="admin-card w-full">
    @csrf
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">افزودن بنر جدید</h2>
            <p class="mt-0.5 text-xs text-zinc-500">بنر تبلیغاتی صفحه اصلی فروشگاه</p>
        </div>
        <a href="{{ route('admin.banners.index') }}" class="admin-btn-secondary text-xs">بازگشت به لیست</a>
    </div>
    <div class="space-y-6 p-6">
        @include('admin.banners._form')
        <div class="flex flex-wrap gap-3 border-t border-zinc-100 pt-6">
            <button type="submit" class="admin-btn-primary">ذخیره بنر</button>
            <a href="{{ route('admin.banners.index') }}" class="admin-btn-secondary">انصراف</a>
        </div>
    </div>
</form>
@endsection
