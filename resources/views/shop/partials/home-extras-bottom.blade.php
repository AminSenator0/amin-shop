{{-- اخیراً مشاهده‌شده --}}
@if($store['homepageRecentlyViewedEnabled'] && $recentlyViewedProducts->isNotEmpty())
<section class="home-section home-recently-viewed-section" aria-label="اخیراً مشاهده‌شده">
    <div class="home-section-container">
        <div class="home-recently-viewed-showcase">
            <div class="home-recently-viewed-glow home-recently-viewed-glow--1" aria-hidden="true"></div>
            <div class="home-recently-viewed-glow home-recently-viewed-glow--2" aria-hidden="true"></div>
            <div class="home-recently-viewed-pattern" aria-hidden="true"></div>

            <div class="home-recently-viewed-header">
                <div class="home-recently-viewed-intro">
                    <span class="home-section-badge home-section-badge--recent">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="home-section-badge-words"><span>بازدید</span><span>اخیر</span></span>
                    </span>
                    <h2 class="home-section-title">اخیراً مشاهده‌شده</h2>
                    <p class="home-section-subtitle">ادامه از جایی که رها کردید</p>
                </div>
                <div class="home-recently-viewed-count" aria-hidden="true">
                    <span class="home-recently-viewed-count-value">{{ to_persian_digits($recentlyViewedProducts->count()) }}</span>
                    <span class="home-recently-viewed-count-label">محصول</span>
                </div>
            </div>

            <x-reviews-carousel class="recently-viewed-carousel">
                @foreach($recentlyViewedProducts as $product)
                    <div class="reviews-carousel-slide">
                        <x-recently-viewed-card :product="$product" :order="$loop->iteration" />
                    </div>
                @endforeach
            </x-reviews-carousel>
        </div>
    </div>
</section>
@endif

{{-- پیشنهاد برای شما --}}
@if($store['homepageRecommendationsEnabled'] && $recommendedProducts->isNotEmpty())
<section class="home-section home-products-section">
    <div class="home-section-container">
        <div class="home-section-header">
            <div class="home-section-intro">
                <span class="home-section-badge home-section-badge--featured">پیشنهاد</span>
                <h2 class="home-section-title">پیشنهاد برای شما</h2>
                <p class="home-section-subtitle">بر اساس علایق شما</p>
            </div>
        </div>
        <div class="home-products-grid">
            @foreach($recommendedProducts as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- نظرات مشتریان --}}
@if($store['homepageReviewsEnabled'] && $testimonialReviews->isNotEmpty())
<section id="home-reviews" class="home-section home-reviews-section scroll-mt-28">
    <div class="home-section-container">
        <div class="home-section-header">
            <div class="home-section-intro">
                <span class="home-section-badge">نظرات</span>
                <h2 class="home-section-title">نظر مشتریان</h2>
                <p class="home-section-subtitle">تجربه واقعی خریداران</p>
            </div>
        </div>
        <x-reviews-carousel>
            @foreach($testimonialReviews as $review)
                @if($review->product)
                    <div class="reviews-carousel-slide">
                        <x-home-review-card :review="$review" />
                    </div>
                @endif
            @endforeach
        </x-reviews-carousel>
    </div>
</section>
@endif

{{-- چرا ما؟ آمار واقعی --}}
@if($store['homepageWhyUsEnabled'])
<section class="home-section home-why-us-section">
    <div class="home-section-container">
        <div class="home-why-us-grid">
            <div class="home-why-us-stat"><p class="home-why-us-value">{{ format_number($shopStats['orders']) }}+</p><p class="home-why-us-label">سفارش موفق</p></div>
            <div class="home-why-us-stat"><p class="home-why-us-value">{{ format_number($shopStats['customers']) }}+</p><p class="home-why-us-label">مشتری</p></div>
            <div class="home-why-us-stat"><p class="home-why-us-value">{{ to_persian_digits(number_format($shopStats['avgRating'], 1)) }}</p><p class="home-why-us-label">میانگین امتیاز</p></div>
            <div class="home-why-us-stat"><p class="home-why-us-value">{{ format_number($shopStats['reviews']) }}+</p><p class="home-why-us-label">نظر ثبت‌شده</p></div>
        </div>
    </div>
</section>
@endif

{{-- ویدیو --}}
@if($store['homepageVideoEnabled'] && $store['homepageVideoUrl'])
<section class="home-section home-video-section">
    <div class="home-section-container">
        <div class="home-section-header">
            <div class="home-section-intro">
                <span class="home-section-badge">ویدیو</span>
                <h2 class="home-section-title">معرفی فروشگاه</h2>
            </div>
        </div>
        <div class="home-video-wrap">
            <iframe src="{{ $store['homepageVideoUrl'] }}" title="ویدیوی معرفی فروشگاه" class="home-video-iframe" loading="lazy" allowfullscreen></iframe>
        </div>
    </div>
</section>
@endif

{{-- FAQ --}}
@if($store['homepageFaqEnabled'] && $faqs->isNotEmpty())
<section id="home-faq" class="home-section home-faq-section scroll-mt-28">
    <div class="home-section-container">
        <div class="mx-auto max-w-3xl">
        <div class="home-section-header">
            <div class="home-section-intro">
                <span class="home-section-badge">سوالات</span>
                <h2 class="home-section-title">سوالات متداول</h2>
            </div>
            @if($showFaqViewAll)
                <a href="{{ route('pages.faq') }}" class="home-view-all">مشاهده همه <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg></a>
            @endif
        </div>
        <div class="home-faq-list" x-data="{ open: null }">
            @foreach($faqs as $faq)
                <div class="home-faq-item">
                    <button type="button" class="home-faq-question" @click="open = open === {{ $faq->id }} ? null : {{ $faq->id }}" :aria-expanded="open === {{ $faq->id }}">
                        <span>{{ $faq->question }}</span>
                        <svg class="h-5 w-5 shrink-0 transition-transform" :class="open === {{ $faq->id }} && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                    </button>
                    <div x-show="open === {{ $faq->id }}" x-cloak class="home-faq-answer">{{ $faq->answer }}</div>
                </div>
            @endforeach
        </div>
        </div>
    </div>
</section>
@endif

{{-- بلاگ --}}
@if($store['homepageBlogEnabled'] && $blogPosts->isNotEmpty())
<section class="home-section home-blog-section">
    <div class="home-section-container">
        <div class="home-section-header">
            <div class="home-section-intro">
                <span class="home-section-badge">بلاگ</span>
                <h2 class="home-section-title">آخرین مقالات</h2>
            </div>
            <a href="{{ route('blog.index') }}" class="home-view-all">همه مقالات <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg></a>
        </div>
        <div class="home-blog-grid">
            @foreach($blogPosts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="home-blog-card group">
                    <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}" loading="lazy" class="home-blog-image">
                    <div class="home-blog-body">
                        <time class="home-blog-date">{{ $post->published_at ? format_jalali($post->published_at) : '' }}</time>
                        <h3 class="home-blog-title group-hover:text-shop-primary">{{ $post->title }}</h3>
                        <p class="home-blog-excerpt">{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 120) }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- تماس سریع --}}
@if($store['homepageContactSectionEnabled'] && ($store['contactPhone'] || $store['whatsappUrl']))
<section class="home-section home-contact-section">
    <div class="home-section-container">
        <div class="home-contact-banner">
            <div>
                <h2 class="home-contact-title">نیاز به راهنمایی دارید؟</h2>
                <p class="home-contact-desc">تیم پشتیبانی ما آماده پاسخگویی است — {{ $store['contactHours'] }}</p>
            </div>
            <div class="home-contact-actions">
                @if($store['contactPhone'])
                    <a href="tel:{{ normalize_mobile($store['contactPhone']) }}" class="home-contact-btn">{{ $store['contactPhone'] }}</a>
                @endif
                @if($store['whatsappUrl'])
                    <a href="{{ $store['whatsappUrl'] }}" target="_blank" rel="noopener" class="home-contact-btn home-contact-btn--whatsapp">واتساپ</a>
                @endif
                <a href="{{ route('pages.contact') }}" class="home-contact-btn home-contact-btn--outline">فرم تماس</a>
            </div>
        </div>
    </div>
</section>
@endif

{{-- نقشه --}}
@if($store['homepageMapEnabled'] && $store['mapsUrl'])
<section class="home-section home-map-section">
    <div class="home-section-container">
        <div class="home-section-header">
            <div class="home-section-intro">
                <h2 class="home-section-title">موقعیت فروشگاه</h2>
                @if($store['contactAddress'])<p class="home-section-subtitle">{{ $store['contactAddress'] }}</p>@endif
            </div>
        </div>
        <a href="{{ $store['mapsUrl'] }}" target="_blank" rel="noopener" class="home-map-link">
            <div class="home-map-placeholder">
                <svg class="h-10 w-10 text-shop-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                <span>مشاهده روی نقشه</span>
            </div>
        </a>
    </div>
</section>
@endif

{{-- بنر اپ --}}
@if($store['homepageAppBannerEnabled'] && $store['homepageAppDownloadUrl'])
<section class="home-section home-app-section">
    <div class="home-section-container">
        <div class="home-app-banner">
            <div>
                <h2 class="home-app-title">{{ $store['homepageAppDownloadText'] }}</h2>
                <p class="home-app-desc">خرید سریع‌تر با اپلیکیشن موبایل</p>
            </div>
            <a href="{{ $store['homepageAppDownloadUrl'] }}" target="_blank" rel="noopener" class="home-app-btn">دانلود</a>
        </div>
    </div>
</section>
@endif

{{-- اینستاگرام --}}
@if(filled($store['socialInstagramEmbed']))
<section class="home-section home-instagram-section">
    <div class="home-section-container">
        <div class="home-section-header">
            <div class="home-section-intro">
                <span class="home-section-badge">اینستاگرام</span>
                <h2 class="home-section-title">ما را دنبال کنید</h2>
            </div>
        </div>
        <div class="home-instagram-embed">{!! $store['socialInstagramEmbed'] !!}</div>
    </div>
</section>
@endif
