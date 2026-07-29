@props(['showControls' => true])

<div
    {{ $attributes->merge(['class' => 'reviews-carousel']) }}
    x-data="reviewsCarousel()"
    x-init="init()"
    aria-roledescription="carousel"
    @touchstart.passive="onTouchStart($event)"
    @touchend.passive="onTouchEnd($event)"
>
    <div
        class="reviews-carousel-viewport"
        x-ref="viewport"
        @pointerdown="onInteractStart()"
        @pointerup.window="onInteractEnd()"
        @pointercancel.window="onInteractEnd()"
        @scroll.passive="onScroll()"
    >
        <div class="reviews-carousel-track" x-ref="track">
            {{ $slot }}
        </div>
    </div>

    @if($showControls)
        <div class="reviews-carousel-controls" x-show="canNavigate" x-cloak>
            <button
                type="button"
                class="reviews-carousel-arrow reviews-carousel-arrow--prev"
                @click="prev()"
                :disabled="index <= 0"
                aria-label="اسلاید قبلی"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </button>

            <div class="reviews-carousel-dots" role="tablist" aria-label="صفحات نظرات">
                <template x-for="page in pages" :key="page">
                    <button
                        type="button"
                        class="reviews-carousel-dot"
                        :class="{ 'is-active': index === page - 1 }"
                        @click="goTo(page - 1)"
                        :aria-label="'صفحه ' + page"
                        :aria-selected="index === page - 1"
                        role="tab"
                    ></button>
                </template>
            </div>

            <button
                type="button"
                class="reviews-carousel-arrow reviews-carousel-arrow--next"
                @click="next()"
                :disabled="index >= maxIndex"
                aria-label="اسلاید بعدی"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            </button>
        </div>
    @endif
</div>
