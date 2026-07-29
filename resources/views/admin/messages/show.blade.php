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

    <div class="border-b border-zinc-100 p-5">
        <h3 class="mb-4 text-sm font-bold text-zinc-900">گفتگو</h3>
        <x-message-thread :message="$message" :show-channel-badge="true" />
    </div>

    <form method="POST" action="{{ route('admin.messages.reply', $message) }}" class="space-y-5 p-5" x-data="{ channel: @js($selectedChannel) }">
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
