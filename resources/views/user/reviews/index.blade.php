@extends('layouts.user')

@section('title', 'نظرات من')

@section('content')
<x-user.breadcrumb :items="[
    ['label' => 'داشبورد', 'url' => route('user.dashboard')],
    ['label' => 'نظرات من'],
]" />

<x-user.page-header
    title="نظرات من"
    subtitle="نظرات ثبت‌شده و محصولاتی که می‌توانید برایشان نظر بگذارید."
/>

@if($pendingProducts->isNotEmpty())
    <section class="mb-10">
        <div class="user-section-head">
            <h2 class="user-section-title">در انتظار ثبت نظر</h2>
            <span class="inline-flex shrink-0 whitespace-nowrap rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-700">{{ $pendingProducts->count() }} محصول</span>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach($pendingProducts as $product)
                <article class="user-review-card" x-data="{ open: {{ old('product_id') == $product->id ? 'true' : 'false' }} }">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-shop-primary/10 text-shop-primary">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('products.show', $product->slug) }}" class="font-black text-shop-primary hover:underline">{{ $product->name }}</a>
                            <p class="mt-1 text-sm text-shop-muted">پس از تحویل سفارش، نظر شما به دیگران کمک می‌کند.</p>
                        </div>
                        <button type="button" @click="open = !open" class="user-action-chip shrink-0" x-text="open ? 'بستن' : 'ثبت نظر'"></button>
                    </div>
                    <form
                        x-show="open"
                        x-cloak
                        method="POST"
                        action="{{ route('products.reviews.store', $product) }}"
                        class="mt-4 space-y-3 border-t border-shop-border/40 pt-4"
                    >
                        @csrf
                        <input type="hidden" name="from" value="panel">
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div>
                            <label class="mb-1 block text-sm font-bold text-shop-text">امتیاز</label>
                            <select name="rating" class="input-shop w-full px-3 py-2" required>
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}" @selected(old('rating') == $i)>{{ $i }} ستاره</option>
                                @endfor
                            </select>
                            @error('rating')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-bold text-shop-text">نظر شما</label>
                            <textarea name="comment" rows="3" class="input-shop w-full px-3 py-2" placeholder="تجربه خرید خود را بنویسید...">{{ old('comment') }}</textarea>
                            @error('comment')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="btn-primary w-full !text-sm">ثبت نظر</button>
                    </form>
                </article>
            @endforeach
        </div>
    </section>
@endif

<div class="user-section-head">
    <h2 class="user-section-title">نظرات ثبت‌شده</h2>
</div>

@if($reviews->isEmpty())
    <x-user.empty-state
        icon="star"
        title="هنوز نظری ثبت نکرده‌اید"
        description="پس از تحویل سفارش، می‌توانید برای محصولات نظر و امتیاز بگذارید."
        :action-url="route('user.orders.index')"
        action-label="مشاهده سفارشات"
    />
@else
    <div class="space-y-4">
        @foreach($reviews as $review)
            <article class="user-review-card">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        @if($review->product)
                            <a href="{{ route('products.show', $review->product->slug) }}" class="text-base font-black text-shop-primary hover:underline">{{ $review->product->name }}</a>
                        @endif
                        <p class="mt-1 text-xs text-shop-muted">{{ format_jalali($review->created_at, 'Y/m/d') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-star-rating :rating="$review->rating" />
                        @if($review->is_approved)
                            <span class="inline-flex shrink-0 whitespace-nowrap rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-700">منتشر شده</span>
                        @else
                            <span class="inline-flex shrink-0 whitespace-nowrap rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-700">در انتظار تأیید</span>
                        @endif
                    </div>
                </div>
                @if($review->comment)
                    <p class="mt-4 text-sm leading-relaxed text-shop-text">{{ $review->comment }}</p>
                @endif
            </article>
        @endforeach
    </div>
    @if($reviews->hasPages())
        <div class="mt-6">{{ $reviews->links() }}</div>
    @endif
@endif
@endsection
