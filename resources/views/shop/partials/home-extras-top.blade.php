{{-- نوار اعلان یکپارچه --}}
@if(
    ($store['homepageCouponBarEnabled'] && $featuredCoupon)
    || ($store['homepageFreeShippingBarEnabled'] && $store['freeShippingThreshold'] > 0)
)
<section class="home-announcement" aria-label="اعلان‌های فروشگاه">
    <div class="home-announcement-shine" aria-hidden="true"></div>
    <div class="home-announcement-inner">
        @if($store['homepageCouponBarEnabled'] && $featuredCoupon)
            <div
                class="home-announcement-pill home-announcement-pill--coupon"
                x-data="{ copied: false }"
            >
                <span class="home-announcement-pill-icon" aria-hidden="true">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/></svg>
                </span>
                <span class="home-announcement-pill-body">
                    <span class="home-announcement-pill-label">کد تخفیف</span>
                    <button
                        type="button"
                        class="home-announcement-code"
                        dir="ltr"
                        :aria-label="copied ? 'کد کپی شد' : 'کپی کد {{ $featuredCoupon->code }}'"
                        @click="navigator.clipboard.writeText('{{ $featuredCoupon->code }}'); copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <span x-show="!copied">{{ $featuredCoupon->code }}</span>
                        <span x-show="copied" x-cloak>کپی شد ✓</span>
                    </button>
                    <span class="home-announcement-pill-value">{{ $featuredCoupon->valueLabel() }} تخفیف</span>
                    @if($featuredCoupon->min_order > 0)
                        <span class="home-announcement-muted">حداقل {{ format_price($featuredCoupon->min_order) }}</span>
                    @endif
                </span>
            </div>
        @endif

        @if($store['homepageCouponBarEnabled'] && $featuredCoupon && $store['homepageFreeShippingBarEnabled'] && $store['freeShippingThreshold'] > 0)
            <span class="home-announcement-divider" aria-hidden="true"></span>
        @endif

        @if($store['homepageFreeShippingBarEnabled'] && $store['freeShippingThreshold'] > 0)
            <div class="home-announcement-pill home-announcement-pill--shipping">
                <span class="home-announcement-pill-icon" aria-hidden="true">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                </span>
                <span class="home-announcement-pill-body">
                    <span class="home-announcement-pill-label">ارسال رایگان</span>
                    <span class="home-announcement-pill-value">بالای {{ format_price($store['freeShippingThreshold']) }}</span>
                </span>
            </div>
        @endif
    </div>
</section>
@endif
