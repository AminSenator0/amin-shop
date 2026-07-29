@extends('layouts.admin')

@section('header', 'ویرایش اسلاید')

@section('content')
<form method="POST" action="{{ route('admin.sliders.update', $slider) }}" enctype="multipart/form-data" class="admin-card w-full">
    @csrf @method('PUT')
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">ویرایش اسلاید</h2>
            <p class="mt-0.5 text-xs text-zinc-500">{{ $slider->title }}</p>
        </div>
        <a href="{{ route('admin.sliders.index') }}" class="admin-btn-secondary text-xs">بازگشت به لیست</a>
    </div>
    <div class="space-y-6 p-6">
        @include('admin.sliders._form')
        <div class="flex flex-wrap gap-3 border-t border-zinc-100 pt-6">
            <button type="submit" class="admin-btn-primary">به‌روزرسانی اسلاید</button>
            <a href="{{ route('admin.sliders.index') }}" class="admin-btn-secondary">انصراف</a>
        </div>
    </div>
</form>
@endsection
