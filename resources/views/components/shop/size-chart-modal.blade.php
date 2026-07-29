@props(['product'])

@if($product->hasSizeChart())
    @php
        $chart = $product->size_chart;
    @endphp

    <div x-data="{ open: false }" class="product-size-chart">
        <button type="button" class="product-size-chart-trigger" @click="open = true">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5M7.5 6.75h.008v.008H7.5V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM7.5 12h.008v.008H7.5V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.008v.008H7.5v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
            راهنمای سایز
        </button>

        <div
            x-show="open"
            x-cloak
            class="product-size-chart-overlay"
            @keydown.escape.window="open = false"
        >
            <div class="product-size-chart-backdrop" @click="open = false"></div>
            <div
                class="product-size-chart-dialog"
                role="dialog"
                aria-modal="true"
                aria-labelledby="size-chart-title-{{ $product->id }}"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <div class="product-size-chart-header">
                    <div>
                        <h2 id="size-chart-title-{{ $product->id }}" class="product-size-chart-title">جدول سایزبندی</h2>
                        <p class="product-size-chart-subtitle">قبل از خرید، اندازه‌های خود را با جدول زیر مقایسه کنید.</p>
                    </div>
                    <button type="button" class="product-size-chart-close" @click="open = false" aria-label="بستن">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <x-shop.size-chart-table-content :chart="$chart" variant="modal" />

                <p class="product-size-chart-note">اندازه‌ها تقریبی هستند و ممکن است بسته به مدل کمی متفاوت باشند.</p>
            </div>
        </div>
    </div>
@endif
