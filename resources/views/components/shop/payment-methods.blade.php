@props(['selectable' => false, 'name' => 'payment_method'])

<div {{ $attributes->merge(['class' => 'shop-payment-methods']) }}>
    <h3 class="shop-payment-methods-title">روش پرداخت</h3>
    <label class="shop-payment-method is-selected">
        @if($selectable)
            <input type="radio" name="{{ $name }}" value="online" checked class="shop-payment-method-input">
        @endif
        <span class="shop-payment-method-icon" aria-hidden="true">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m3 0h3m-9.75 0H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
        </span>
        <span class="shop-payment-method-content">
            <span class="shop-payment-method-name">پرداخت آنلاین</span>
            <span class="shop-payment-method-desc">پرداخت امن از طریق درگاه زرین‌پال</span>
        </span>
        <span class="shop-payment-method-check" aria-hidden="true">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
        </span>
    </label>
    <p class="shop-payment-methods-note">پس از ثبت سفارش، به درگاه زرین‌پال هدایت می‌شوید.</p>
</div>
