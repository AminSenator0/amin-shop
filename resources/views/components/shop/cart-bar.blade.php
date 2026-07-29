@props([
    'cartCount' => 0,
    'subtotal' => 0,
])

@if($cartCount > 0)
    <div class="shop-cart-bar" x-data="{ show: true }" x-show="show" x-transition>
        <div class="shop-cart-bar-inner">
            <div class="shop-cart-bar-info">
                <span class="shop-cart-bar-count">{{ $cartCount }} محصول در سبد</span>
                <span class="shop-cart-bar-total">{{ format_price($subtotal) }}</span>
            </div>
            <div class="shop-cart-bar-actions">
                <button type="button" @click="show = false" class="shop-cart-bar-dismiss" aria-label="بستن">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <a href="{{ route('cart.index') }}" class="btn-primary !rounded-xl !px-5 !py-2.5 !text-sm">مشاهده سبد و ادامه</a>
            </div>
        </div>
    </div>
@endif
