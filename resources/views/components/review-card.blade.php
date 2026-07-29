@props(['review', 'variant' => 'shop'])

@php
    $initial = mb_substr($review->user->name, 0, 1);
@endphp

<article {{ $attributes->merge(['class' => 'review-card']) }}>
    <div class="review-card-accent" aria-hidden="true"></div>
    <div class="review-card-body">
        <div class="review-card-header">
            <div class="review-card-author">
                <span class="review-card-avatar">{{ $initial }}</span>
                <div class="review-card-author-meta">
                    <span class="review-card-name">{{ $review->user->name }}</span>
                    <time class="review-card-date" datetime="{{ $review->created_at->toIso8601String() }}">{{ format_jalali($review->created_at) }}</time>
                </div>
            </div>
            <div class="review-card-rating">
                <x-star-rating :rating="$review->rating" size="sm" />
                @if(! $review->is_approved)
                    <span class="review-card-badge review-card-badge--pending">در انتظار تأیید</span>
                @endif
            </div>
        </div>
        @if($review->comment)
            <div class="review-card-quote">
                <svg class="review-card-quote-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.45l.966 1.8c-3.446 2.12-4.005 4.851-4.089 5.71.507-.34 1.175-.551 1.947-.551 1.923 0 3.42 1.245 3.42 3.475 0 1.71-1.37 3.108-3.21 3.108-1.707 0-2.839-1.02-3.481-2.679zm10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.45l.966 1.8c-3.446 2.12-4.005 4.851-4.089 5.71.507-.34 1.175-.551 1.947-.551 1.923 0 3.42 1.245 3.42 3.475 0 1.71-1.37 3.108-3.21 3.108-1.707 0-2.839-1.02-3.481-2.679z"/>
                </svg>
                <x-expandable-text :text="$review->comment" :limit="200" variant="shop" class="review-card-text" />
            </div>
        @endif
    </div>
</article>
