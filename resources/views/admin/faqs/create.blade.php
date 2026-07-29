@extends('layouts.admin')

@section('header', 'سوال متداول جدید')

@section('content')
<form method="POST" action="{{ route('admin.faqs.store') }}" class="admin-card w-full">
    @csrf
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">افزودن سوال متداول</h2>
            <p class="mt-0.5 text-xs text-zinc-500">نمایش در صفحه اصلی و صفحه FAQ</p>
        </div>
        <a href="{{ route('admin.faqs.index') }}" class="admin-btn-secondary text-xs">بازگشت به لیست</a>
    </div>
    <div class="space-y-6 p-6">
        @include('admin.faqs._form')
        <div class="flex flex-wrap gap-3 border-t border-zinc-100 pt-6">
            <button type="submit" class="admin-btn-primary">ذخیره</button>
            <a href="{{ route('admin.faqs.index') }}" class="admin-btn-secondary">انصراف</a>
        </div>
    </div>
</form>
@endsection
