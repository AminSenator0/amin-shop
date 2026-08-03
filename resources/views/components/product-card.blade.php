@props(['product', 'listReturnUrl' => null, 'variant' => 'default'])

@php
    use App\Support\ProductListReturn;

    $avgRating = round($product->approved_reviews_avg_rating ?? $product->averageRating(), 1);
    $reviewCount = (int) ($product->approved_reviews_count ?? $product->approvedReviews()->count());
    $inWishlist = app(\App\Services\WishlistService::class)->has($product->id);
    $productUrl = ProductListReturn::productUrl($product, $listReturnUrl);
    $cardClass = match ($variant) {
        'featured' => 'product-card product-card--featured group',
        'spotlight' => 'product-card product-card--spotlight group',
        'fresh' => 'product-card product-card--fresh group',
        default => 'product-card group',
    };
@endphp

<article {{ $attributes->merge(['class' => $cardClass]) }}>
    @if(in_array($variant, ['featured', 'spotlight', 'fresh'], true))
        <div class="product-card-accent" aria-hidden="true"></div>
    @endif
    <a href="{{ $productUrl }}" class="product-card-media block">
        <div class="product-card-image-wrap">
            @if($thumbnailUrl = $product->thumbnailUrl())
                <img
                    src="{{ $thumbnailUrl }}"
                    alt="{{ $product->name }}"
                    loading="lazy"
                    class="product-card-image"
                >
            @endif

            <div class="product-card-badges">
                @if($product->hasDiscount())
                    <span class="product-card-badge product-card-badge--sale">{{ $product->discountPercent() }}٪</span>
                @endif
                @if($product->is_featured)
                    <span class="product-card-badge product-card-badge--featured">ویژه</span>
                @endif
            </div>

            <form method="POST" action="{{ route('wishlist.toggle', $product) }}" class="product-card-wishlist-form">
                @csrf
                <button type="submit" class="product-card-wishlist {{ $inWishlist ? 'is-active' : '' }}" aria-label="علاقه‌مندی">
                    <svg class="h-5 w-5" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                </button>
            </form>
        </div>
    </a>

    <div class="product-card-body">
        <p class="product-card-category">{{ $product->category->name }}</p>
        <a href="{{ $productUrl }}" class="product-card-title-link">
            <h3 class="product-card-title">{{ $product->name }}</h3>
        </a>

        @if($reviewCount > 0)
            <div class="product-card-rating">
                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                <span>{{ to_persian_digits(number_format($avgRating, 1)) }}</span>
                <span class="product-card-rating-count">({{ format_number($reviewCount) }})</span>
            </div>
        @endif

        <div class="product-card-footer">
            <div class="product-card-price">
                @if($product->hasDiscount())
                    <span class="product-card-price-old">{{ format_price($product->compare_price) }}</span>
                @endif
                <span class="product-card-price-current">{{ format_price($product->price) }}</span>
            </div>

            @if($product->isInStock())
            <a href="{{ $productUrl }}" class="product-card-add" aria-label="انتخاب {{ $product->name }}">
    {{-- موبایل: آیکون سبد خرید --}}
    <svg class="h-5 w-5 sm:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
    </svg>
    {{-- دسکتاپ: متن انتخاب --}}
    <span class="hidden sm:inline">انتخاب</span>
</a>
            @else
                <span class="product-card-out-of-stock">ناموجود</span>
            @endif
        </div>
    </div>
</article>
