@extends('layouts.admin')

@section('header', 'مشاهده پیام')

@section('content')
@php
    use App\Enums\ReplyChannel;
    $hasPhone = (bool) ($message->phone ?? $message->user?->phone);
    $selectedChannel = old('channel', ReplyChannel::Panel->value);
@endphp

<div class="mb-4 flex flex-wrap items-center gap-3">
    <a href="{{ route('admin.messages.index') }}" class="admin-btn-secondary text-xs">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>
        بازگشت به لیست
    </a>
    @if($adjacent['newer'])
        <a href="{{ route('admin.messages.show', $adjacent['newer']) }}" class="admin-btn-secondary text-xs">پیام جدیدتر</a>
    @endif
    @if($adjacent['older'])
        <a href="{{ route('admin.messages.show', $adjacent['older']) }}" class="admin-btn-secondary text-xs">پیام قدیمی‌تر</a>
    @endif
    @if($message->user)
        <a href="{{ route('admin.users.show', $message->user) }}" class="admin-btn-secondary text-xs">پروفایل کاربر</a>
    @endif
</div>

<div class="admin-form-card">
    <div class="admin-card-header">
        <div class="min-w-0">
            <h2 class="admin-card-title truncate">{{ $message->subject }}</h2>
            <p class="mt-1 text-sm text-zinc-500">{{ format_jalali($message->created_at, 'Y/m/d H:i') }}</p>
        </div>
        @if($message->is_read)
            <span class="admin-badge-success">خوانده شده</span>
        @else
            <span class="admin-badge-warning">جدید</span>
        @endif
    </div>

    <div class="space-y-4 border-b border-zinc-100 p-5">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-zinc-100 bg-zinc-50/80 px-4 py-3">
                <p class="text-xs font-medium text-zinc-500">نام</p>
                <p class="mt-1 font-bold text-zinc-900">{{ $message->name }}</p>
            </div>
            <div class="rounded-xl border border-zinc-100 bg-zinc-50/80 px-4 py-3">
                <p class="text-xs font-medium text-zinc-500">ایمیل</p>
                <a href="mailto:{{ $message->email }}" class="mt-1 block font-bold text-indigo-600 hover:underline" dir="ltr">{{ $message->email }}</a>
            </div>
            @if($message->phone)
                <div class="rounded-xl border border-zinc-100 bg-zinc-50/80 px-4 py-3">
                    <p class="text-xs font-medium text-zinc-500">تلفن</p>
                    <a href="tel:{{ $message->phone }}" class="mt-1 block font-bold text-indigo-600 hover:underline" dir="ltr">{{ $message->phone }}</a>
                </div>
            @endif
        </div>
    </div>

    {{-- پیام اصلی با نمایش پیوست --}}
    <div class="border-b border-zinc-100 p-5">
        <div class="mb-3 flex items-center gap-2">
            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold">ک</span>
            <span class="text-xs font-medium text-zinc-500">کاربر: {{ $message->name }}</span>
            <span class="text-xs text-zinc-400">{{ format_jalali($message->created_at, 'Y/m/d H:i') }}</span>
        </div>
        <p class="text-sm text-zinc-800 leading-relaxed">{{ $message->message }}</p>
        @if($message->attachment)
            <div class="mt-3">
                <a href="{{ asset('storage/' . $message->attachment) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-indigo-50 px-3 py-2 text-sm text-indigo-600 hover:bg-indigo-100">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    مشاهده پیوست پیام اصلی
                </a>
            </div>
        @endif
    </div>

    {{-- پاسخ‌ها با نمایش پیوست --}}
    @foreach($message->replies as $reply)
        <div class="border-b border-zinc-100 p-5 {{ $reply->is_from_admin ? 'bg-emerald-50/30' : '' }}">
            <div class="mb-3 flex items-center gap-2">
                @if($reply->is_from_admin)
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">پ</span>
                    <span class="text-xs font-medium text-emerald-700">پشتیبانی</span>
                @else
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold">ک</span>
                    <span class="text-xs font-medium text-zinc-500">کاربر</span>
                @endif
                <span class="text-xs text-zinc-400">{{ format_jalali($reply->created_at, 'Y/m/d H:i') }}</span>
                @if($reply->channel?->label())
                    <span class="admin-badge-secondary text-xs">{{ $reply->channel->label() }}</span>
                @endif
            </div>
            <p class="text-sm text-zinc-800 leading-relaxed">{{ $reply->body }}</p>
            @if($reply->attachment)
                <div class="mt-3">
                    <a href="{{ asset('storage/' . $reply->attachment) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-indigo-50 px-3 py-2 text-sm text-indigo-600 hover:bg-indigo-100">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        مشاهده پیوست
                    </a>
                </div>
            @endif
        </div>
    @endforeach

    {{-- فرم پاسخ ادمین --}}
    <form method="POST" action="{{ route('admin.messages.reply', $message) }}" enctype="multipart/form-data" class="space-y-5 p-5" x-data="{ channel: @js($selectedChannel) }">
        @csrf

        <div>
            <p class="mb-3 text-sm font-medium text-zinc-700">روش ارسال پاسخ</p>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach(ReplyChannel::cases() as $option)
                    @php
                        $needsPhone = in_array($option, [ReplyChannel::PanelSms, ReplyChannel::Sms]);
                        $disabled = $needsPhone && ! $hasPhone;
                    @endphp
                    <label
                        class="relative flex rounded-xl border p-3 transition {{ $disabled ? 'cursor-not-allowed border-zinc-200 bg-zinc-50 opacity-60' : 'cursor-pointer border-zinc-200 hover:border-zinc-300' }}"
                        :class="!@js($disabled) && channel === @js($option->value) ? 'border-indigo-500 bg-indigo-50/50 ring-1 ring-indigo-500' : ''"
                    >
                        <input
                            type="radio"
                            name="channel"
                            value="{{ $option->value }}"
                            class="mt-1 shrink-0 text-indigo-600"
                            x-model="channel"
                            @checked($selectedChannel === $option->value)
                            @disabled($disabled)
                        >
                        <span class="ms-2.5 min-w-0">
                            <span class="block text-sm font-semibold text-zinc-900">
                                {{ $option->label() }}
                                @if($option === ReplyChannel::Panel)
                                    <span class="font-normal text-zinc-400">(پیش‌فرض)</span>
                                @endif
                            </span>
                            <span class="mt-0.5 block text-xs leading-5 text-zinc-500">{{ $option->description() }}</span>
                            @if($disabled)
                                <span class="mt-1 block text-xs text-amber-600">شماره موبایل ثبت نشده</span>
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>
            @error('channel')<p class="mt-2 text-sm text-red-500">{{ $errors->first('channel') }}</p>@enderror
        </div>

        <div>
            <label for="body" class="mb-1.5 block text-sm font-medium text-zinc-700">متن پاسخ</label>
            <textarea
                id="body"
                name="body"
                rows="4"
                class="admin-input w-full resize-y"
                placeholder="پاسخ خود را بنویسید..."
                required
            >{{ old('body') }}</textarea>
            @error('body')<p class="mt-1 text-sm text-red-500">{{ $errors->first('body') }}</p>@enderror
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

        <button type="submit" class="admin-btn-primary text-sm">ارسال پاسخ</button>
    </form>

    <div class="flex flex-wrap items-center gap-3 border-t border-zinc-100 px-5 py-4">
        <form method="POST" action="{{ route('admin.messages.unread', $message) }}">
            @csrf @method('PATCH')
            <button type="submit" class="admin-btn-secondary text-sm">علامت‌گذاری خوانده‌نشده</button>
        </form>
        <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" class="ms-auto">
            @csrf @method('DELETE')
            <button
                type="submit"
                data-confirm-message="آیا از حذف این پیام مطمئن هستید؟ این عمل قابل بازگشت نیست."
                data-confirm-title="تأیید حذف"
                data-confirm-variant="danger"
                class="text-red-500 text-sm hover:underline"
            >حذف پیام</button>
        </form>
    </div>
</div>
@endsection