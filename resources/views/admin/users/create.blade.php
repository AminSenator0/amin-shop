@extends('layouts.admin')

@section('header', 'کاربر جدید')

@section('content')
<form method="POST" action="{{ route('admin.users.store') }}" class="admin-form-card max-w-2xl">
    @csrf
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">ایجاد کاربر جدید</h2>
            <p class="mt-0.5 text-xs text-zinc-500">حساب مشتری یا مدیر را از پنل مدیریت بسازید</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="admin-btn-secondary text-xs">بازگشت به لیست</a>
    </div>
    <div class="space-y-5 p-6">
        <div>
            <label for="name" class="admin-field-label">نام <span class="text-rose-500">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required class="admin-input w-full">
            @error('name')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="admin-field-label">ایمیل <span class="text-rose-500">*</span></label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required class="admin-input w-full" dir="ltr">
            @error('email')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="phone" class="admin-field-label">تلفن</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="۰۹۱۲۳۴۵۶۷۸۹" data-phone-input class="admin-input w-full" dir="ltr" inputmode="numeric">
            @error('phone')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="role" class="admin-field-label">نقش <span class="text-rose-500">*</span></label>
            <select id="role" name="role" class="admin-select w-full">
                @foreach(\App\Enums\UserRole::cases() as $role)
                    <option value="{{ $role->value }}" @selected(old('role', 'customer') === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>
            @error('role')<p class="admin-field-error">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="password" class="admin-field-label">رمز عبور <span class="text-rose-500">*</span></label>
                <input type="password" id="password" name="password" required class="admin-input w-full" dir="ltr" autocomplete="new-password">
                @error('password')<p class="admin-field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="admin-field-label">تکرار رمز عبور <span class="text-rose-500">*</span></label>
                <input type="password" id="password_confirmation" name="password_confirmation" required class="admin-input w-full" dir="ltr" autocomplete="new-password">
            </div>
        </div>

        <div class="flex flex-wrap gap-3 border-t border-zinc-100 pt-6">
            <button type="submit" class="admin-btn-primary">ایجاد کاربر</button>
            <a href="{{ route('admin.users.index') }}" class="admin-btn-secondary">انصراف</a>
        </div>
    </div>
</form>
@endsection
