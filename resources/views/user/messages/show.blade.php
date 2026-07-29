@extends('layouts.user')

@section('title', $message->subject)

@section('content')
<x-user.breadcrumb :items="[
    ['label' => 'داشبورد', 'url' => route('user.dashboard')],
    ['label' => 'پیام‌های من', 'url' => route('user.messages.index')],
    ['label' => $message->subject],
]" />

<div class="user-info-card overflow-hidden !p-0">
    <div class="border-b border-shop-border/60 px-6 py-5">
        <h1 class="text-xl font-black text-shop-text">{{ $message->subject }}</h1>
        <p class="mt-1.5 text-sm text-shop-muted">ارسال‌شده در {{ format_jalali($message->created_at, 'Y/m/d — H:i') }}</p>
    </div>

    <div class="p-6">
        <x-message-thread :message="$message" variant="user" />
    </div>

    <form method="POST" action="{{ route('user.messages.reply', $message) }}" class="border-t border-shop-border/60 bg-shop-background/60 p-6">
        @csrf
        <label for="body" class="mb-2 block text-sm font-bold text-shop-text">پاسخ شما</label>
        <textarea
            id="body"
            name="body"
            rows="4"
            class="input-shop w-full px-4 py-3 text-sm"
            placeholder="اگر سوال یا توضیح بیشتری دارید اینجا بنویسید..."
            required
        >{{ old('body') }}</textarea>
        @error('body')<p class="mt-1.5 text-sm text-rose-600">{{ $errors->first('body') }}</p>@enderror
        <button type="submit" class="btn-primary mt-4 !text-sm">ارسال پیام</button>
    </form>
</div>
@endsection
