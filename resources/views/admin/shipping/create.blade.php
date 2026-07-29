@extends('layouts.admin')

@section('header', 'روش ارسال جدید')

@section('content')
<form method="POST" action="{{ route('admin.shipping.store') }}" class="admin-form-card">
    @csrf
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">افزودن روش ارسال جدید</h2>
            <p class="mt-0.5 text-xs text-zinc-500">نام، هزینه و شرایط تحویل را مشخص کنید</p>
        </div>
        <a href="{{ route('admin.shipping.index') }}" class="admin-btn-secondary text-xs">بازگشت به لیست</a>
    </div>

    @include('admin.shipping._form', ['presets' => $presets])

    <div class="flex flex-wrap gap-3 border-t border-zinc-100 px-6 py-5">
        <button type="submit" class="admin-btn-primary">ذخیره روش ارسال</button>
        <a href="{{ route('admin.shipping.index') }}" class="admin-btn-secondary">انصراف</a>
    </div>
</form>
@endsection
