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
                    <svg class="h-5 w-5 sm:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="hidden sm:inline">انتخاب</span>
                </a>
            @else
                <span class="product-card-out-of-stock">ناموجود</span>
            @endif
        </div>
    </div>
</article>
