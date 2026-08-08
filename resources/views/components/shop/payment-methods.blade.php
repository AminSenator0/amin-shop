@props(['selectable' => false, 'name' => 'payment_method'])

@php
    $selected = old($name, 'online');
@endphp

<div {{ $attributes->merge(['class' => 'shop-payment-methods']) }}>
    <h3 class="shop-payment-methods-title text-lg font-semibold mb-4">روش پرداخت</h3>

    {{-- زرین‌پال --}}
    <label class="shop-payment-method flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all duration-200 hover:border-blue-400 {{ $selectable && $selected === 'online' ? 'is-selected border-green-500 bg-green-50' : 'border-gray-200' }}">
        @if($selectable)
            <input type="radio" name="{{ $name }}" value="online"
                {{ $selected === 'online' ? 'checked' : '' }}
                class="shop-payment-method-input hidden">
        @endif
        <span class="shop-payment-method-icon text-gray-600" aria-hidden="true">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m3 0h3m-9.75 0H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.251 2.251 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
            </svg>
        </span>
        <span class="shop-payment-method-content flex-1">
            <span class="shop-payment-method-name block font-medium">پرداخت آنلاین</span>
            <span class="shop-payment-method-desc text-sm text-gray-500">پرداخت امن از طریق درگاه زرین‌پال</span>
        </span>
        @if($selectable)
            <span class="shop-payment-method-check text-green-500 {{ $selected === 'online' ? '' : 'hidden' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
            </span>
        @endif
    </label>

    {{-- کارت به کارت --}}
    <label class="shop-payment-method flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition-all duration-200 hover:border-blue-400 mt-2 {{ $selectable && $selected === 'c2c' ? 'is-selected border-green-500 bg-green-50' : 'border-gray-200' }}">
        @if($selectable)
            <input type="radio" name="{{ $name }}" value="c2c"
                {{ $selected === 'c2c' ? 'checked' : '' }}
                class="shop-payment-method-input hidden">
        @endif
        <span class="shop-payment-method-icon text-gray-600" aria-hidden="true">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
            </svg>
        </span>
        <span class="shop-payment-method-content flex-1">
            <span class="shop-payment-method-name block font-medium">کارت به کارت</span>
            <span class="shop-payment-method-desc text-sm text-gray-500">واریز مستقیم به کارت فروشنده با شناسه یکتا</span>
        </span>
        @if($selectable)
            <span class="shop-payment-method-check text-green-500 {{ $selected === 'c2c' ? '' : 'hidden' }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
            </span>
        @endif
    </label>
</div>

{{-- اسکریپت برای تغییر کلاس و نمایش علامت --}}
@if($selectable)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const labels = document.querySelectorAll('.shop-payment-method');
            labels.forEach(label => {
                const radio = label.querySelector('input[type="radio"]');
                if (radio) {
                    radio.addEventListener('change', function() {
                        // حذف کلاس is-selected از همه labelها
                        labels.forEach(l => l.classList.remove('is-selected', 'border-green-500', 'bg-green-50'));
                        // اضافه کردن کلاس به label والد این رادیو
                        if (this.checked) {
                            const parentLabel = this.closest('.shop-payment-method');
                            parentLabel.classList.add('is-selected', 'border-green-500', 'bg-green-50');
                            // نمایش علامت چک در این label و مخفی کردن در بقیه
                            labels.forEach(l => {
                                const check = l.querySelector('.shop-payment-method-check');
                                if (check) {
                                    if (l === parentLabel) {
                                        check.classList.remove('hidden');
                                    } else {
                                        check.classList.add('hidden');
                                    }
                                }
                            });
                        }
                    });
                }
            });
        });
    </script>
@endif