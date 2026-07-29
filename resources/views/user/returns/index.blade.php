@extends('layouts.user')

@section('title', 'مرجوعی‌ها')

@section('content')
<x-user.breadcrumb :items="[
    ['label' => 'داشبورد', 'url' => route('user.dashboard')],
    ['label' => 'مرجوعی‌ها'],
]" />

<div class="user-section-head">
    <x-user.page-header
        class="!mb-0"
        title="درخواست‌های مرجوعی"
        subtitle="وضعیت درخواست‌های بازگشت کالا و استرداد وجه خود را پیگیری کنید."
    />
</div>

@if($returns->isEmpty())
    <x-user.empty-state
        icon="orders"
        title="درخواست مرجوعی ندارید"
        description="پس از تحویل سفارش، در صورت مشکل می‌توانید از جزئیات سفارش درخواست مرجوعی ثبت کنید."
        :action-url="route('user.orders.index')"
        action-label="مشاهده سفارشات"
    />
@else
    <div class="space-y-4">
        @foreach($returns as $return)
            <article class="user-review-card">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <a href="{{ route('user.orders.show', $return->order) }}" class="font-black text-shop-primary hover:underline">
                            سفارش {{ $return->order->order_number }}
                        </a>
                        <p class="mt-1 text-xs text-shop-muted">{{ format_jalali($return->created_at, 'Y/m/d — H:i') }}</p>
                    </div>
                    <x-user.status-badge :status="$return->status" />
                </div>
                <p class="mt-3 text-sm leading-relaxed text-shop-text">{{ $return->reason }}</p>
                @if($return->admin_note)
                    <div class="mt-3 rounded-2xl bg-shop-background px-4 py-3 text-sm text-shop-muted">
                        <span class="font-bold text-shop-text">پاسخ پشتیبانی:</span> {{ $return->admin_note }}
                    </div>
                @endif
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm">
                    <span class="text-shop-muted">مبلغ درخواستی: <span class="font-bold text-shop-primary">{{ format_price($return->refund_amount) }}</span></span>
                    @if($return->processed_at)
                        <span class="text-shop-muted">بررسی‌شده در {{ format_jalali($return->processed_at, 'Y/m/d') }}</span>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
    @if($returns->hasPages())
        <div class="mt-6">{{ $returns->links() }}</div>
    @endif
@endif
@endsection
