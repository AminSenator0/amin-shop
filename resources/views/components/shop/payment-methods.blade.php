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
        <span class="shop-payment-method-content flex-1 space-y-1.5">
    {{-- سطر اول: نام + تخفیف --}}
    <div class="flex items-center flex-wrap gap-2">
        <span class="shop-payment-method-name text-base font-bold text-gray-800">
            کارت به کارت
        </span>
        <span class="c2c-badge">
            <svg class="inline-block w-3.5 h-3.5 -mt-0.5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            ۱٪ تخفیف
        </span>
    </div>

    {{-- سطر دوم: توضیحات با آیکون --}}
    <div class="flex items-start gap-1.5 text-sm text-gray-500">
        <svg class="w-4 h-4 mt-0.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.875 6.75a1.875 1.875 0 01-3.75 0 1.875 1.875 0 013.75 0z" />
        </svg>
        <span class="shop-payment-method-desc leading-relaxed">
            واریز مستقیم به کارت فروشنده با شناسه یکتا
        </span>
    </div>
</span>

@if($selectable)
    <span class="shop-payment-method-check flex-shrink-0 text-green-500 {{ $selected === 'c2c' ? '' : 'hidden' }}">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M4.5 12.75l6 6 9-13.5"/>
        </svg>
    </span>
@endif
</label>
</div>

<style>
.c2c-badge {
    display: inline-flex;
    align-items: center;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #fff;
    padding: 2px 12px 2px 10px;
    border-radius: 100px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.3px;
    box-shadow: 0 1px 3px rgba(16, 185, 129, 0.3);
    line-height: 22px;
    white-space: nowrap;
}

/* بهبود ظاهر در حالت انتخاب */
.shop-payment-method.is-selected .shop-payment-method-name {
    color: #059669;
}

.shop-payment-method.is-selected .c2c-badge {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    box-shadow: 0 1px 3px rgba(245, 158, 11, 0.3);
}

/* حالت hover */
.shop-payment-method:hover .shop-payment-method-name {
    color: #0f172a;
}

.shop-payment-method:hover .c2c-badge {
    transform: scale(1.02);
    transition: transform 0.2s ease;
}
</style>
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