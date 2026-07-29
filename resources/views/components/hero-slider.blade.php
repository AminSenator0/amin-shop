@props(['sliders'])

@php
    use App\Support\StoreSettings;

    $slideData = $sliders->map(fn ($slide) => [
        'title' => $slide->title,
        'subtitle' => $slide->subtitle,
        'button_url' => $slide->button_url ?? route('products.index'),
        'button_text' => $slide->button_text ?: 'مشاهده محصولات',
    ])->values();
@endphp

@if($sliders->isNotEmpty())
<div
    x-data="{
        current: 0,
        total: {{ $sliders->count() }},
        slides: @js($slideData),
        autoplay: null,
        progress: 0,
        duration: 7000,
        tick: null,
        touchStartX: 0,
        next() {
            this.current = (this.current + 1) % this.total;
            this.resetProgress();
        },
        prev() {
            this.current = (this.current - 1 + this.total) % this.total;
            this.resetProgress();
        },
        goTo(i) {
            this.current = i;
            this.resetProgress();
        },
        resetProgress() {
            this.progress = 0;
        },
        startAutoplay() {
            if (this.total <= 1) return;
            this.stopAutoplay();
            this.resetProgress();
            this.autoplay = setInterval(() => this.next(), this.duration);
            this.tick = setInterval(() => {
                this.progress = Math.min(this.progress + (100 / (this.duration / 50)), 100);
            }, 50);
        },
        stopAutoplay() {
            if (this.autoplay) clearInterval(this.autoplay);
            if (this.tick) clearInterval(this.tick);
            this.autoplay = null;
            this.tick = null;
        },
        onTouchStart(e) {
            this.touchStartX = e.touches[0].clientX;
        },
        onTouchEnd(e) {
            const delta = this.touchStartX - e.changedTouches[0].clientX;
            if (Math.abs(delta) < 48) return;
            delta > 0 ? this.next() : this.prev();
        }
    }"
    x-init="startAutoplay()"
    @mouseenter="stopAutoplay()"
    @mouseleave="startAutoplay()"
    @focusin="stopAutoplay()"
    @focusout="startAutoplay()"
    @keydown.arrow-left.window="next()"
    @keydown.arrow-right.window="prev()"
    class="home-slider"
    aria-roledescription="carousel"
>
    <div
        class="home-slider-viewport"
        @touchstart.passive="onTouchStart($event)"
        @touchend.passive="onTouchEnd($event)"
    >
        <div class="home-slider-media-stack" aria-hidden="true">
            @foreach($sliders as $index => $slide)
                <div
                    x-show="current === {{ $index }}"
                    x-transition:enter="transition ease-out duration-700"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-500"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="home-slider-panel"
                    :class="current === {{ $index }} ? 'home-slider-panel--active' : ''"
                    @if($index > 0) x-cloak @endif
                >
                    <img
                        src="{{ slider_image_url($slide->image, $loop->iteration, $slide->title) }}"
                        alt=""
                        class="home-slider-image"
                        loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                        decoding="async"
                    >
                </div>
            @endforeach
        </div>

        <div class="home-slider-scrim" aria-hidden="true"></div>

        <div
            class="home-slider-body"
            role="group"
            aria-roledescription="slide"
            :aria-label="slides[current].title"
        >
            <div class="home-slider-card home-slider-card--fixed">
                <div class="home-slider-card-top">
                    <span class="home-slider-badge">پیشنهاد ویژه</span>
                </div>

                <h2 class="home-slider-heading" x-text="slides[current].title"></h2>

                <p
                    class="home-slider-lead"
                    x-show="slides[current].subtitle"
                    x-text="slides[current].subtitle"
                ></p>

                <a
                    :href="slides[current].button_url"
                    class="home-slider-cta"
                >
                    <span x-text="slides[current].button_text"></span>
                    <span class="home-slider-cta-icon" aria-hidden="true">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                    </span>
                </a>
            </div>
        </div>

        @if($sliders->count() > 1)
            <div class="home-slider-arrows" aria-hidden="false">
                <button type="button" @click="prev()" class="home-slider-arrow home-slider-arrow--prev" aria-label="اسلاید قبلی">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                </button>
                <button type="button" @click="next()" class="home-slider-arrow home-slider-arrow--next" aria-label="اسلاید بعدی">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </button>
            </div>

            <nav class="home-slider-pager" role="tablist" aria-label="انتخاب اسلاید">
                @foreach($sliders as $index => $slide)
                    <button
                        type="button"
                        @click="goTo({{ $index }})"
                        class="home-slider-pill"
                        :class="current === {{ $index }} ? 'home-slider-pill--active' : ''"
                        role="tab"
                        :aria-selected="current === {{ $index }}"
                        :aria-label="'اسلاید {{ $index + 1 }}: {{ $slide->title }}'"
                    >
                        <span class="home-slider-pill-track" aria-hidden="true">
                            <span
                                class="home-slider-pill-fill"
                                :style="current === {{ $index }} ? `transform: scaleX(${progress / 100})` : (current > {{ $index }} ? 'transform: scaleX(1)' : 'transform: scaleX(0)')"
                            ></span>
                        </span>
                        <span class="home-slider-pill-title">{{ $slide->title }}</span>
                    </button>
                @endforeach
            </nav>

            <div class="home-slider-dots" role="tablist" aria-label="انتخاب اسلاید">
                @foreach($sliders as $index => $slide)
                    <button
                        type="button"
                        @click="goTo({{ $index }})"
                        class="home-slider-dot"
                        :class="current === {{ $index }} ? 'home-slider-dot--active' : ''"
                        role="tab"
                        :aria-selected="current === {{ $index }}"
                        :aria-label="'اسلاید {{ $index + 1 }}'"
                    >
                        <span
                            class="home-slider-dot-fill"
                            :style="current === {{ $index }} ? `transform: scaleX(${progress / 100})` : ''"
                        ></span>
                    </button>
                @endforeach
            </div>
        @endif
    </div>
</div>
@else
<div class="home-slider-fallback">
    <h1 class="text-2xl font-black sm:text-3xl">{{ StoreSettings::get('store_name', config('app.name')) }}</h1>
    <p class="mt-3 text-shop-on-hero/90">{{ StoreSettings::get('tagline') }}</p>
    <a href="{{ route('products.index') }}" class="home-slider-cta mt-6">مشاهده محصولات</a>
</div>
@endif
