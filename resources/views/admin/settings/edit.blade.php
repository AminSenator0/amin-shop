@extends('layouts.admin')

@section('header', 'تنظیمات فروشگاه')

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="admin-card w-full">
    @csrf
    @method('PUT')

    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">تنظیمات فروشگاه</h2>
            <p class="mt-0.5 text-xs text-zinc-500">نام، ظاهر، واحد پول و اطلاعات تماس — در سراسر فروشگاه و فاکتورها اعمال می‌شود</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="admin-btn-secondary text-xs">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                مشاهده فروشگاه
            </a>
            <a href="{{ route('pages.contact') }}" target="_blank" rel="noopener" class="admin-btn-secondary text-xs">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
                صفحه تماس
            </a>
        </div>
    </div>

    <div class="p-6">
        @include('admin.settings._form')

        <div class="mt-6 flex flex-wrap gap-3 border-t border-zinc-100 pt-6">
            <button type="submit" class="admin-btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                ذخیره تنظیمات
            </button>
        </div>
    </div>
</form>
@endsection
