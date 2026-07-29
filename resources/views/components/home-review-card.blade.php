@props(['review'])

@php
    $productUrl = $review->product
        ? route('products.show', $review->product->slug).'#reviews'
        : null;
@endphp

@if($productUrl)
    <a href="{{ $productUrl }}" class="home-review-card home-review-card--link" {{ $attributes }}>
        <div class="home-review-card-accent" aria-hidden="true"></div>
        <svg class="home-review-quote" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.45l.966 1.8c-3.446 2.12-4.005 4.851-4.089 5.71.507-.34 1.175-.551 1.947-.551 1.923 0 3.42 1.245 3.42 3.475 0 1.71-1.37 3.108-3.21 3.108-1.707 0-2.839-1.02-3.481-2.679zm10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.45l.966 1.8c-3.446 2.12-4.005 4.851-4.089 5.71.507-.34 1.175-.551 1.947-.551 1.923 0 3.42 1.245 3.42 3.475 0 1.71-1.37 3.108-3.21 3.108-1.707 0-2.839-1.02-3.481-2.679z"/>
        </svg>
        <x-star-rating :rating="$review->rating" class="home-review-stars" />
        <p class="home-review-text">{{ \Illuminate\Support\Str::limit($review->comment, 160) }}</p>
        <div class="home-review-meta">
            <span class="home-review-avatar">{{ mb_substr($review->user->name, 0, 1) }}</span>
            <div class="home-review-author-wrap">
                <span class="home-review-author">{{ $review->user->name }}</span>
                <span class="home-review-product">{{ $review->product->name }}</span>
            </div>
            <span class="home-review-link-hint" aria-hidden="true">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </span>
        </div>
    </a>
@endif
