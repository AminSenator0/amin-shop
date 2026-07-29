@if($store['homepageAboutSnippetEnabled'] && filled($store['aboutContent']))
@php
    $aboutLines = array_values(array_filter(preg_split('/\R/u', trim($store['aboutContent']), 3)));
    $aboutPreview = $aboutLines[0] ?? '';
    $aboutSecondary = $aboutLines[1] ?? null;
@endphp
<section id="home-about" class="home-section home-about-section scroll-mt-28" aria-labelledby="home-about-title">
    <div class="home-section-container">
        <div class="home-about-panel">
            <div class="home-about-glow home-about-glow--1" aria-hidden="true"></div>
            <div class="home-about-glow home-about-glow--2" aria-hidden="true"></div>

            <div class="home-about-grid">
                <div class="home-about-visual">
                    <div class="home-about-visual-frame">
                        <picture>
                            @php $heroImage = hero_showcase_image_sources(); @endphp
                            @if($heroImage['webp'])
                                <source srcset="{{ $heroImage['webp'] }}" type="image/webp">
                            @endif
                            <img
                                src="{{ $heroImage['jpg'] ?? $heroImage['webp'] ?? hero_showcase_image_url() }}"
                                alt="{{ $store['name'] }}"
                                loading="lazy"
                                class="home-about-image"
                            >
                        </picture>
                    </div>
                    <div class="home-about-stat-card">
                        <p class="home-about-stat-value">{{ format_number($shopStats['orders'] ?? 0) }}+</p>
                        <p class="home-about-stat-label">سفارش موفق</p>
                    </div>
                </div>

                <div class="home-about-content">
                    <span class="home-section-badge">درباره ما</span>
                    <h2 id="home-about-title" class="home-about-title">{{ $store['name'] }}</h2>
                    <p class="home-about-text">{{ $aboutPreview }}</p>
                    @if($aboutSecondary)
                        <p class="home-about-text home-about-text--muted">{{ $aboutSecondary }}</p>
                    @endif

                    <ul class="home-about-features">
                        @foreach(array_slice($store['trustBadges'], 0, 3) as $feature)
                            <li class="home-about-feature">
                                <svg class="h-4 w-4 shrink-0 text-shop-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ $feature['title'] }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <a href="{{ route('pages.about') }}" class="home-about-link">
                        بیشتر بدانید
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endif
