@extends('layouts.shop')

@section('title', ($product->meta_title ?: $product->name) . ' | خرید ' . $product->name . ' از ' . $store['name'])

@section('meta_description', $product->meta_description ?: Str::limit(strip_tags($product->short_description ?: $product->description ?? 'خرید آنلاین ' . $product->name . ' از ' . $store['name']), 160))

@section('canonical', route('products.show', $product->slug))

@section('open_graph')
    <meta property="og:type" content="product">
    <meta property="og:title" content="{{ $product->meta_title ?: $product->name }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($product->short_description ?: $product->description ?? ''), 200) }}">
    <meta property="og:url" content="{{ route('products.show', $product->slug) }}">
    <meta property="og:image" content="{{ $product->thumbnailUrl() }}">
    <meta property="og:site_name" content="{{ $store['name'] }}">
    <meta property="product:price:amount" content="{{ $product->price }}">
    <meta property="product:price:currency" content="IRR">
@endsection

@section('twitter_card')
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $product->meta_title ?: $product->name }}">
    <meta name="twitter:description" content="{{ Str::limit(strip_tags($product->short_description ?: $product->description ?? ''), 200) }}">
    <meta name="twitter:image" content="{{ $product->thumbnailUrl() }}">
@endsection

@push('head')
@php
$schemaProduct = [
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product->name,
    'description' => strip_tags($product->short_description ?: $product->description ?? ''),
    'sku' => $product->sku,
    'image' => $product->thumbnailUrl(),
    'brand' => $product->brand ? ['@type' => 'Brand', 'name' => $product->brand->name] : null,
    'offers' => [
        '@type' => 'Offer',
        'price' => $product->price,
        'priceCurrency' => 'IRR',
        'availability' => $product->isInStock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'url' => route('products.show', $product->slug),
        'itemCondition' => 'https://schema.org/NewCondition',
    ],
    'aggregateRating' => $approvedCount > 0 ? [
        '@type' => 'AggregateRating',
        'ratingValue' => $product->averageRating(),
        'reviewCount' => $approvedCount,
    ] : null,
];
$schemaProduct = array_filter($schemaProduct, fn($v) => $v !== null);
@endphp
<script type="application/ld+json">
{!! json_encode($schemaProduct, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'خانه', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'محصولات', 'item' => route('products.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $product->category->name, 'item' => route('products.index', ['category' => $product->category->slug])],
        ['@type' => 'ListItem', 'position' => 4, 'name' => $product->name, 'item' => route('products.show', $product->slug)],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
@php
    $product->loadMissing('attributeValues.attribute');
    $inWishlist = app(\App\Services\WishlistService::class)->has($product->id);
    $gallery = $product->images->isNotEmpty()
        ? $product->images
        : ($product->image ? collect([(object)['path' => $product->image]]) : collect());
    $avgRating = $product->averageRating();
    $hasDescription = filled($product->description);
    $hasAttributes = $product->attributeValues->isNotEmpty();
    $hasSpecs = $product->brand || $product->weight || $product->sku || $hasAttributes;
    $hasSizeChart = $product->hasSizeChart();
    $defaultTab = $hasDescription ? 'description' : ($hasSizeChart ? 'size-chart' : ($hasSpecs ? 'specs' : 'reviews'));
    $cartQty = app(\App\Services\CartService::class)->quantityFor($product->id);
@endphp

<div
    class="product-detail-page"
    x-data="{
        qty: {{ (int) old('quantity', 1) }},
        size: @js(old('size', '')),
        color: @js(old('color', '')),
        showStickyBar: false,
        requiresSize: @json($product->hasSizes()),
        requiresColor: @json($product->hasColors()),
    }"
    x-init="window.addEventListener('scroll', () => { showStickyBar = window.scrollY > 500 })"
>
    <div class="home-section-container py-6 sm:py-8">
        <a href="{{ $productsBackUrl }}" class="product-back-link">
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            {{ $productsBackLabel }}
        </a>

        {{-- Breadcrumb --}}
        <nav class="products-breadcrumb" aria-label="مسیر صفحه">
            <a href="{{ route('home') }}">خانه</a>
            <span class="products-breadcrumb-sep" aria-hidden="true">/</span>
            <a href="{{ route('products.index') }}">محصولات</a>
            <span class="products-breadcrumb-sep" aria-hidden="true">/</span>
            <a href="{{ route('products.index', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a>
            <span class="products-breadcrumb-sep" aria-hidden="true">/</span>
            <span class="products-breadcrumb-current">{{ $product->name }}</span>
        </nav>

        {{-- Hero: Gallery + Purchase Panel --}}
        <div class="product-detail-hero">
            <div class="product-detail-gallery-col">
                <x-shop.product-gallery :product="$product" :gallery="$gallery" />
            </div>

            <div class="product-detail-info-col">
                <x-shop.product-purchase-panel
                    :product="$product"
                    :in-wishlist="$inWishlist"
                    :review-count="$approvedCount"
                    :avg-rating="$avgRating"
                />
            </div>
        </div>

        {{-- Tabs: Description / Specs / Reviews --}}
        <div
            class="product-tabs-section"
            x-data="{
                tab: '{{ $defaultTab }}',
                openReviews() {
                    this.tab = 'reviews';
                    if (window.location.hash !== '#reviews') {
                        history.replaceState(null, '', '#reviews');
                    }
                    this.$nextTick(() => {
                        document.getElementById('reviews')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                },
                syncFromHash() {
                    if (window.location.hash === '#reviews') {
                        this.openReviews();
                    }
                },
            }"
            x-init="syncFromHash(); window.addEventListener('hashchange', () => syncFromHash())"
            @open-product-reviews.window="openReviews()"
        >
            <div class="product-tabs-nav" role="tablist">
                @if($hasDescription)
                    <button type="button" role="tab" class="product-tab-btn" :class="{ 'is-active': tab === 'description' }" @click="tab = 'description'" :aria-selected="tab === 'description'">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        توضیحات
                    </button>
                @endif
                @if($hasSizeChart)
                    <button type="button" role="tab" class="product-tab-btn" :class="{ 'is-active': tab === 'size-chart' }" @click="tab = 'size-chart'" :aria-selected="tab === 'size-chart'">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/></svg>
                        راهنمای سایز
                    </button>
                @endif
                @if($hasSpecs)
                    <button type="button" role="tab" class="product-tab-btn" :class="{ 'is-active': tab === 'specs' }" @click="tab = 'specs'" :aria-selected="tab === 'specs'">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                        مشخصات
                    </button>
                @endif
                <button type="button" role="tab" class="product-tab-btn" :class="{ 'is-active': tab === 'reviews' }" @click="tab = 'reviews'" :aria-selected="tab === 'reviews'">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/></svg>
                    نظرات
                    @if($approvedCount > 0)
                        <span class="product-tab-count">{{ format_number($approvedCount) }}</span>
                    @endif
                </button>
            </div>

            <div class="product-tabs-panels">
                @if($hasDescription)
                    <div x-show="tab === 'description'" x-cloak role="tabpanel" class="product-tab-panel">
                        <div class="product-description-content">
                            <x-expandable-text :text="$product->description" :limit="400" variant="shop" />
                        </div>
                    </div>
                @endif

                @if($hasSizeChart)
                    <div x-show="tab === 'size-chart'" x-cloak role="tabpanel" class="product-tab-panel">
                        <x-shop.size-chart-table :product="$product" />
                    </div>
                @endif

                @if($hasSpecs)
                    <div x-show="tab === 'specs'" x-cloak role="tabpanel" class="product-tab-panel">
                        <div class="product-specs-wrap">
                            <table class="product-specs-table">
                                <tbody>
                                    <tr>
                                        <th scope="row">دسته‌بندی</th>
                                        <td><a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="product-spec-link">{{ $product->category->name }}</a></td>
                                    </tr>
                                    @if($product->brand)
                                        <tr>
                                            <th scope="row">برند</th>
                                            <td><a href="{{ route('products.index', ['brand' => $product->brand->slug]) }}" class="product-spec-link">{{ $product->brand->name }}</a></td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th scope="row">کد کالا (SKU)</th>
                                        <td><span class="product-spec-mono" dir="ltr">{{ $product->sku }}</span></td>
                                    </tr>
                                    @if($product->weight)
                                        <tr>
                                            <th scope="row">وزن</th>
                                            <td>{{ format_number($product->weight) }} گرم</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th scope="row">وضعیت موجودی</th>
                                        <td>
                                            @if($product->hasVariants())
                                                <span id="variant-stock-display" class="product-spec-badge product-spec-badge--success">سایز و رنگ را انتخاب کنید</span>
                                            @else
                                                @if($product->isInStock())
                                                    <span class="product-spec-badge product-spec-badge--success">{{ format_number($product->stock) }} عدد موجود</span>
                                                @else
                                                    <span class="product-spec-badge product-spec-badge--danger">ناموجود</span>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                    @if($store['returnDays'] > 0)
                                        <tr>
                                            <th scope="row">مهلت مرجوعی</th>
                                            <td>{{ format_number($store['returnDays']) }} روز</td>
                                        </tr>
                                    @endif

                                    {{-- مشخصات فنی داینامیک (Attributes) ← اضافه شده --}}
                                    @foreach($product->attributeValues as $attrValue)
                                        <tr>
                                            <th scope="row">{{ $attrValue->attribute->name }}</th>
                                            <td>{{ $attrValue->value }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <div x-show="tab === 'reviews'" x-cloak role="tabpanel" class="product-tab-panel" id="reviews">
                    @include('shop.products._reviews', [
                        'product' => $product,
                        'reviews' => $reviews,
                        'canReview' => $canReview,
                        'approvedCount' => $approvedCount,
                        'avgRating' => $avgRating,
                        'ratingDistribution' => $ratingDistribution,
                    ])
                </div>
            </div>
        </div>

        {{-- Related Products --}}
        @if($relatedProducts->count())
            <section class="product-related-section">
                <div class="home-section-header">
                    <div class="home-section-intro">
                        <span class="home-section-badge">مرتبط</span>
                        <h2 class="home-section-title">محصولات مرتبط</h2>
                        <p class="home-section-subtitle">دیگر محصولات دسته «{{ $product->category->name }}»</p>
                    </div>
                    <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="home-view-all">
                        مشاهده همه
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                    </a>
                </div>
                <div class="home-products-grid">
                    @foreach($relatedProducts as $related)
                        <x-product-card :product="$related" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Recently Viewed --}}
        @if($recentlyViewedProducts->isNotEmpty())
            <section class="product-related-section product-related-section--alt">
                <div class="home-section-header">
                    <div class="home-section-intro">
                        <span class="home-section-badge home-section-badge--recent">
                            <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="home-section-badge-words"><span>بازدید</span><span>اخیر</span></span>
                        </span>
                        <h2 class="home-section-title">اخیراً مشاهده‌شده</h2>
                    </div>
                </div>
                <div class="home-products-grid">
                    @foreach($recentlyViewedProducts as $recent)
                        <x-product-card :product="$recent" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- Mobile Sticky Purchase Bar --}}
    @if($product->isInStock())
        <div class="product-sticky-bar" x-show="showStickyBar" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0">
            <div class="product-sticky-bar-inner">
                <div class="product-sticky-bar-info">
                    <p class="product-sticky-bar-name">{{ Str::limit($product->name, 40) }}</p>
                    <p class="product-sticky-bar-price">{{ format_price($product->price) }}</p>
                </div>
                @if($cartQty > 0)
                    <a href="{{ route('cart.index') }}" class="btn-primary product-sticky-bar-btn">مشاهده سبد خرید</a>
                @else
                    <form
                        method="POST"
                        action="{{ route('cart.store') }}"
                        class="product-sticky-bar-form"
                        @submit="if ((requiresSize && !size) || (requiresColor && !color)) { $event.preventDefault(); document.getElementById('product-purchase-options')?.scrollIntoView({ behavior: 'smooth', block: 'center' }); }"
                    >
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="quantity" :value="qty">
                        @if($product->hasSizes())
                            <input type="hidden" name="size" :value="size">
                        @endif
                        @if($product->hasColors())
                            <input type="hidden" name="color" :value="color">
                        @endif
                        <button type="submit" class="btn-primary product-sticky-bar-btn">افزودن به سبد</button>
                    </form>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function() {
    const variants = @json(
        $product->variants->mapWithKeys(fn($v) => [
            ($v->size ?? '_') . '|' . ($v->color ?? '_') => $v->stock
        ])
    );

    function updateStockDisplay() {
        const sizeEl = document.querySelector('input[name="size"]:checked, select[name="size"]');
        const colorEl = document.querySelector('input[name="color"]:checked, select[name="color"]');
        const display = document.getElementById('variant-stock-display');
        if (!display) return;

        const size = sizeEl?.value || '_';
        const color = colorEl?.value || '_';
        const stock = variants[size + '|' + color];

        if (stock !== undefined) {
            if (stock > 0) {
                display.textContent = new Intl.NumberFormat('fa-IR').format(stock) + ' عدد موجود';
                display.className = 'product-spec-badge product-spec-badge--success';
            } else {
                display.textContent = 'ناموجود';
                display.className = 'product-spec-badge product-spec-badge--danger';
            }
        } else {
            display.textContent = 'سایز و رنگ را انتخاب کنید';
            display.className = 'product-spec-badge product-spec-badge--success';
        }
    }

    document.querySelectorAll('input[name="size"], select[name="size"], input[name="color"], select[name="color"]').forEach(el => {
        el.addEventListener('change', updateStockDisplay);
    });
})();
</script>
@endpush