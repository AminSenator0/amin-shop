@props([
    'step' => 'cart',
    'cartCount' => 0,
])

@php
    $steps = [
        'browse' => ['label' => 'انتخاب محصول', 'route' => 'products.index'],
        'cart' => ['label' => 'سبد خرید', 'route' => 'cart.index'],
        'checkout' => ['label' => 'اطلاعات ارسال', 'route' => 'checkout.index'],
        'payment' => ['label' => 'پرداخت', 'route' => null],
    ];

    $showBrowseStep = $step === 'browse' || \App\Support\ShoppingFlow::isActive();
    $visibleSteps = $showBrowseStep
        ? ['browse', 'cart', 'checkout', 'payment']
        : ['cart', 'checkout', 'payment'];

    $stepOrder = array_keys($steps);
    $currentIndex = array_search($step, $stepOrder, true);
    $canCheckout = $cartCount > 0 && auth()->check();
@endphp

<header class="checkout-header sticky top-0 z-50 border-b border-shop-border/60 bg-shop-surface/95 backdrop-blur-md">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-14 items-center justify-between gap-3 sm:h-16">
            <div class="flex min-w-0 items-center gap-2">
                @auth
                    <a href="{{ route('user.dashboard') }}" class="checkout-header-back shrink-0 !px-2 sm:!px-3" title="بازگشت به حساب">
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                        <span class="hidden sm:inline">حساب من</span>
                    </a>
                @else
                    <a href="{{ route('home') }}" class="flex min-w-0 shrink-0 items-center gap-2">
                        <x-shop.store-logo size="sm" />
                        <span class="hidden truncate text-base font-black text-shop-text sm:inline sm:text-lg">{{ $store['name'] }}</span>
                    </a>
                @endauth
            </div>

            <nav class="checkout-steps hidden flex-1 justify-center lg:flex" aria-label="مراحل خرید">
                @foreach($visibleSteps as $key)
                    @php
                        $meta = $steps[$key];
                        $index = array_search($key, $stepOrder, true);
                        $isActive = $key === $step;
                        $isCompleted = $index < $currentIndex;
                        $isClickable = $meta['route'] && ($isCompleted || ($key === 'cart' && $step !== 'cart') || ($key === 'browse' && $step !== 'browse'));
                        if ($key === 'checkout' && ! $canCheckout) {
                            $isClickable = false;
                        }
                    @endphp

                    <div class="checkout-step {{ $isActive ? 'is-active' : '' }} {{ $isCompleted ? 'is-completed' : '' }}">
                        @if($isClickable)
                            <a href="{{ route($meta['route'], $key === 'browse' ? ['shop' => 1] : []) }}" class="checkout-step-link">
                                <span class="checkout-step-badge">
                                    @if($isCompleted)
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </span>
                                <span class="checkout-step-label">{{ $meta['label'] }}</span>
                            </a>
                        @else
                            <span class="checkout-step-link" @if($isActive) aria-current="step" @endif>
                                <span class="checkout-step-badge">
                                    @if($isCompleted)
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </span>
                                <span class="checkout-step-label">{{ $meta['label'] }}</span>
                            </span>
                        @endif
                    </div>

                    @if(! $loop->last)
                        <span class="checkout-step-divider" aria-hidden="true"></span>
                    @endif
                @endforeach
            </nav>

            <div class="flex shrink-0 items-center gap-2">
                @if($step === 'browse' && $cartCount > 0)
                    <a href="{{ route('cart.index') }}" class="btn-primary !rounded-xl !px-3 !py-2 !text-sm sm:!px-4">
                        سبد خرید
                        <span class="mr-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-white/20 px-1 text-xs">{{ $cartCount }}</span>
                    </a>
                @elseif($step === 'cart' && $cartCount > 0)
                    @auth
                        <a href="{{ route('checkout.index') }}" class="btn-primary !rounded-xl !px-3 !py-2 !text-sm sm:!px-4">پرداخت</a>
                    @else
                        <a href="{{ route('checkout.index') }}" class="btn-primary !rounded-xl !px-3 !py-2 !text-sm sm:!px-4">ورود و پرداخت</a>
                    @endauth
                @elseif($step === 'checkout')
                    <a href="{{ route('cart.index') }}" class="checkout-header-back hidden sm:inline-flex">بازگشت</a>
                @elseif($step === 'payment')
                    <a href="{{ route('checkout.index') }}" class="checkout-header-back hidden sm:inline-flex">بازگشت</a>
                @endif
            </div>
        </div>

        <div class="checkout-steps-mobile flex items-center justify-between gap-1 border-t border-shop-border/40 pb-3 pt-2.5 lg:hidden">
            @foreach($visibleSteps as $key)
                @php
                    $meta = $steps[$key];
                    $isActive = $key === $step;
                    $isCompleted = array_search($key, $stepOrder, true) < $currentIndex;
                @endphp
                <div class="checkout-step-mobile {{ $isActive ? 'is-active' : '' }} {{ $isCompleted ? 'is-completed' : '' }}">
                    <span class="checkout-step-badge">
                        @if($isCompleted)
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        @else
                            {{ $loop->iteration }}
                        @endif
                    </span>
                    <span class="checkout-step-label">{{ $meta['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</header>
