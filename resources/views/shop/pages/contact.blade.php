@extends('layouts.shop')

@section('title', 'تماس با ما — '.$store['name'])

@section('content')
<div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-8 text-center">
        <span class="badge mb-3">تماس</span>
        <h1 class="text-2xl font-black text-shop-text sm:text-3xl">تماس با ما</h1>
        <p class="mt-2 text-sm text-shop-muted">سوال، پیشنهاد یا مشکل دارید؟ خوشحال می‌شویم بشنویم.</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-5">
        <div class="card space-y-5 p-6 lg:col-span-2">
            <h2 class="text-sm font-black text-shop-text">راه‌های ارتباطی</h2>
            @if($store['contactEmail'])
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-shop-primary/10 text-shop-primary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-shop-muted">ایمیل</p>
                        <a href="mailto:{{ $store['contactEmail'] }}" class="text-sm text-shop-primary hover:underline" dir="ltr">{{ $store['contactEmail'] }}</a>
                    </div>
                </div>
            @endif
            @if($store['contactPhone'])
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-shop-primary/10 text-shop-primary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-shop-muted">تلفن</p>
                        <p class="text-sm text-shop-text">{{ $store['contactPhone'] }}</p>
                    </div>
                </div>
            @endif
            @if($store['contactHours'])
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-shop-primary/10 text-shop-primary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-shop-muted">ساعات پاسخگویی</p>
                        <p class="text-sm text-shop-text">{{ $store['contactHours'] }}</p>
                    </div>
                </div>
            @endif
            @if($store['contactAddress'])
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-shop-primary/10 text-shop-primary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-shop-muted">آدرس</p>
                        <p class="text-sm text-shop-text">{{ $store['contactAddress'] }}</p>
                    </div>
                </div>
            @endif
            @if($store['whatsappUrl'])
                <a href="{{ $store['whatsappUrl'] }}" target="_blank" rel="noopener" class="btn-primary !w-full !justify-center !rounded-xl">
                    گفتگو در واتساپ
                </a>
            @endif
            @if($store['mapsUrl'])
                <a href="{{ $store['mapsUrl'] }}" target="_blank" rel="noopener" class="btn-secondary !w-full !justify-center !rounded-xl">
                    مشاهده روی نقشه
                </a>
            @endif
        </div>

        <form method="POST" action="{{ route('contact.store') }}" class="card space-y-4 p-6 lg:col-span-3">
            @csrf
            <h2 class="text-sm font-black text-shop-text">ارسال پیام</h2>
            <div>
                <label class="mb-1.5 block text-sm font-bold text-shop-text">نام</label>
                <input type="text" name="name" value="{{ old('name') }}" class="input-shop w-full px-3 py-2.5" placeholder="نام و نام خانوادگی" required>
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-shop-text">ایمیل</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="input-shop w-full px-3 py-2.5" placeholder="example@email.com" dir="ltr" required>
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-shop-text">تلفن</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" data-phone-input class="input-shop w-full px-3 py-2.5" placeholder="09121234567" dir="ltr">
                    @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-bold text-shop-text">موضوع</label>
                <input type="text" name="subject" value="{{ old('subject') }}" class="input-shop w-full px-3 py-2.5" required>
                @error('subject')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-bold text-shop-text">پیام</label>
                <textarea name="message" rows="4" class="input-shop w-full resize-y px-3 py-2.5" required>{{ old('message') }}</textarea>
                @error('message')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn-primary w-full !rounded-xl !py-3">ارسال پیام</button>
        </form>
    </div>
</div>
@endsection
