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

        <form method="POST" action="{{ route('contact.store') }}" enctype="multipart/form-data" class="card space-y-4 p-6 lg:col-span-3">
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
            
            <button type="submit" class="btn-primary w-full !rounded-xl !py-3">ارسال پیام</button>
        </form>
    </div>
</div>
@endsection