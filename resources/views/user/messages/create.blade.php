@extends('layouts.user')

@section('title', 'پیام جدید')

@section('content')
<x-user.breadcrumb :items="[
    ['label' => 'داشبورد', 'url' => route('user.dashboard')],
    ['label' => 'پیام‌های من', 'url' => route('user.messages.index')],
    ['label' => 'پیام جدید'],
]" />

<x-user.page-header
    title="ارسال پیام به پشتیبانی"
    subtitle="سوال، مشکل یا درخواست خود را بنویسید. پاسخ در همین بخش نمایش داده می‌شود."
/>

<form method="POST" action="{{ route('user.messages.store') }}" class="user-info-card space-y-5">
    @csrf
    <div>
        <label for="subject" class="mb-1.5 block text-sm font-bold text-shop-text">موضوع</label>
        <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required placeholder="مثلاً: پیگیری سفارش، سوال درباره محصول..." class="input-shop w-full px-4 py-3 text-sm">
        @error('subject')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="message" class="mb-1.5 block text-sm font-bold text-shop-text">متن پیام</label>
        <textarea name="message" id="message" rows="6" required placeholder="پیام خود را به فارسی بنویسید..." class="input-shop w-full px-4 py-3 text-sm">{{ old('message') }}</textarea>
        @error('message')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div class="flex flex-wrap gap-3">
        <button type="submit" class="btn-primary !text-sm">ارسال پیام</button>
        <a href="{{ route('user.messages.index') }}" class="btn-secondary !text-sm">انصراف</a>
    </div>
</form>
@endsection
