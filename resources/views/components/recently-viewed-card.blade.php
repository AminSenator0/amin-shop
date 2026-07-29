@props(['product', 'order' => 1])

@php
    use App\Support\ProductListReturn;

    $productUrl = ProductListReturn::productUrl($product);
@endphp

<a href="{{ $productUrl }}" class="recently-viewed-card group">
    <span class="recently-viewed-card-order" aria-hidden="true">{{ to_persian_digits($order) }}</span>

    <div class="recently-viewed-card-image-wrap">
        @if($thumbnailUrl = $product->thumbnailUrl())
            <img
                src="{{ $thumbnailUrl }}"
                alt="{{ $product->name }}"
                loading="lazy"
                class="recently-viewed-card-image"
            >
        @endif
        @if($product->hasDiscount())
            <span class="recently-viewed-card-discount">{{ $product->discountPercent() }}٪</span>
        @endif
    </div>

    <div class="recently-viewed-card-body">
        <p class="recently-viewed-card-category">{{ $product->category->name }}</p>
        <h3 class="recently-viewed-card-title">{{ $product->name }}</h3>
        <div class="recently-viewed-card-price">
            @if($product->hasDiscount())
                <span class="recently-viewed-card-price-old">{{ format_price($product->compare_price) }}</span>
            @endif
            <span class="recently-viewed-card-price-current">{{ format_price($product->price) }}</span>
        </div>
    </div>

    <span class="recently-viewed-card-arrow" aria-hidden="true">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
    </span>
</a>
