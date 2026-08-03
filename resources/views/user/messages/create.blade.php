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

<form method="POST" action="{{ route('user.messages.store') }}" enctype="multipart/form-data" class="user-info-card space-y-5">
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
    
    <div class="file-upload-wrapper" x-data="fileUpload()">
    <label class="mb-1.5 block text-sm font-bold text-shop-text">فایل پیوست (اختیاری)</label>
    
    <div 
        @dragover.prevent="dragOver = true"
        @dragleave.prevent="dragOver = false"
        @drop.prevent="handleDrop($event)"
        :class="{'border-emerald-400 bg-emerald-50/10': dragOver, 'border-shop-border': !dragOver}"
        class="relative cursor-pointer rounded-xl border-2 border-dashed p-6 transition-all duration-300 hover:border-shop-primary hover:bg-shop-primary/5"
    >
        <input 
            type="file" 
            name="attachment" 
            @change="handleFile($event.target.files[0])"
            class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
        >
        
        <!-- Empty State -->
        <div x-show="!file" class="flex flex-col items-center gap-3">
        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-shop-primary shadow-lg">
        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l-3.75 3.75M12 9.75l3.75 3.75M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </div>
            <div class="text-center">
                <p class="text-sm font-semibold text-shop-text">انتخاب فایل</p>
                <p class="text-xs text-shop-muted">یا فایل را اینجا رها کنید</p>
            </div>
            <p class="text-[11px] text-shop-muted/70">JPG, PNG, PDF, DOC — حداکثر ۵ مگابایت</p>
        </div>

        <!-- Drag Active -->
        <div x-show="dragOver && !file" class="flex flex-col items-center gap-2">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-500/20 animate-bounce">
                <svg class="h-7 w-7 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                </svg>
            </div>
            <p class="text-sm font-bold text-emerald-500">رها کنید!</p>
        </div>
    </div>

    <!-- File Preview -->
    <div x-show="file" x-transition class="mt-3 overflow-hidden rounded-xl border border-shop-border bg-shop-background p-4">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-shop-border overflow-hidden">
                <img x-show="preview" :src="preview" class="h-full w-full object-cover">
                <svg x-show="!preview" class="h-6 w-6 text-shop-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p x-text="fileName" class="truncate text-sm font-semibold text-shop-text"></p>
                <p x-text="fileSize" class="text-xs text-shop-muted"></p>
            </div>
            <button @click="removeFile()" type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-shop-muted hover:bg-rose-50 hover:text-rose-500 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="mt-3">
            <div class="h-1.5 w-full overflow-hidden rounded-full bg-shop-border">
                <div :style="`width: ${progress}%`" class="h-full rounded-full bg-gradient-to-r from-shop-primary to-violet-500 transition-all duration-300"></div>
            </div>
            <div class="mt-1 flex justify-between">
                <span x-text="progressText" class="text-[11px] text-shop-muted"></span>
                <span x-text="progress + '%'" class="text-[11px] font-bold text-shop-primary"></span>
            </div>
        </div>
    </div>

    @error('attachment')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>

<script>
function fileUpload() {
    return {
        file: null, preview: null, fileName: '', fileSize: '', progress: 0, progressText: 'در حال آپلود...', dragOver: false,
        handleFile(file) {
            if (!file) return;
            this.file = file; this.fileName = file.name; this.fileSize = this.formatBytes(file.size); this.progress = 0; this.progressText = 'در حال آپلود...';
            if (file.type.startsWith('image/')) { const r = new FileReader(); r.onload = (e) => this.preview = e.target.result; r.readAsDataURL(file); } else this.preview = null;
            let w = 0; const i = setInterval(() => { w += Math.random() * 15 + 5; if (w >= 100) { w = 100; clearInterval(i); this.progressText = 'آپلود تکمیل شد'; } this.progress = Math.floor(w); }, 150);
        },
        handleDrop(e) { this.dragOver = false; const f = e.dataTransfer.files[0]; if (f) this.handleFile(f); },
        removeFile() { this.file = null; this.preview = null; this.progress = 0; const inp = document.querySelector('input[name="attachment"]'); if (inp) inp.value = ''; },
        formatBytes(b) { if (b === 0) return '۰ بایت'; const k = 1024, s = ['بایت', 'کیلوبایت', 'مگابایت'], i = Math.floor(Math.log(b) / Math.log(k)); return parseFloat((b / Math.pow(k, i)).toFixed(1)).toLocaleString('fa-IR') + ' ' + s[i]; }
    }
}
</script>
    
    <div class="flex flex-wrap gap-3">
        <button type="submit" class="btn-primary !text-sm">ارسال پیام</button>
        <a href="{{ route('user.messages.index') }}" class="btn-secondary !text-sm">انصراف</a>
    </div>
</form>
@endsection