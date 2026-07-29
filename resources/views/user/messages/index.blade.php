@extends('layouts.user')

@section('title', 'پیام‌های من')

@section('content')
<x-user.breadcrumb :items="[
    ['label' => 'داشبورد', 'url' => route('user.dashboard')],
    ['label' => 'پیام‌های من'],
]" />

<div class="user-section-head">
    <x-user.page-header
        class="!mb-0"
        title="پیام‌های من"
        subtitle="پیام‌های ارسالی و پاسخ‌های پشتیبانی در این بخش نمایش داده می‌شود."
    />
    <a href="{{ route('user.messages.create') }}" class="btn-primary !text-sm shrink-0">پیام جدید</a>
</div>

@if($messages->isEmpty())
    <x-user.empty-state
        icon="messages"
        title="هنوز پیامی ثبت نکرده‌اید"
        description="برای ارتباط با پشتیبانی فروشگاه، از صفحه تماس با ما پیام بفرستید."
        :action-url="route('pages.contact')"
        action-label="رفتن به تماس با ما"
    />
@else
    <div class="hidden md:block user-table-wrap overflow-x-auto">
        <table>
            <thead>
                <tr>
                    <th>موضوع</th>
                    <th>آخرین بروزرسانی</th>
                    <th>تعداد پاسخ</th>
                    <th>وضعیت</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($messages as $msg)
                    <tr @class(['bg-shop-primary/[0.04]' => $msg->has_unread_reply_for_user])>
                        <td class="font-bold">{{ $msg->subject }}</td>
                        <td class="text-shop-muted">{{ format_jalali($msg->last_replied_at ?? $msg->created_at, 'Y/m/d — H:i') }}</td>
                        <td>{{ $msg->replies_count }}</td>
                        <td>
                            @if($msg->has_unread_reply_for_user)
                                <span class="inline-flex shrink-0 items-center whitespace-nowrap rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-700">پاسخ جدید</span>
                            @elseif($msg->replies_count > 0)
                                <span class="inline-flex shrink-0 items-center whitespace-nowrap rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-700">پاسخ داده شده</span>
                            @else
                                <span class="inline-flex shrink-0 items-center whitespace-nowrap rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-bold text-zinc-600">در انتظار پاسخ</span>
                            @endif
                        </td>
                        <td><a href="{{ route('user.messages.show', $msg) }}" class="user-section-link">مشاهده گفتگو</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="space-y-3 md:hidden">
        @foreach($messages as $msg)
            <a href="{{ route('user.messages.show', $msg) }}" class="user-order-card block">
                <div class="mb-2 flex items-start justify-between gap-3">
                    <p class="font-black text-shop-text">{{ $msg->subject }}</p>
                    @if($msg->has_unread_reply_for_user)
                        <span class="shrink-0 rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-bold text-white">جدید</span>
                    @endif
                </div>
                <p class="text-xs text-shop-muted">{{ format_jalali($msg->last_replied_at ?? $msg->created_at, 'Y/m/d — H:i') }}</p>
                <p class="mt-2 text-sm text-shop-muted">{{ $msg->replies_count }} پاسخ</p>
            </a>
        @endforeach
    </div>

    @if($messages->hasPages())
        <div class="mt-6">{{ $messages->links() }}</div>
    @endif
@endif
@endsection
