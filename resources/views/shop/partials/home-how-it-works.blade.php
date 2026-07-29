@if($store['homepageHowItWorksEnabled'])
<section id="home-how-it-works" class="home-section home-how-section scroll-mt-28" aria-labelledby="home-how-title">
    <div class="home-section-container">
        <div class="home-how-panel">
            <div class="home-how-glow" aria-hidden="true"></div>

            <div class="home-section-header home-how-header">
                <div class="home-section-intro">
                    <span class="home-section-badge">نحوه خرید</span>
                    <h2 id="home-how-title" class="home-section-title">خرید در ۳ قدم ساده</h2>
                    <p class="home-section-subtitle">از انتخاب تا تحویل — مسیر شفاف و سریع</p>
                </div>
            </div>

            <div class="home-how-steps">
                @foreach([
                    ['step' => '۱', 'title' => 'انتخاب محصول', 'desc' => 'محصول مورد نظر را جستجو کنید و به سبد اضافه کنید.', 'icon' => 'M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5V6a3.75 3.75 0 117.5 0v4.5'],
                    ['step' => '۲', 'title' => 'پرداخت امن', 'desc' => 'اطلاعات ارسال را وارد کنید و از درگاه زرین‌پال پرداخت کنید.', 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m3 0h3m-9.75 0H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z'],
                    ['step' => '۳', 'title' => 'ارسال و تحویل', 'desc' => 'سفارش آماده و ارسال می‌شود — کد پیگیری در پنل شماست.', 'icon' => 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12'],
                ] as $item)
                    <article class="home-how-step">
                        <div class="home-how-step-icon-wrap">
                            <span class="home-how-step-number">{{ $item['step'] }}</span>
                            <svg class="home-how-step-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/></svg>
                        </div>
                        <h3 class="home-how-step-title">{{ $item['title'] }}</h3>
                        <p class="home-how-step-desc">{{ $item['desc'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
