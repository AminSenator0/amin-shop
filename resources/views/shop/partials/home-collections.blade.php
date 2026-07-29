@if($store['homepageCollectionsEnabled'] && !empty($homeCollections))
<section id="home-collections" class="home-section home-collections-section scroll-mt-28" aria-labelledby="home-collections-title">
    <div class="home-section-container">
        <div class="home-section-header">
            <div class="home-section-intro">
                <span class="home-section-badge home-section-badge--featured">کالکشن</span>
                <h2 id="home-collections-title" class="home-section-title">مجموعه‌های منتخب</h2>
                <p class="home-section-subtitle">مسیرهای خرید سریع بر اساس سلیقه و نیاز شما</p>
            </div>
            <a href="{{ route('products.index') }}" class="home-view-all">
                همه محصولات
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
        </div>

        <div class="home-collections-grid">
            @foreach($homeCollections as $collection)
                <a href="{{ $collection['url'] }}" class="home-collection-card group">
                    <div class="home-collection-image-wrap">
                        <img src="{{ $collection['image'] }}" alt="{{ $collection['title'] }}" loading="lazy" class="home-collection-image">
                        <div class="home-collection-overlay"></div>
                        @if($collection['badge'])
                            <span class="home-collection-badge">{{ $collection['badge'] }}</span>
                        @endif
                    </div>
                    <div class="home-collection-body">
                        <h3 class="home-collection-title">{{ $collection['title'] }}</h3>
                        <p class="home-collection-subtitle">{{ $collection['subtitle'] }}</p>
                        <span class="home-collection-cta">
                            مشاهده
                            <svg class="h-4 w-4 transition group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
