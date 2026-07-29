@props(['product', 'inWishlist' => false, 'reviewCount' => 0, 'avgRating' => 0, 'compact' => false])

@php
    $cartQty = app(\App\Services\CartService::class)->quantityFor($product->id);
    $qualifiesFreeShipping = $store['freeShippingThreshold'] > 0 && $product->price >= $store['freeShippingThreshold'];
    $remainingForFreeShipping = $store['freeShippingThreshold'] > 0
        ? max(0, $store['freeShippingThreshold'] - $product->price)
        : 0;
@endphp

<div {{ $attributes->merge(['class' => 'product-purchase-panel' . ($compact ? ' product-purchase-panel--compact' : '')]) }}>
    @unless($compact)
        <div class="product-purchase-badges">
            <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="badge">{{ $product->category->name }}</a>
            @if($product->is_featured)
                <span class="product-badge product-badge--featured">ویژه</span>
            @endif
            @if($product->hasDiscount())
                <span class="product-badge product-badge--sale">{{ $product->discountPercent() }}٪ تخفیف</span>
            @endif
            @if($product->isLowStock())
                <span class="product-badge product-badge--stock">تنها {{ format_number($product->stock) }} عدد باقی‌مانده</span>
            @endif
        </div>

        <h1 class="product-detail-title">{{ $product->name }}</h1>

        @if($product->short_description)
            <p class="product-detail-short-desc">{{ $product->short_description }}</p>
        @endif

        <div class="product-detail-meta">
            @if($product->brand)
                <span class="product-meta-item">
                    <span class="product-meta-label">برند</span>
                    <a href="{{ route('products.index', ['brand' => $product->brand->slug]) }}" class="product-meta-value product-meta-link">{{ $product->brand->name }}</a>
                </span>
            @endif
            <span class="product-meta-item">
                <span class="product-meta-label">کد کالا</span>
                <button
                    type="button"
                    class="product-meta-value product-meta-copy"
                    x-data="{ copied: false }"
                    @click="navigator.clipboard.writeText('{{ $product->sku }}'); copied = true; setTimeout(() => copied = false, 2000)"
                    title="کپی کد کالا"
                >
                    <span dir="ltr">{{ $product->sku }}</span>
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9m9 9.75V8.625c0-.621-.504-1.125-1.125-1.125H18.75a9 9 0 00-9 9v1.5m12-3.375V9.375c0-.621-.504-1.125-1.125-1.125H18.75"/></svg>
                    <span x-show="copied" x-cloak class="product-meta-copied">کپی شد!</span>
                </button>
            </span>
            <span class="product-meta-item">
                <span class="product-meta-label">وضعیت</span>
                @if($product->isInStock())
                    <span class="product-meta-value product-meta-value--in-stock">موجود</span>
                @else
                    <span class="product-meta-value product-meta-value--out-of-stock">ناموجود</span>
                @endif
            </span>
        </div>

        @if($reviewCount > 0)
            <a href="#reviews" class="product-rating-summary" @click.prevent="$dispatch('open-product-reviews')">
                <x-star-rating :rating="round($avgRating)" size="sm" />
                <span class="product-rating-value">{{ to_persian_digits(number_format($avgRating, 1)) }}</span>
                <span class="product-rating-count">({{ format_number($reviewCount) }} نظر)</span>
            </a>
        @endif
    @endunless

    <div class="product-price-block">
        @if($product->hasDiscount())
            <div class="product-price-row">
                <span class="product-price-current">{{ format_price($product->price) }}</span>
                <span class="product-price-old">{{ format_price($product->compare_price) }}</span>
            </div>
            <p class="product-price-savings">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z"/></svg>
                {{ format_price($product->savingsAmount()) }} صرفه‌جویی
            </p>
        @else
            <span class="product-price-current">{{ format_price($product->price) }}</span>
        @endif

        @if($store['freeShippingThreshold'] > 0)
            <div class="product-shipping-hint {{ $qualifiesFreeShipping ? 'product-shipping-hint--free' : '' }}">
                @if($qualifiesFreeShipping)
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                    <span>ارسال رایگان برای این محصول</span>
                @else
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                    <span>{{ format_price($remainingForFreeShipping) }} تا ارسال رایگان</span>
                @endif
            </div>
        @endif
    </div>

    <div class="product-purchase-actions" id="product-purchase-options">
        @if($product->isInStock())
            <form method="POST" action="{{ route('cart.store') }}" class="product-cart-form">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                <div class="product-cart-form-fields">
                    @if($product->hasSizes())
                        <div class="product-option-group">
                            <div class="product-option-label-row">
                                <span class="product-option-label">سایز <span class="text-red-500">*</span></span>
                                <x-shop.size-chart-modal :product="$product" />
                            </div>
                            <div class="product-option-list" role="listbox" aria-label="انتخاب سایز">
                                @foreach($product->sizes as $size)
                                    <button
                                        type="button"
                                        class="product-option-btn"
                                        :class="{ 'is-selected': size === @js($size) }"
                                        @click="size = @js($size)"
                                    >{{ $size }}</button>
                                @endforeach
                            </div>
                            <input type="hidden" name="size" :value="size">
                            @error('size')<p class="product-option-error">{{ $message }}</p>@enderror
                        </div>
                    @endif

                    @if($product->hasColors())
                        <div class="product-option-group">
                            <span class="product-option-label">رنگ <span class="text-red-500">*</span></span>
                            <div class="product-option-list" role="listbox" aria-label="انتخاب رنگ">
                                @foreach($product->colors as $color)
                                    <button
                                        type="button"
                                        class="product-option-btn"
                                        :class="{ 'is-selected': color === @js($color) }"
                                        @click="color = @js($color)"
                                    >{{ $color }}</button>
                                @endforeach
                            </div>
                            <input type="hidden" name="color" :value="color">
                            @error('color')<p class="product-option-error">{{ $message }}</p>@enderror
                        </div>
                    @endif

                    <div class="product-option-group">
                        <span class="product-option-label">تعداد</span>
                        <div class="product-qty-stepper" dir="ltr">
                            <button type="button" class="product-qty-btn" @click="qty = Math.max(1, qty - 1)" aria-label="کاهش تعداد">−</button>
                            <input type="text" name="quantity" x-model="qty" data-numeric-input :min="1" :max="{{ $product->stock }}" class="product-qty-input" inputmode="numeric">
                            <button type="button" class="product-qty-btn" @click="qty = Math.min({{ $product->stock }}, qty + 1)" aria-label="افزایش تعداد">+</button>
                        </div>
                        @error('quantity')<p class="product-option-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                @if($cartQty > 0)
                    <a href="{{ route('cart.index') }}" class="btn-primary product-add-btn">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121 0 1.879-.902 1.987-1.995l1.283-12.132A1.125 1.125 0 0018.168 6H5.833"/></svg>
                        مشاهده سبد خرید
                    </a>
                @else
                    <button type="submit" class="btn-primary product-add-btn">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5V6a3.75 3.75 0 117.5 0v4.5"/></svg>
                        افزودن به سبد
                    </button>
                @endif
            </form>
        @else
            <div class="product-out-of-stock-banner">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                این محصول در حال حاضر موجود نیست
            </div>
        @endif

        <div class="product-secondary-actions">
            <form method="POST" action="{{ route('wishlist.toggle', $product) }}">
                @csrf
                <button
                    type="{{ $inWishlist ? 'button' : 'submit' }}"
                    @if($inWishlist)
                        data-delete-confirm
                        data-confirm-title="حذف از علاقه‌مندی‌ها"
                        data-confirm-message="آیا می‌خواهید این محصول را از علاقه‌مندی‌ها حذف کنید؟"
                    @endif
                    class="product-wishlist-btn {{ $inWishlist ? 'is-active' : '' }}"
                >
                    <svg class="h-5 w-5" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                    {{ $inWishlist ? 'در علاقه‌مندی‌ها' : 'علاقه‌مندی' }}
                </button>
            </form>

            <button
                type="button"
                class="product-share-btn"
                x-data="{ copied: false }"
                @click="navigator.clipboard.writeText(window.location.href); copied = true; setTimeout(() => copied = false, 2000)"
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z"/></svg>
                <span x-text="copied ? 'کپی شد!' : 'اشتراک‌گذاری'"></span>
            </button>
        </div>
    </div>

    @unless($compact)
        <div class="product-trust-grid">
            @foreach(array_slice($store['trustBadges'], 0, 3) as $badge)
                <div class="product-trust-item">
                    <span class="product-trust-icon" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $badge['icon'] }}"/></svg>
                    </span>
                    <div>
                        <p class="product-trust-title">{{ $badge['title'] }}</p>
                        <p class="product-trust-desc">{{ $badge['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endunless
</div>
