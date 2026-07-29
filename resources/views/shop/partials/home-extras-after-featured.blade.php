{{-- پرفروش‌ترین‌ها --}}
@if($store['homepageBestsellersEnabled'] && $bestsellerProducts->isNotEmpty())
<section id="home-bestsellers" class="home-section home-bestsellers-section scroll-mt-28">
    <div class="home-section-container">
        <div class="home-bestsellers-showcase">
            <div class="home-bestsellers-showcase-glow home-bestsellers-showcase-glow--1" aria-hidden="true"></div>
            <div class="home-bestsellers-showcase-glow home-bestsellers-showcase-glow--2" aria-hidden="true"></div>
            <div class="home-bestsellers-pattern" aria-hidden="true"></div>

            <div class="home-section-header home-bestsellers-header">
                <div class="home-section-intro">
                    <span class="home-section-badge home-section-badge--bestseller">
                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        پرفروش
                    </span>
                    <h2 class="home-section-title">پرفروش‌ترین‌ها</h2>
                    <p class="home-section-subtitle">محبوب‌ترین انتخاب مشتریان در این فروشگاه</p>
                </div>
                <a href="{{ route('products.index', ['sort' => 'bestseller']) }}" class="home-view-all home-view-all--bestseller">
                    مشاهده همه
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                </a>
            </div>

            <div class="home-products-grid relative z-10">
                @foreach($bestsellerProducts as $product)
                    <x-product-card :product="$product" variant="featured" />
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

{{-- تخفیف‌دار --}}
@if($store['homepageDiscountedEnabled'] && $discountedProducts->isNotEmpty())
<section id="home-discounted" class="home-section home-discounted-section scroll-mt-28" aria-label="محصولات تخفیف‌دار">
    <div class="home-section-container">
        <div class="home-discounted-showcase">
            <div class="home-discounted-showcase-glow home-discounted-showcase-glow--1" aria-hidden="true"></div>
            <div class="home-discounted-showcase-glow home-discounted-showcase-glow--2" aria-hidden="true"></div>
            <div class="home-discounted-pattern" aria-hidden="true"></div>

            <div class="home-section-header home-discounted-header">
                <div class="home-section-intro">
                    <span class="home-section-badge home-section-badge--sale">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.236 2.573l1.5 9A2.25 2.25 0 006 18h12.75a2.25 2.25 0 002.236-2.573l-1.5-9z"/></svg>
                        تخفیف ویژه
                    </span>
                    <h2 class="home-section-title">محصولات تخفیف‌دار</h2>
                    <p class="home-section-subtitle">پرفروش‌ترین پیشنهادها با بیشترین درصد تخفیف</p>
                </div>

                <div class="home-discounted-actions">
                    @if(($heroMaxDiscount ?? 0) > 0)
                        <div class="home-discounted-highlight">
                            <span class="home-discounted-highlight-label">بیشترین تخفیف</span>
                            <span class="home-discounted-highlight-value">تا {{ to_persian_digits((string) $heroMaxDiscount) }}٪</span>
                        </div>
                    @endif
                    <a href="{{ route('products.index', ['sort' => 'discount']) }}" class="home-view-all home-view-all--sale">
                        مشاهده همه
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                    </a>
                </div>
            </div>

            <x-reviews-carousel class="home-discounted-carousel">
                @foreach($discountedProducts as $product)
                    <div class="reviews-carousel-slide">
                        <x-product-card :product="$product" variant="featured" />
                    </div>
                @endforeach
            </x-reviews-carousel>
        </div>
    </div>
</section>
@endif
