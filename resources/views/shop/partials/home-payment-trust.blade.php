@if($store['homepagePaymentTrustEnabled'] && ($store['paymentGateways'] || $store['enamadImageUrl'] || $store['enamadUrl']))
<section class="home-payment-trust" aria-label="پرداخت امن و نماد اعتماد">
    <div class="home-payment-trust-inner">
        <div class="home-payment-trust-intro">
            <span class="home-payment-trust-icon" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
            </span>
            <div>
                <p class="home-payment-trust-title">پرداخت امن و مطمئن</p>
                <p class="home-payment-trust-desc">تراکنش‌های شما از طریق درگاه زرین‌پال انجام می‌شود</p>
            </div>
        </div>

        <div class="home-payment-trust-badges">
            @foreach($store['paymentGateways'] ?: ['زرین‌پال'] as $gateway)
                <span class="home-payment-trust-badge">{{ $gateway }}</span>
            @endforeach

            @if($store['enamadImageUrl'] && $store['enamadUrl'])
                <a href="{{ $store['enamadUrl'] }}" target="_blank" rel="noopener" class="home-payment-trust-enamad" title="نماد اعتماد الکترونیکی">
                    <img src="{{ $store['enamadImageUrl'] }}" alt="نماد اعتماد الکترونیکی" loading="lazy" class="home-payment-trust-enamad-img">
                </a>
            @elseif($store['enamadImageUrl'])
                <span class="home-payment-trust-enamad">
                    <img src="{{ $store['enamadImageUrl'] }}" alt="نماد اعتماد الکترونیکی" loading="lazy" class="home-payment-trust-enamad-img">
                </span>
            @elseif($store['enamadUrl'])
                <a href="{{ $store['enamadUrl'] }}" target="_blank" rel="noopener" class="home-payment-trust-badge home-payment-trust-badge--enamad">اینماد</a>
            @endif
        </div>
    </div>
</section>
@endif
