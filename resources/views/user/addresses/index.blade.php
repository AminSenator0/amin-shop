@extends('layouts.user')

@section('title', 'آدرس‌ها')

@section('content')
<x-user.breadcrumb :items="[
    ['label' => 'داشبورد', 'url' => route('user.dashboard')],
    ['label' => 'آدرس‌ها'],
]" />

<div class="user-section-head">
    <x-user.page-header
        class="!mb-0"
        title="آدرس‌های من"
        subtitle="آدرس‌های ذخیره‌شده برای تسویه حساب سریع‌تر و راحت‌تر."
    />
    <a href="{{ route('user.addresses.create') }}" class="btn-primary !text-sm shrink-0">افزودن آدرس جدید</a>
</div>

@if($addresses->isEmpty())
    <x-user.empty-state
        icon="location"
        title="آدرسی ثبت نشده است"
        description="برای ثبت سفارش سریع‌تر، آدرس تحویل خود را اضافه کنید."
        :action-url="route('user.addresses.create')"
        action-label="افزودن اولین آدرس"
    />
@else
    <div class="grid gap-4 md:grid-cols-2">
        @foreach($addresses as $address)
            <article class="user-address-card">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <h3 class="font-black text-shop-text">{{ $address->title }}</h3>
                        @if($address->is_default)
                            <span class="mt-2 inline-flex shrink-0 whitespace-nowrap rounded-full bg-shop-primary/10 px-2.5 py-0.5 text-xs font-bold text-shop-primary">آدرس پیش‌فرض</span>
                        @endif
                    </div>
                    <div class="flex gap-3 text-sm font-bold">
                        <a href="{{ route('user.addresses.edit', $address) }}" class="text-shop-primary hover:underline">ویرایش</a>
                        <form method="POST" action="{{ route('user.addresses.destroy', $address) }}">
                            @csrf @method('DELETE')
                            <button type="submit" data-confirm-message="آیا از حذف این آدرس مطمئن هستید؟" class="text-rose-500 hover:underline">حذف</button>
                        </form>
                    </div>
                </div>
                <div class="space-y-1 text-sm">
                    <p class="font-bold">{{ $address->full_name }}</p>
                    <p class="text-shop-muted" dir="ltr">{{ $address->phone }}</p>
                    <p class="leading-relaxed text-shop-muted">{{ $address->fullAddress() }}</p>
                </div>
            </article>
        @endforeach
    </div>
@endif
@endsection
