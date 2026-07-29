{{-- پیشنهاد زمان‌دار (حراج محدود) --}}
@if($store['homepageFlashSaleEnabled'] && $flashSaleEndsAt && $flashSaleProducts->isNotEmpty())
<section id="home-flash-sale" class="home-section home-flash-section scroll-mt-28" aria-label="پیشنهاد زمان‌دار" x-data="flashCountdown('{{ $flashSaleEndsAt->toIso8601String() }}')" x-init="start()">
    <div class="home-section-container">
        <div class="home-flash-panel">
            <div class="home-flash-glow home-flash-glow--1" aria-hidden="true"></div>
            <div class="home-flash-glow home-flash-glow--2" aria-hidden="true"></div>
            <div class="home-flash-pattern" aria-hidden="true"></div>

            <div class="home-flash-top">
                <div class="home-flash-intro">
                    <span class="home-flash-badge">
                        <svg class="home-flash-badge-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                        پیشنهاد زمان‌دار
                    </span>
                    <h2 class="home-flash-title">فرصت محدود</h2>
                    <p class="home-flash-subtitle">تخفیف‌های ویژه — فقط تا پایان شمارش معکوس</p>
                </div>

                <div class="home-flash-timer-wrap">
                    <span class="home-flash-timer-caption">زمان باقی‌مانده</span>
                    <div class="home-flash-timer" aria-live="polite">
                        <div class="home-flash-timer-unit">
                            <span x-text="days" class="home-flash-timer-value">۰۰</span>
                            <span class="home-flash-timer-label">روز</span>
                        </div>
                        <span class="home-flash-timer-sep" aria-hidden="true">:</span>
                        <div class="home-flash-timer-unit">
                            <span x-text="hours" class="home-flash-timer-value">۰۰</span>
                            <span class="home-flash-timer-label">ساعت</span>
                        </div>
                        <span class="home-flash-timer-sep" aria-hidden="true">:</span>
                        <div class="home-flash-timer-unit">
                            <span x-text="minutes" class="home-flash-timer-value">۰۰</span>
                            <span class="home-flash-timer-label">دقیقه</span>
                        </div>
                        <span class="home-flash-timer-sep" aria-hidden="true">:</span>
                        <div class="home-flash-timer-unit home-flash-timer-unit--seconds">
                            <span x-text="seconds" class="home-flash-timer-value">۰۰</span>
                            <span class="home-flash-timer-label">ثانیه</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="home-flash-body">
                <x-reviews-carousel class="home-flash-carousel">
                    @foreach($flashSaleProducts as $product)
                        <div class="reviews-carousel-slide">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </x-reviews-carousel>
                <div class="home-flash-footer">
                    <a href="{{ route('products.index', ['sort' => 'discount']) }}" class="home-flash-cta">
                        مشاهده همه تخفیف‌ها
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- برندها --}}
@if($store['homepageBrandsEnabled'] && $brands->isNotEmpty())
<section class="home-section home-brands-section" aria-label="برندهای معتبر">
    <div class="home-section-container">
        <div class="home-section-header">
            <div class="home-section-intro">
                <span class="home-section-badge">برندها</span>
                <h2 class="home-section-title">برندهای معتبر</h2>
                <p class="home-section-subtitle">خرید از برندهای محبوب و معتبر با ضمانت اصالت کالا</p>
            </div>
            <a href="{{ route('products.index') }}#brands" class="home-view-all">
                همه برندها
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            </a>
        </div>

        <div class="home-brands-showcase">
            <div class="home-brands-showcase-glow home-brands-showcase-glow--1" aria-hidden="true"></div>
            <div class="home-brands-showcase-glow home-brands-showcase-glow--2" aria-hidden="true"></div>
            <div class="home-brands-grid">
                @foreach($brands as $brand)
                    <a
                        href="{{ route('products.index', ['brand' => $brand->slug]) }}"
                        class="home-brand-card group"
                        title="{{ $brand->name }}"
                    >
                        <div class="home-brand-logo-wrap">
                            <img
                                src="{{ brand_logo_url($brand->logo, $brand->slug, $loop->iteration) }}"
                                alt="{{ $brand->name }}"
                                loading="lazy"
                                class="home-brand-logo"
                            >
                        </div>
                        <div class="home-brand-meta">
                            <span class="home-brand-name">{{ $brand->name }}</span>
                            @if(($brand->products_count ?? 0) > 0)
                                <span class="home-brand-count">{{ number_format($brand->products_count) }} محصول</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
