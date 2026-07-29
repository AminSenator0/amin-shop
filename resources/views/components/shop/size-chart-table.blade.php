@props(['product'])

@if($product->hasSizeChart())
    @php $chart = $product->size_chart; @endphp

    <div class="product-size-chart-inline">
        <div class="product-size-chart-inline-header">
            <p class="product-size-chart-inline-intro">قبل از انتخاب سایز، اندازه‌های خود را با جدول زیر مقایسه کنید.</p>
            <span class="product-size-chart-unit-badge">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5L7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5"/></svg>
                سانتی‌متر
            </span>
        </div>

        <x-shop.size-chart-table-content :chart="$chart" variant="inline" />

        <p class="product-size-chart-inline-note">اندازه‌ها تقریبی هستند و ممکن است بسته به مدل کمی متفاوت باشند.</p>
    </div>
@endif
