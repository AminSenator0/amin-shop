@if($store['homepageSizeGuideEnabled'] && !empty($sizeChartSample))
<section id="home-size-guide" class="home-section home-size-guide-section scroll-mt-28" aria-labelledby="home-size-guide-title">
    <div class="home-section-container">
        <div class="home-size-guide-panel" x-data="{ open: false }">
            <div class="home-size-guide-copy">
                <span class="home-section-badge">راهنما</span>
                <h2 id="home-size-guide-title" class="home-section-title">راهنمای انتخاب سایز</h2>
                <p class="home-size-guide-desc">قبل از خرید پوشاک، اندازه‌های خود را با جدول سایزبندی مقایسه کنید تا بهترین انتخاب را داشته باشید.</p>
                <button type="button" class="home-size-guide-btn" @click="open = true">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/></svg>
                    مشاهده جدول سایز
                </button>
            </div>

            <div class="home-size-guide-preview" aria-hidden="true">
                <div class="home-size-guide-preview-table">
                    <div class="home-size-guide-preview-head">
                        <span>سایز</span>
                        @foreach(array_slice($sizeChartSample['columns'] ?? [], 0, 2) as $column)
                            <span>{{ $column }}</span>
                        @endforeach
                    </div>
                    @foreach(array_slice($sizeChartSample['rows'] ?? [], 0, 3) as $row)
                        <div class="home-size-guide-preview-row">
                            <span>{{ $row['size'] }}</span>
                            @foreach(array_slice($row['values'] ?? [], 0, 2) as $value)
                                <span dir="ltr">{{ $value }}</span>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            <div x-show="open" x-cloak class="product-size-chart-overlay" @keydown.escape.window="open = false">
                <div class="product-size-chart-backdrop" @click="open = false"></div>
                <div
                    class="product-size-chart-dialog"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="home-size-chart-title"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                >
                    <div class="product-size-chart-header">
                        <div>
                            <h2 id="home-size-chart-title" class="product-size-chart-title">جدول سایزبندی</h2>
                            <p class="product-size-chart-subtitle">اندازه‌های خود را با جدول زیر مقایسه کنید.</p>
                        </div>
                        <button type="button" class="product-size-chart-close" @click="open = false" aria-label="بستن">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <x-shop.size-chart-table-content :chart="$sizeChartSample" variant="modal" />
                </div>
            </div>
        </div>
    </div>
</section>
@endif
