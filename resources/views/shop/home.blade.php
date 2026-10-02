@extends('layouts.shop')

@section('title', ($store['tagline'] ? $store['name'] . ' | ' . $store['tagline'] : $store['name']))

@push('head')
<style>
/* ─── اسلایدر هیرو: انیمیشن Ken Burns ─── */
.hero-banner-img.is-active {
    animation: hero-kenburns 6.5s ease-out both;
    will-change: transform;
}
@keyframes hero-kenburns {
    0%   { transform: scale(1.02); }
    100% { transform: scale(1.14); }
}
</style>
@endpush


@php
    $productCount = \App\Models\Product::where('is_active', true)->count();
    $topCategories = $categories->sortByDesc(fn ($c) => $c->activeProducts()->count())->take(6);

    $taglineLines = array_values(array_filter(preg_split('/\R|\|/u', trim($store['tagline'] ?? ''), 2)));
    if (count($taglineLines) < 2) {
        $taglineWords = preg_split('/\s+/u', trim($store['tagline'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        if (count($taglineWords) > 1) {
            $taglineMid = (int) ceil(count($taglineWords) / 2);
            $taglineLines = [
                implode(' ', array_slice($taglineWords, 0, $taglineMid)),
                implode(' ', array_slice($taglineWords, $taglineMid)),
            ];
        } else {
            $taglineLines = [trim($store['tagline'] ?? '')];
        }
    }

    $homeNavSections = array_values(array_filter([
        $categories->count() ? ['id' => 'home-categories', 'label' => 'دسته‌ها'] : null,
        $store['homepageCollectionsEnabled'] && ! empty($homeCollections) ? ['id' => 'home-collections', 'label' => 'کالکشن'] : null,
        $featuredProducts->count() ? ['id' => 'home-featured', 'label' => 'ویژه'] : null,
        $store['homepageFlashSaleEnabled'] && $flashSaleEndsAt && $flashSaleProducts->isNotEmpty() ? ['id' => 'home-flash-sale', 'label' => 'حراج'] : null,
        $store['homepageBestsellersEnabled'] && $bestsellerProducts->isNotEmpty() ? ['id' => 'home-bestsellers', 'label' => 'پرفروش'] : null,
        $store['homepageDiscountedEnabled'] && $discountedProducts->isNotEmpty() ? ['id' => 'home-discounted', 'label' => 'تخفیف'] : null,
        $latestProducts->isNotEmpty() ? ['id' => 'home-latest', 'label' => 'جدید'] : null,
        $store['homepageReviewsEnabled'] && $testimonialReviews->isNotEmpty() ? ['id' => 'home-reviews', 'label' => 'نظرات'] : null,
        $store['homepageFaqEnabled'] && $faqs->isNotEmpty() ? ['id' => 'home-faq', 'label' => 'سوالات'] : null,
    ]));
@endphp

@section('meta_description', $store['metaDescription'] ?? 'خرید آنلاین از ' . $store['name'] . ' - ' . ($store['tagline'] ?? 'بهترین محصولات با قیمت مناسب و ارسال سریع'))

@section('content')

@push('preload')
@php $heroImagePreload = hero_showcase_image_sources(); @endphp
@if($heroImagePreload['webp'])
    <link rel="preload" as="image" href="{{ $heroImagePreload['webp'] }}" type="image/webp" fetchpriority="high">
@elseif($heroImagePreload['jpg'])
    <link rel="preload" as="image" href="{{ $heroImagePreload['jpg'] }}" fetchpriority="high">
@else
    <link rel="preload" as="image" href="{{ hero_showcase_image_url() }}" fetchpriority="high">
@endif
@endpush

<div class="home-page">

<x-home-sticky-nav :sections="$homeNavSections" />

@include('shop.partials.home-extras-top')

{{-- ── Hero ── --}}
<section class="home-hero relative overflow-hidden" aria-labelledby="home-hero-title">
    <div class="home-hero-pattern" aria-hidden="true"></div>
    <div class="home-hero-glow home-hero-glow--primary" aria-hidden="true"></div>
    <div class="home-hero-glow home-hero-glow--accent" aria-hidden="true"></div>

    <div class="relative z-10 mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8 lg:py-20">
        <div class="grid items-center gap-8 lg:grid-cols-12 lg:gap-12 xl:gap-14">
            <div class="order-2 lg:order-1 lg:col-span-6 xl:col-span-5">
                @if($heroPersonalization)
                    <div class="home-hero-personal mb-5 sm:mb-6">
                        <div class="home-hero-personal-main">
                            <span class="home-hero-personal-greeting">سلام {{ $heroPersonalization['firstName'] }} 👋</span>
                            @if($heroPersonalization['activeOrder'])
                                <p class="home-hero-personal-text">
                                    سفارش <strong dir="ltr">#{{ $heroPersonalization['activeOrder']->tracking_code }}</strong>
                                    در وضعیت «{{ $heroPersonalization['activeOrder']->status->label() }}» است.
                                </p>
                                <a href="{{ route('orders.track', ['code' => $heroPersonalization['activeOrder']->tracking_code]) }}" class="home-hero-personal-link">
                                    پیگیری سفارش
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                                </a>
                            @elseif($heroPersonalization['wishlistCount'] > 0)
                                <p class="home-hero-personal-text">{{ to_persian_digits((string) $heroPersonalization['wishlistCount']) }} محصول در علاقه‌مندی‌های شماست.</p>
                                <a href="{{ route('user.wishlist.index') }}" class="home-hero-personal-link">
                                    مشاهده علاقه‌مندی‌ها
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                                </a>
                            @else
                                <p class="home-hero-personal-text">خوش آمدید! پیشنهادهای ویژه امروز را از دست ندهید.</p>
                            @endif
                        </div>
                    </div>
                @endif

                <span class="badge-hero mb-5 inline-flex items-center gap-2.5 sm:mb-6">
                    <span class="home-pulse-dot"></span>
                    {{ $store['name'] }}
                </span>

                <h1 id="home-hero-title" class="home-hero-title">
                    @foreach($taglineLines as $line)
                        <span class="text-gradient home-hero-title-line">{{ $line }}</span>
                    @endforeach
                </h1>

                <p class="home-hero-desc">
                    بیش از {{ number_format($productCount) }} محصول در {{ $categories->count() }} دسته‌بندی — خرید آسان، ارسال سریع و پشتیبانی واقعی.
                </p>

                <form action="{{ route('products.index') }}" method="GET" class="home-search" x-data="searchAutocomplete('{{ route('search.suggest') }}')" @click.outside="open = false">
                    <svg class="home-search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input type="text" name="search" x-model="query" @input.debounce.300ms="fetch()" @focus="query.length >= 2 && fetch()" placeholder="جستجوی محصول، برند یا دسته‌بندی..." class="home-search-input" autocomplete="off" aria-label="جستجوی محصولات">
                    <div x-show="open && (products.length || categories.length)" x-cloak class="home-search-suggest">
                        <template x-if="categories.length">
                            <div class="home-search-suggest-group">
                                <p class="home-search-suggest-label">دسته‌بندی‌ها</p>
                                <template x-for="item in categories" :key="item.url">
                                    <a :href="item.url" class="home-search-suggest-item" x-text="item.name"></a>
                                </template>
                            </div>
                        </template>
                        <template x-if="products.length">
                            <div class="home-search-suggest-group">
                                <p class="home-search-suggest-label">محصولات</p>
                                <template x-for="item in products" :key="item.url">
                                    <a :href="item.url" class="home-search-suggest-item home-search-suggest-product">
                                        <img :src="item.image" :alt="item.name" class="home-search-suggest-thumb">
                                        <span><span x-text="item.name"></span><span class="home-search-suggest-price" x-text="item.price"></span></span>
                                    </a>
                                </template>
                            </div>
                        </template>
                    </div>
                    <button type="submit" class="home-search-btn">
                        <span class="hidden sm:inline">جستجو</span>
                        <svg class="h-5 w-5 sm:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    </button>
                </form>

                @if($topCategories->isNotEmpty())
                    <div class="home-quick-tags">
                        <span class="home-quick-tags-label">محبوب:</span>
                        <div class="home-quick-tags-list">
                            @foreach($topCategories->take(4) as $category)
                                <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="home-quick-tag">{{ $category->name }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="home-hero-actions">
                    <a href="{{ route('products.index') }}" class="btn-primary home-hero-cta">
                        <svg class="icon-arrow-forward h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        مشاهده همه محصولات
                    </a>
                    <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="btn-secondary-hero">جدیدترین‌ها</a>
                </div>

                <div class="home-stats">
                    <div class="home-stat">
                        <p class="home-stat-value">{{ number_format($productCount) }}+</p>
                        <p class="home-stat-label">محصول فعال</p>
                    </div>
                    <div class="home-stat-divider" aria-hidden="true"></div>
                    <div class="home-stat">
                        <p class="home-stat-value">{{ $categories->count() }}</p>
                        <p class="home-stat-label">دسته‌بندی</p>
                    </div>
                    <div class="home-stat-divider" aria-hidden="true"></div>
                    <div class="home-stat">
                        <p class="home-stat-value">{{ $store['returnDays'] > 0 ? $store['returnDays'].' روز' : '۲۴/۷' }}</p>
                        <p class="home-stat-label">{{ $store['returnDays'] > 0 ? 'ضمانت بازگشت' : 'پشتیبانی' }}</p>
                    </div>
                </div>
            </div>

            <div class="order-1 lg:order-2 lg:col-span-6 xl:col-span-7">
                <div class="home-hero-visual">
                    <div class="home-hero-ring" aria-hidden="true"></div>
                    <div class="home-hero-collage">
                        <div class="home-hero-collage-main">
                            @if($heroBanners->isNotEmpty())
                                {{-- اسلایدر هیرو — مدیریت از: پنل ادمین → بنرهای هیرو --}}
                                <div class="relative h-full w-full overflow-hidden"
                                     x-data="{
                                         current: 0,
                                         total: {{ $heroBanners->count() }},
                                         timer: null,
                                         start() { if (this.total > 1) { this.stop(); this.timer = setInterval(() => { this.current = (this.current + 1) % this.total }, 6000) } },
                                         stop() { clearInterval(this.timer) }
                                     }"
                                     x-init="start()"
                                     @mouseenter="stop()"
                                     @mouseleave="start()">
                                    @foreach($heroBanners as $index => $banner)
                                        <div class="absolute inset-0"
                                             :class="current === {{ $index }} ? 'z-10 opacity-100' : 'z-0 opacity-0'"
                                             style="transition: opacity 1.2s ease;">
                                            @if($banner->link)
                                                <a href="{{ $banner->link }}" class="block h-full w-full">
                                                    <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->title }}" class="hero-banner-img h-full w-full object-cover" :class="current === {{ $index }} && 'is-active'">
                                                </a>
                                            @else
                                                <img src="{{ $banner->imageUrl() }}" alt="{{ $banner->title }}" class="hero-banner-img h-full w-full object-cover" :class="current === {{ $index }} && 'is-active'">
                                            @endif
                                        </div>
                                    @endforeach

                                    @if($heroBanners->count() > 1)
                                        <div class="absolute bottom-3 left-1/2 z-20 flex -translate-x-1/2 gap-1.5">
                                            @foreach($heroBanners as $index => $banner)
                                                <button type="button"
                                                        @click="stop(); current = {{ $index }}; start()"
                                                        :class="current === {{ $index }} ? 'w-5 bg-white' : 'w-2 bg-white/50 hover:bg-white/80'"
                                                        class="h-2 rounded-full transition-all duration-300"
                                                        aria-label="اسلاید {{ $index + 1 }}"></button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @else
                                @php $heroImage = hero_showcase_image_sources(); @endphp
                                <picture>
                                    @if($heroImage['webp'])
                                        <source srcset="{{ $heroImage['webp'] }}" type="image/webp">
                                    @endif
                                    <img
                                        src="{{ $heroImage['jpg'] ?? $heroImage['webp'] ?? hero_showcase_image_url() }}"
                                        alt="ویترین {{ $store['name'] }}"
                                        class="h-full w-full object-cover"
                                        width="1200"
                                        height="800"
                                        fetchpriority="high"
                                        decoding="async"
                                    >
                                </picture>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="home-post-hero">
    {{-- ── Trust strip ── --}}
    <section class="home-trust-strip" aria-label="مزایای خرید">
        <div class="home-trust-grid">
            @foreach($store['trustBadges'] as $feature)
                <article class="home-trust-item">
                    <div class="home-trust-item-glow" aria-hidden="true"></div>
                    <div class="home-trust-icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}"/></svg>
                    </div>
                    <div class="home-trust-content">
                        <p class="home-trust-title">{{ $feature['title'] }}</p>
                        <p class="home-trust-desc">{{ $feature['desc'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    @include('shop.partials.home-quick-track')

    @include('shop.partials.home-payment-trust')
</div>

@include('shop.partials.home-how-it-works')

{{-- ── Slider ── --}}
@if($sliders->isNotEmpty())
    <section class="home-section home-slider-section home-section--lead" aria-label="اسلایدر تبلیغاتی">
        <div class="home-section-container">
            <div class="home-section-header home-slider-section-header">
                <div class="home-section-intro">
                    <span class="home-section-badge">ویترین</span>
                    <h2 class="home-section-title">پیشنهادهای ویژه</h2>
                    <p class="home-section-subtitle">جدیدترین کمپین‌ها و تخفیف‌های فروشگاه</p>
                </div>
            </div>
            <x-hero-slider :sliders="$sliders" />
        </div>
    </section>
@endif

{{-- ── Categories ── --}}
@if($categories->count())
    <section id="home-categories" class="home-section home-section--lead home-categories-section scroll-mt-28">
        <div class="home-section-container">
            <div class="home-section-header">
                <div class="home-section-intro">
                    <span class="home-section-badge">دسته‌بندی‌ها</span>
                    <h2 class="home-section-title">کاوش در دسته‌ها</h2>
                    <p class="home-section-subtitle">محصول مورد نظرتان را سریع‌تر پیدا کنید</p>
                </div>
                <a href="{{ route('products.index') }}" class="home-view-all">
                    همه دسته‌ها
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                </a>
            </div>

            <div class="home-categories-grid">
                @foreach($categories->take(12) as $category)
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="home-category-card group">
                        <div class="home-category-image">
                            <img
                                src="{{ category_image_url($category->image, $loop->iteration, $category->name, $category->slug) }}"
                                alt="{{ $category->name }}"
                                loading="lazy"
                                class="home-category-img"
                            >
                            <div class="home-category-overlay"></div>
                            <div class="home-category-content">
                                <h3 class="home-category-name">{{ $category->name }}</h3>
                                <span class="home-category-count">{{ $category->activeProducts()->count() }} محصول</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

@include('shop.partials.home-collections')

{{-- ── Featured products ── --}}
@if($featuredProducts->count())
    <section id="home-featured" class="home-section home-featured-section scroll-mt-28">
        <div class="home-section-container">
            <div class="home-featured-showcase">
                <div class="home-featured-showcase-glow" aria-hidden="true"></div>
                <div class="home-section-header home-featured-header">
                    <div class="home-section-intro">
                        <span class="home-section-badge home-section-badge--featured">منتخب</span>
                        <h2 class="home-section-title">محصولات ویژه</h2>
                        <p class="home-section-subtitle">برگزیده‌های این هفته با بهترین قیمت</p>
                    </div>
                    <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="home-view-all home-view-all--featured">
                        مشاهده همه
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                    </a>
                </div>

                <div class="home-products-grid relative z-10">
                    @foreach($featuredProducts as $product)
                        <x-product-card :product="$product" variant="featured" />
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif

@include('shop.partials.home-extras-mid')

{{-- ── Promo banners ── --}}
@if($banners->isNotEmpty())
    <section class="home-section home-banners-section" aria-label="پیشنهادهای ویژه">
        <div class="home-section-container">
            <div class="home-section-header">
                <div class="home-section-intro">
                    <span class="home-section-badge home-section-badge--featured">پیشنهادها</span>
                    <h2 class="home-section-title">کالکشن‌های منتخب</h2>
                    <p class="home-section-subtitle">فرصت‌های خرید با تصاویر و پیشنهادهای جذاب</p>
                </div>
            </div>
            <x-promo-banners :banners="$banners" />
        </div>
    </section>
@endif

@include('shop.partials.home-extras-after-featured')

{{-- ── Latest products ── --}}
@if($latestProducts->isNotEmpty())
<section id="home-latest" class="home-section home-latest-section scroll-mt-28">
    <div class="home-section-container">
        <div class="home-latest-showcase">
            <div class="home-latest-showcase-glow home-latest-showcase-glow--1" aria-hidden="true"></div>
            <div class="home-latest-showcase-glow home-latest-showcase-glow--2" aria-hidden="true"></div>
            <div class="home-latest-pattern" aria-hidden="true"></div>

            <div class="home-section-header home-latest-header">
                <div class="home-section-intro">
                    <span class="home-section-badge home-section-badge--new">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/></svg>
                        تازه‌ها
                    </span>
                    <h2 class="home-section-title">جدیدترین محصولات</h2>
                    <p class="home-section-subtitle">آخرین محصولات اضافه‌شده به فروشگاه</p>
                </div>
                <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="home-view-all home-view-all--new">
                    مشاهده همه
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                </a>
            </div>

            <div class="home-products-grid relative z-10">
                @foreach($latestProducts as $product)
                    <x-product-card :product="$product" variant="fresh" />
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

{{-- ── Promo CTA ── --}}
<section class="home-section home-cta-section">
    <div class="home-section-container home-section-container--tight">
        <div class="home-cta-banner">
            <div class="home-cta-glow" aria-hidden="true"></div>
            <div class="home-cta-content">
                <span class="badge-hero mb-4">پیشنهاد ویژه</span>
                <h2 class="home-cta-title">{{ $store['promoBannerTitle'] }}</h2>
                <p class="home-cta-text">{{ $store['promoBannerText'] }}</p>
                <a href="{{ route('products.index') }}" class="home-cta-btn">
                    شروع خرید
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                </a>
            </div>
            @if($latestProducts->isNotEmpty())
                <div class="home-cta-images">
                    @foreach($latestProducts->take(3) as $i => $product)
                        <div class="home-cta-image home-cta-image--{{ $i + 1 }}">
                            @if($thumbnailUrl = $product->thumbnailUrl())
                                <img src="{{ $thumbnailUrl }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover">
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>

@include('shop.partials.home-extras-bottom')

@include('shop.partials.home-about-snippet')

@include('shop.partials.home-size-guide')

@if($store['newsletterEnabled'])
<section class="home-section home-newsletter-section">
    <div class="home-section-container">
        <div class="home-newsletter">
            <div class="home-newsletter-icon" aria-hidden="true">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
            </div>
            <div class="home-newsletter-body">
                <h2 class="home-newsletter-title">از تخفیف‌ها باخبر شوید</h2>
                <p class="home-newsletter-desc">
                    @if(filled($store['newsletterIncentiveText'] ?? null))
                        {{ $store['newsletterIncentiveText'] }}
                    @else
                        ایمیل خود را وارد کنید تا از پیشنهادهای ویژه و تخفیف‌های محدود مطلع شوید.
                    @endif
                </p>
            </div>
            <form method="POST" action="{{ route('newsletter.subscribe') }}" class="home-newsletter-form">
                @csrf
                <div class="home-newsletter-input-wrap">
                    <input type="email" name="email" placeholder="ایمیل شما..." class="home-newsletter-input" required autocomplete="email">
                </div>
                <button type="submit" class="btn-primary home-newsletter-btn">عضویت</button>
            </form>
        </div>
    </div>
</section>
@endif

</div>

<x-home-back-to-top />

@push('head')
<link rel="canonical" href="{{ route('home') }}">

<meta property="og:type" content="website">
<meta property="og:title" content="{{ $store['name'] }}">
<meta property="og:description" content="{{ $store['tagline'] ?? $store['metaDescription'] ?? 'فروشگاه آنلاین ' . $store['name'] }}">
<meta property="og:url" content="{{ route('home') }}">
<meta property="og:image" content="{{ asset('images/og-default.jpg') }}">
<meta property="og:site_name" content="{{ $store['name'] }}">
<meta property="og:locale" content="fa_IR">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $store['name'] }}">
<meta name="twitter:description" content="{{ $store['tagline'] ?? $store['metaDescription'] ?? 'فروشگاه آنلاین ' . $store['name'] }}">
<meta name="twitter:image" content="{{ asset('images/og-default.jpg') }}">

<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => $store['name'],
    'url' => route('home'),
    'potentialAction' => [
        '@type' => 'SearchAction',
        'target' => route('products.index').'?search={search_term_string}',
        'query-input' => 'required name=search_term_string',
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => $store['name'],
    'url' => route('home'),
    'logo' => asset('images/logo.png'),
    'sameAs' => array_values(array_filter([
        $store['instagram'] ?? null,
        $store['telegram'] ?? null,
    ])),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@endsection