@if($store['homepageQuickTrackEnabled'])
<div class="home-quick-track-wrap">
    <form action="{{ route('orders.track') }}" method="GET" class="home-quick-track">
        <div class="home-quick-track-icon" aria-hidden="true">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="home-quick-track-body">
            <p class="home-quick-track-title">پیگیری سفارش</p>
            <p class="home-quick-track-desc hidden sm:block">کد پیگیری را وارد کنید</p>
        </div>
        <div class="home-quick-track-form">
            <input type="text" name="code" placeholder="کد پیگیری..." class="home-quick-track-input" autocomplete="off" aria-label="کد پیگیری سفارش">
            <input type="text" name="phone" placeholder="موبایل" class="home-quick-track-input !max-w-[7rem]" dir="ltr" autocomplete="off" aria-label="شماره موبایل">
            <button type="submit" class="home-quick-track-btn" aria-label="پیگیری سفارش">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            </button>
        </div>
    </form>
</div>
@endif
