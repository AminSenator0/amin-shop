@props(['product', 'gallery'])

@php
    $urls = $gallery->isNotEmpty()
        ? $gallery->map(fn ($img) => product_image_url($img->path, $product->id))->filter()->values()
        : ($product->isMenPerfume() ? collect([$product->thumbnailUrl()])->filter()->values() : collect());
@endphp

<div
    class="product-gallery"
    x-data="productGallery({{ Js::from($urls) }})"
    @keydown.window="onLightboxKeydown($event)"
>
    <div class="product-gallery-main card">
        @if($urls->isNotEmpty())
            @foreach($urls as $i => $url)
                <button
                    type="button"
                    class="product-gallery-slide"
                    x-show="active === {{ $i }}"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    @click="openLightbox({{ $i }})"
                    aria-label="بزرگ‌نمایی تصویر {{ $i + 1 }}"
                >
                    <img
                        src="{{ $url }}"
                        alt="{{ $product->name }}"
                        class="product-gallery-image"
                    >
                    <span class="product-gallery-zoom-hint" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6"/></svg>
                    </span>
                </button>
            @endforeach

            @if($urls->count() > 1)
                <button type="button" class="product-gallery-nav product-gallery-nav--prev" @click.stop="prev()" aria-label="تصویر قبلی">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </button>
                <button type="button" class="product-gallery-nav product-gallery-nav--next" @click.stop="next()" aria-label="تصویر بعدی">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                </button>
                <span class="product-gallery-counter" x-text="(active + 1) + ' / ' + urls.length"></span>
            @endif
        @else
            <div class="product-gallery-empty">
                <svg class="h-16 w-16 text-shop-border" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg>
                <span class="text-shop-muted">بدون تصویر</span>
            </div>
        @endif
    </div>

    @if($urls->count() > 1)
        <div class="product-gallery-thumbs" role="tablist" aria-label="تصاویر محصول">
            @foreach($urls as $i => $url)
                <button
                    type="button"
                    role="tab"
                    :aria-selected="active === {{ $i }}"
                    @click="active = {{ $i }}"
                    class="product-gallery-thumb"
                    :class="{ 'is-active': active === {{ $i }} }"
                >
                    <img src="{{ $url }}" alt="" class="h-full w-full object-cover">
                </button>
            @endforeach
        </div>
    @endif

    <div
        x-show="lightbox"
        x-cloak
        class="product-gallery-lightbox"
        @click.self="closeLightbox()"
        role="dialog"
        aria-modal="true"
        aria-label="نمایش تصویر بزرگ"
    >
        <button type="button" class="product-gallery-lightbox-close" @click="closeLightbox()" aria-label="بستن">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <template x-if="urls.length > 1">
            <button type="button" class="product-gallery-lightbox-nav product-gallery-lightbox-nav--prev" @click="prev()" aria-label="تصویر قبلی">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </button>
        </template>

        <div
            x-ref="lightboxViewport"
            class="product-gallery-lightbox-viewport"
            :class="{ 'is-grabbing': isDragging, 'is-zoomed': isZoomed }"
            @wheel.prevent="onWheel($event)"
            @dblclick="onDoubleClick($event)"
            @mousedown="pointerDown($event)"
            @mousemove.window="pointerMove($event)"
            @mouseup.window="pointerUp()"
            @mouseleave.window="pointerUp()"
            @touchstart="touchStart($event)"
            @touchmove="touchMove($event)"
            @touchend="touchEnd($event)"
            @touchcancel="touchEnd($event)"
        >
            <div class="product-gallery-lightbox-stage" :style="stageStyle">
                <img
                    x-ref="lightboxImage"
                    :src="urls[active]"
                    alt="{{ $product->name }}"
                    class="product-gallery-lightbox-image"
                    @load="clampPan()"
                    draggable="false"
                >
            </div>
        </div>

        <template x-if="urls.length > 1">
            <button type="button" class="product-gallery-lightbox-nav product-gallery-lightbox-nav--next" @click="next()" aria-label="تصویر بعدی">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            </button>
        </template>

        <div class="product-gallery-lightbox-toolbar">
            <button type="button" class="product-gallery-lightbox-tool" @click="zoomOut()" :disabled="scale <= minScale" aria-label="کوچک‌نمایی">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>
            </button>
            <span class="product-gallery-lightbox-zoom-level" x-text="Math.round(scale * 100) + '%'"></span>
            <button type="button" class="product-gallery-lightbox-tool" @click="zoomIn()" :disabled="scale >= maxScale" aria-label="بزرگ‌نمایی">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"/></svg>
            </button>
            <button type="button" class="product-gallery-lightbox-tool" @click="resetZoom()" :disabled="!isZoomed" aria-label="بازنشانی زوم">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg>
            </button>
        </div>

        <p class="product-gallery-lightbox-hint">
            <span x-show="!isZoomed">برای بزرگ‌نمایی اسکرول کنید یا دوبار کلیک کنید</span>
            <span x-show="isZoomed" x-cloak>برای جابه‌جایی تصویر را بکشید</span>
        </p>
    </div>
</div>
