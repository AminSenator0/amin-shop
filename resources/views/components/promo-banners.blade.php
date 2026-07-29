@props(['banners'])

@php
    $displayBanners = $banners->take(4);
    $heroBanner = $displayBanners->count() > 1 ? $displayBanners->first() : null;
    $clusterBanners = $heroBanner ? $displayBanners->skip(1) : $displayBanners;
@endphp

@if($displayBanners->isNotEmpty())
<div class="home-banners-bento" data-banner-count="{{ $displayBanners->count() }}">
    @if($heroBanner)
        @php
            $imageUrl = banner_image_url($heroBanner->image, 1);
            $description = $heroBanner->description;
            $showDescription = filled($description) && ! str_starts_with($description, 'بنر تبلیغاتی');
        @endphp
        <a
            href="{{ $heroBanner->link ?? route('products.index') }}"
            class="home-banner-card home-banner-card--hero group"
        >
            <img src="{{ $imageUrl }}" alt="{{ $heroBanner->title }}" class="home-banner-img" loading="lazy">
            <div class="home-banner-overlay" aria-hidden="true"></div>

            <div class="home-banner-content">
                <span class="home-banner-eyebrow">پیشنهاد ویژه</span>
                <h3 class="home-banner-title">{{ $heroBanner->title }}</h3>
                @if($showDescription)
                    <p class="home-banner-desc">{{ $description }}</p>
                @endif
                <span class="home-banner-link">
                    مشاهده
                    <svg class="h-4 w-4 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                </span>
            </div>
        </a>
    @endif

    @if($clusterBanners->isNotEmpty())
        <div class="home-banners-bento-cluster">
            @foreach($clusterBanners as $banner)
                @php
                    $imageUrl = banner_image_url($banner->image, $loop->iteration + ($heroBanner ? 1 : 0));
                    $description = $banner->description;
                    $showDescription = filled($description) && ! str_starts_with($description, 'بنر تبلیغاتی');
                    $isWide = $loop->last && $clusterBanners->count() >= 2;
                @endphp
                <a
                    href="{{ $banner->link ?? route('products.index') }}"
                    class="home-banner-card group {{ $isWide ? 'home-banner-card--wide' : '' }}"
                >
                    <img src="{{ $imageUrl }}" alt="{{ $banner->title }}" class="home-banner-img" loading="lazy">
                    <div class="home-banner-overlay" aria-hidden="true"></div>

                    <div class="home-banner-content">
                        <span class="home-banner-eyebrow">پیشنهاد ویژه</span>
                        <h3 class="home-banner-title">{{ $banner->title }}</h3>
                        @if($showDescription)
                            <p class="home-banner-desc">{{ $description }}</p>
                        @endif
                        <span class="home-banner-link">
                            مشاهده
                            <svg class="h-4 w-4 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endif
