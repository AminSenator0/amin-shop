@extends('layouts.admin')

@section('header', 'ویرایش روش ارسال')

@section('content')
<form method="POST" action="{{ route('admin.shipping.update', $shipping) }}" class="admin-form-card">
    @csrf
    @method('PUT')
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">ویرایش: {{ $shipping->name }}</h2>
            <p class="mt-0.5 text-xs text-zinc-500">
                @if($shipping->estimated_days)
                    تحویل تقریبی {{ to_persian_digits((string) $shipping->estimated_days) }} روز کاری
                @else
                    بدون زمان تحویل مشخص
                @endif
                · {{ $shipping->is_active ? 'فعال در فروشگاه' : 'غیرفعال' }}
            </p>
        </div>
        <a href="{{ route('admin.shipping.index') }}" class="admin-btn-secondary text-xs">بازگشت به لیست</a>
    </div>

    @include('admin.shipping._form', ['shipping' => $shipping])

    <div class="flex flex-wrap gap-3 border-t border-zinc-100 px-6 py-5">
        <button type="submit" class="admin-btn-primary">به‌روزرسانی</button>
        <a href="{{ route('admin.shipping.index') }}" class="admin-btn-secondary">انصراف</a>
    </div>
</form>
@endsection
