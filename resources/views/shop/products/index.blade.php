@extends('layouts.shop')

@section('title', 'محصولات')

@section('content')
@php
    $inShoppingFlow = \App\Support\ShoppingFlow::isActive();
    $activeCategory = $categories->firstWhere('slug', request('category'));
    $sort = request('sort', 'newest');

    $activeBrands = (array) ($activeBrands ?? request('brands', []));

    /*
    |--------------------------------------------------------------------------
    | Price slider values
    |--------------------------------------------------------------------------
    */
    $sliderMaxPrice = max((int) ($maxProductPrice ?? 0), 1000);

    $currentMinPrice = request()->filled('min_price')
        ? min(max((int) request('min_price'), 0), $sliderMaxPrice)
        : 0;

    $currentMaxPrice = request()->filled('max_price')
        ? min(max((int) request('max_price'), 0), $sliderMaxPrice)
        : $sliderMaxPrice;

    if ($currentMinPrice > $currentMaxPrice) {
        [$currentMinPrice, $currentMaxPrice] = [$currentMaxPrice, $currentMinPrice];
    }

    $activeFilterCount = 0;
    if (request()->filled('category')) $activeFilterCount++;
    if (!empty($activeBrands)) $activeFilterCount += count($activeBrands);
    if (request()->filled('min_price')) $activeFilterCount++;
    if (request()->filled('max_price')) $activeFilterCount++;
    if (request()->boolean('in_stock')) $activeFilterCount++;
    if (request()->boolean('discount')) $activeFilterCount++;
    if (request()->filled('min_rating')) $activeFilterCount++;

    $hasActiveFilters = request()->filled('search')
        || request()->filled('category')
        || !empty($activeBrands)
        || request()->filled('min_price')
        || request()->filled('max_price')
        || request()->boolean('in_stock')
        || request()->boolean('discount')
        || request()->filled('min_rating')
        || ($sort && $sort !== 'newest');

    $listParams = array_filter([
        'shop' => $inShoppingFlow ? 1 : null,
        'search' => request('search'),
        'category' => request('category'),
        'sort' => $sort !== 'newest' ? $sort : null,
        'min_price' => request('min_price'),
        'max_price' => request('max_price'),
        'in_stock' => request('in_stock'),
        'discount' => request('discount'),
        'min_rating' => request('min_rating'),
        'brands' => !empty($activeBrands) ? $activeBrands : null,
    ], fn ($value) => $value !== null && $value !== '' && !(is_array($value) && empty($value)));
@endphp

<div
    class="products-page"
    x-data="{
        filtersOpen: false,

        mobileCategoryOpen: true,
        mobilePriceOpen: false,
        mobileStatusOpen: false,
        mobileRatingOpen: false,
        mobileBrandsOpen: false,

        minPrice: {{ $currentMinPrice }},
        maxPrice: {{ $currentMaxPrice }},
        priceLimit: {{ $sliderMaxPrice }},

        get minPercent() {
            if (!this.priceLimit) return 0;
            return Math.min(100, Math.max(0, (this.minPrice / this.priceLimit) * 100));
        },

        get maxPercent() {
            if (!this.priceLimit) return 100;
            return Math.min(100, Math.max(0, (this.maxPrice / this.priceLimit) * 100));
        },

        syncMinPrice() {
            this.minPrice = Math.min(
                Math.max(0, Number(this.minPrice)),
                Math.max(0, Number(this.maxPrice) - 1)
            );
        },

        syncMaxPrice() {
            this.maxPrice = Math.max(
                Math.min(Number(this.maxPrice), this.priceLimit),
                Number(this.minPrice) + 1
            );
        },

        formatPrice(value) {
            return new Intl.NumberFormat('fa-IR').format(Number(value || 0));
        },

        openFilters() {
            this.filtersOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        closeFilters() {
            this.filtersOpen = false;
            document.body.classList.remove('overflow-hidden');
        }
    }"
>
    <div class="home-section-container py-6 sm:py-8">

        @unless($inShoppingFlow)
            <nav class="products-breadcrumb" aria-label="مسیر صفحه">
                <a href="{{ route('home') }}">خانه</a>
                <span class="products-breadcrumb-sep" aria-hidden="true">/</span>

                @if($activeCategory)
                    <a href="{{ route('products.index', request()->except('page', 'category', 'brands')) }}">
                        محصولات
                    </a>

                    <span class="products-breadcrumb-sep" aria-hidden="true">/</span>

                    <span class="products-breadcrumb-current">
                        {{ $activeCategory->name }}
                    </span>
                @else
                    <span class="products-breadcrumb-current">محصولات</span>
                @endif
            </nav>
        @endunless

        @if($inShoppingFlow)
            <div class="shop-flow-intro mb-6">
                <div class="shop-flow-intro-content">
                    <span class="shop-flow-step-badge">مرحله ۱ از ۴</span>

                    <h1 class="shop-flow-title">
                        محصولات مورد نظر خود را انتخاب کنید
                    </h1>

                    <p class="shop-flow-desc">
                        محصولات را به سبد اضافه کنید و در مرحله بعد تسویه حساب را انجام دهید.
                    </p>
                </div>
            </div>
        @else
            <header class="products-header">
                <span class="home-section-badge">ویترین</span>

                <h1 class="home-section-title mt-2">
                    @if($activeCategory)
                        {{ $activeCategory->name }}
                    @else
                        همه محصولات
                    @endif
                </h1>

                <p class="home-section-subtitle">
                    @if($activeCategory)
                        محصولات دسته «{{ $activeCategory->name }}» را مرور کنید و فیلترها را اعمال کنید.
                    @else
                        جستجو، دسته‌بندی و مرتب‌سازی را برای یافتن سریع‌تر محصول مورد نظر اعمال کنید.
                    @endif
                </p>
            </header>
        @endif

        <div class="products-layout">

            {{-- =========================================================
                 DESKTOP SIDEBAR
            ========================================================== --}}
            <aside class="products-sidebar" aria-label="فیلتر محصولات">

                <div class="products-sidebar-card">

                    <form
                        method="GET"
                        action="{{ route('products.index') }}"
                        id="sidebar-filter-form"
                        x-data="{
                            minPrice: {{ $currentMinPrice }},
                            maxPrice: {{ $currentMaxPrice }},
                            maxAvailablePrice: {{ $sliderMaxPrice }},

                            updateMinPrice(value) {
                                this.minPrice = Number(value);
                                if (this.minPrice > this.maxPrice) {
                                    this.maxPrice = this.minPrice;
                                }
                            },

                            updateMaxPrice(value) {
                                this.maxPrice = Number(value);
                                if (this.maxPrice < this.minPrice) {
                                    this.minPrice = this.maxPrice;
                                }
                            },

                            formatPrice(value) {
                                return new Intl.NumberFormat('fa-IR').format(Number(value || 0));
                            }
                        }"
                    >

                        @if($inShoppingFlow)
                            <input type="hidden" name="shop" value="1">
                        @endif

                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif

                        @if(request('sort') && request('sort') !== 'newest')
                            <input type="hidden" name="sort" value="{{ request('sort') }}">
                        @endif

                        {{-- ✅ حفظ دسته‌بندی فعلی هنگام اعمال فیلتر --}}
                        @if(request('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif


                        {{-- =====================================================
                             CATEGORY
                        ====================================================== --}}
                        <div class="mb-6">

                            <h2 class="flex items-center gap-2 text-sm font-black text-zinc-700 mb-3">
                                <svg
                                    class="h-4 w-4 text-zinc-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"
                                    />
                                </svg>

                                <span>دسته‌بندی</span>
                            </h2>

                            <div class="space-y-1">

                                <a
                                    href="{{ route('products.index', array_merge($listParams, ['category' => null])) }}"
                                    class="flex items-center justify-between rounded-lg px-3 py-2 text-sm transition
                                    {{ !request('category')
                                        ? 'bg-shop-primary/10 text-shop-primary font-bold'
                                        : 'text-zinc-600 hover:bg-zinc-50' }}"
                                >
                                    <span>همه محصولات</span>
                                </a>

                                @foreach($categories as $cat)
                                    <a
                                        href="{{ route('products.index', array_merge($listParams, ['category' => $cat->slug])) }}"
                                        class="flex items-center justify-between rounded-lg px-3 py-2 text-sm transition
                                        {{ request('category') === $cat->slug
                                            ? 'bg-shop-primary/10 text-shop-primary font-bold'
                                            : 'text-zinc-600 hover:bg-zinc-50' }}"
                                    >
                                        <span>{{ $cat->name }}</span>

                                        <span class="text-xs text-zinc-400 bg-zinc-100 rounded-full px-2 py-0.5">
                                            {{ format_number($cat->activeProducts()->count()) }}
                                        </span>
                                    </a>
                                @endforeach

                            </div>
                        </div>


                        {{-- =====================================================
                             PRICE RANGE
                        ====================================================== --}}
                        <div class="mb-6 pt-5 border-t border-zinc-100">

                            <h2 class="flex items-center gap-2 text-sm font-black text-zinc-700 mb-4">

                                <svg
                                    class="h-4 w-4 text-zinc-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>

                                <span>محدوده قیمت</span>
                            </h2>


                            {{-- Hidden values --}}
                            <input
                                type="hidden"
                                name="min_price"
                                x-bind:value="minPrice > 0 ? minPrice : ''"
                            >

                            <input
                                type="hidden"
                                name="max_price"
                                x-bind:value="maxPrice < maxAvailablePrice ? maxPrice : ''"
                            >


                            {{-- Price labels --}}
                            <div class="flex items-center justify-between gap-3 mb-3">

                                <div class="min-w-0">
                                    <span class="block text-[10px] text-zinc-400 mb-0.5">
                                        حداقل
                                    </span>

                                    <strong
                                        class="block text-xs font-black text-zinc-700 truncate"
                                        x-text="formatPrice(minPrice) + ' تومان'"
                                    ></strong>
                                </div>

                                <div class="h-px flex-1 bg-zinc-200"></div>

                                <div class="min-w-0 text-left">
                                    <span class="block text-[10px] text-zinc-400 mb-0.5">
                                        حداکثر
                                    </span>

                                    <strong
                                        class="block text-xs font-black text-zinc-700 truncate"
                                        x-text="formatPrice(maxPrice) + ' تومان'"
                                    ></strong>
                                </div>

                            </div>


                            {{-- Modern dual range slider --}}
                            <div class="price-range-slider">

                                <div class="price-range-track"></div>

                                <div
                                    class="price-range-progress"
                                    :style="`
                                        right: ${(minPrice / maxAvailablePrice) * 100}%;
                                        left: ${100 - ((maxPrice / maxAvailablePrice) * 100)}%;
                                    `"
                                ></div>

                                {{-- Minimum --}}
                                <input
                                    type="range"
                                    min="0"
                                    max="{{ $sliderMaxPrice }}"
                                    step="1000"
                                    x-model.number="minPrice"
                                    @input="updateMinPrice($event.target.value)"
                                    class="price-range-input price-range-min"
                                    aria-label="حداقل قیمت"
                                >

                                {{-- Maximum --}}
                                <input
                                    type="range"
                                    min="0"
                                    max="{{ $sliderMaxPrice }}"
                                    step="1000"
                                    x-model.number="maxPrice"
                                    @input="updateMaxPrice($event.target.value)"
                                    class="price-range-input price-range-max"
                                    aria-label="حداکثر قیمت"
                                >

                            </div>


                            <div class="flex items-center justify-between mt-2">

                                <span class="text-[10px] text-zinc-400">
                                    ۰ تومان
                                </span>

                                <span class="text-[10px] text-zinc-400">
                                    {{ format_number($sliderMaxPrice, false) }} تومان
                                </span>

                            </div>


                            {{-- Optional manual values --}}
                            <div class="grid grid-cols-2 gap-2 mt-4">

                                <div>
                                    <label class="block text-[10px] text-zinc-400 mb-1">
                                        از
                                    </label>

                                    <div class="relative">
                                        <input
                                            type="number"
                                            min="0"
                                            :max="maxPrice"
                                            x-model.number="minPrice"
                                            @change="updateMinPrice($event.target.value)"
                                            class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs text-zinc-800 outline-none transition focus:border-zinc-400 focus:bg-white focus:ring-2 focus:ring-zinc-100"
                                            dir="ltr"
                                        >
                                    </div>
                                </div>


                                <div>
                                    <label class="block text-[10px] text-zinc-400 mb-1">
                                        تا
                                    </label>

                                    <div class="relative">
                                        <input
                                            type="number"
                                            min="0"
                                            :max="maxAvailablePrice"
                                            x-model.number="maxPrice"
                                            @change="updateMaxPrice($event.target.value)"
                                            class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs text-zinc-800 outline-none transition focus:border-zinc-400 focus:bg-white focus:ring-2 focus:ring-zinc-100"
                                            dir="ltr"
                                        >
                                    </div>
                                </div>

                            </div>

                        </div>


                        {{-- =====================================================
                             STOCK / DISCOUNT
                        ====================================================== --}}
                        <div class="mb-6 pt-5 border-t border-zinc-100">

                            <h2 class="flex items-center gap-2 text-sm font-black text-zinc-700 mb-3">

                                <svg
                                    class="h-4 w-4 text-zinc-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>

                                <span>وضعیت کالا</span>
                            </h2>


                            {{-- In stock --}}
                            <label
                                class="filter-switch-row group"
                            >
                                <span class="text-sm text-zinc-600 group-hover:text-zinc-800 transition">
                                    فقط کالاهای موجود
                                </span>

                                <span class="filter-switch">

                                    <input
                                        type="checkbox"
                                        name="in_stock"
                                        value="1"
                                        @checked(request('in_stock'))
                                        class="peer sr-only"
                                    >

                                    <span class="filter-switch-track"></span>

                                    <span class="filter-switch-thumb"></span>

                                </span>
                            </label>


                            {{-- Discount --}}
                            <label
                                class="filter-switch-row group"
                            >
                                <span class="text-sm text-zinc-600 group-hover:text-zinc-800 transition">
                                    فقط تخفیف‌دارها
                                </span>

                                <span class="filter-switch filter-switch-discount">

                                    <input
                                        type="checkbox"
                                        name="discount"
                                        value="1"
                                        @checked(request('discount'))
                                        class="peer sr-only"
                                    >

                                    <span class="filter-switch-track"></span>

                                    <span class="filter-switch-thumb"></span>

                                </span>
                            </label>

                        </div>


                        {{-- =====================================================
                             RATING
                        ====================================================== --}}
                        <div class="mb-6 pt-5 border-t border-zinc-100">

                            <h2 class="flex items-center gap-2 text-sm font-black text-zinc-700 mb-3">

                                <svg
                                    class="h-4 w-4 text-zinc-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.563.563 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"
                                    />
                                </svg>

                                <span>حداقل امتیاز</span>
                            </h2>


                            <div class="flex flex-wrap gap-2">

                                @foreach([4, 3, 2] as $rate)

                                    <label class="rating-filter-option">

                                        <input
                                            type="radio"
                                            name="min_rating"
                                            value="{{ $rate }}"
                                            @checked(request('min_rating') == $rate)
                                            class="peer sr-only"
                                        >

                                        <span class="rating-filter-pill">

                                            <svg
                                                class="h-3.5 w-3.5 transition-transform duration-200"
                                                fill="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>

                                            {{ $rate }}+

                                        </span>

                                    </label>

                                @endforeach


                                @if(request('min_rating'))

                                    <a
                                        href="{{ route('products.index', array_diff_key($listParams, array_flip(['min_rating']))) }}"
                                        class="text-xs text-zinc-400 hover:text-zinc-600 self-center px-1 transition"
                                    >
                                        پاک کردن
                                    </a>

                                @endif

                            </div>

                        </div>


                        {{-- =====================================================
                             BRANDS
                        ====================================================== --}}
                        @if($brands->isNotEmpty())

                            <div class="mb-6 pt-5 border-t border-zinc-100">

                                <h2 class="flex items-center gap-2 text-sm font-black text-zinc-700 mb-3">

                                    <svg
                                        class="h-4 w-4 text-zinc-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6 6h.008v.008H6V6z"
                                        />
                                    </svg>

                                    <span>برندها</span>

                                </h2>


                                <div class="max-h-52 overflow-y-auto space-y-1 pr-1 custom-scrollbar">

                                    @foreach($brands as $brand)

                                        <label class="brand-filter-option">

                                            <input
                                                type="checkbox"
                                                name="brands[]"
                                                value="{{ $brand->slug }}"
                                                @checked(in_array($brand->slug, $activeBrands))
                                                class="peer sr-only"
                                            >

                                            <span class="brand-filter-box">

                                                <svg
                                                    class="brand-filter-check"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    stroke-width="3"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M4.5 12.75l6 6 9-13.5"
                                                    />
                                                </svg>

                                            </span>

                                            <span class="brand-filter-name">
                                                {{ $brand->name }}
                                            </span>

                                        </label>

                                    @endforeach

                                </div>

                            </div>

                        @endif


                        {{-- =====================================================
                             BUTTONS
                        ====================================================== --}}
                        <div class="sticky bottom-0 bg-white/95 backdrop-blur-sm pt-3 pb-1 border-t border-zinc-100 mt-2">

                            <button
                                type="submit"
                                class="btn-primary w-full justify-center !py-2.5 !text-sm filter-submit-btn"
                            >

                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m0 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"
                                    />
                                </svg>

                                اعمال فیلترها

                            </button>


                            @if($hasActiveFilters)

                                <a
                                    href="{{ route('products.index', $inShoppingFlow ? ['shop' => 1] : []) }}"
                                    class="mt-2 flex w-full items-center justify-center gap-1 rounded-lg border border-zinc-200 py-2 text-xs font-medium text-zinc-500 transition hover:border-zinc-300 hover:text-zinc-700"
                                >

                                    <svg
                                        class="h-3.5 w-3.5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>

                                    پاک کردن همه

                                </a>

                            @endif

                        </div>

                    </form>

                </div>

            </aside>


            {{-- =========================================================
                 MAIN CONTENT
            ========================================================== --}}
            <div class="products-main">

                {{-- =====================================================
                     MOBILE FILTER TRIGGER
                ====================================================== --}}
                <div class="md:hidden mb-4">

                    <button
                        type="button"
                        @click="openFilters()"
                        class="mobile-filter-trigger"
                        :aria-expanded="filtersOpen"
                    >

                        <span class="mobile-filter-trigger-icon">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m0 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/>
                            </svg>
                        </span>

                        <span class="flex-1 text-right">
                            فیلتر محصولات
                        </span>

                        @if($activeFilterCount > 0)
                            <span class="mobile-filter-count">
                                {{ format_number($activeFilterCount) }}
                            </span>
                        @endif

                        <svg
                            class="h-4 w-4 text-zinc-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 5l7 7-7 7"/>
                        </svg>

                    </button>

                </div>


                {{-- =====================================================
                     MOBILE FILTER OVERLAY (FULL SHEET)
                ====================================================== --}}
                <div
                    x-show="filtersOpen"
                    x-cloak
                    x-transition.opacity
                    class="mobile-filter-overlay md:hidden"
                    @click.self="closeFilters()"
                >

                    <div
                        x-show="filtersOpen"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="translate-y-full"
                        x-transition:enter-end="translate-y-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="translate-y-0"
                        x-transition:leave-end="translate-y-full"
                        class="mobile-filter-sheet"
                    >

                        {{-- Header --}}
                        <div class="mobile-filter-header">

                            <div>
                                <div class="flex items-center gap-2">

                                    <h2 class="text-base font-black text-zinc-900">
                                        فیلتر محصولات
                                    </h2>

                                    @if($activeFilterCount > 0)
                                        <span class="mobile-filter-header-count">
                                            {{ format_number($activeFilterCount) }} فعال
                                        </span>
                                    @endif

                                </div>

                                <p class="mt-1 text-xs text-zinc-400">
                                    گزینه‌های موردنظر را انتخاب کنید
                                </p>
                            </div>

                            <button
                                type="button"
                                @click="closeFilters()"
                                class="mobile-filter-close"
                                aria-label="بستن"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>

                        </div>


                        {{-- Mobile Filter Form --}}
                        <form
                            method="GET"
                            action="{{ route('products.index') }}"
                            class="mobile-filter-form"
                        >

                            @if($inShoppingFlow)
                                <input type="hidden" name="shop" value="1">
                            @endif

                            @if(request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif

                            @if(request('sort') && request('sort') !== 'newest')
                                <input type="hidden" name="sort" value="{{ request('sort') }}">
                            @endif

                            {{-- ✅ حفظ دسته‌بندی فعلی در موبایل --}}
                            @if(request('category'))
                                <input type="hidden" name="category" value="{{ request('category') }}">
                            @endif


                            {{-- جستجو --}}
                            <div class="mobile-filter-search">

                                <svg
                                    class="h-4 w-4 text-zinc-400 shrink-0"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                                </svg>

                                <input
                                    type="text"
                                    name="search"
                                    value="{{ request('search') }}"
                                    placeholder="جستجوی محصول..."
                                >

                            </div>


                            {{-- دسته‌بندی --}}
                            <div class="mobile-filter-section">

                                <button
                                    type="button"
                                    @click="mobileCategoryOpen = !mobileCategoryOpen"
                                    class="mobile-filter-section-head"
                                >

                                    <div class="flex items-center gap-3">

                                        <span class="mobile-filter-section-icon">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6z"/>
                                            </svg>
                                        </span>

                                        <span>
                                            دسته‌بندی
                                        </span>

                                    </div>

                                    <svg
                                        class="h-4 w-4 text-zinc-400 transition-transform"
                                        :class="mobileCategoryOpen ? 'rotate-180' : ''"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>

                                </button>


                                <div
                                    x-show="mobileCategoryOpen"
                                    x-collapse
                                    class="mobile-filter-section-content"
                                >

                                    <div class="mobile-category-list">

                                        <label class="mobile-category-option">
                                            <input
                                                type="radio"
                                                name="category"
                                                value=""
                                                @checked(!request('category'))
                                                class="peer sr-only"
                                            >

                                            <span class="mobile-category-radio">
                                                <span></span>
                                            </span>

                                            <span class="flex-1">
                                                همه محصولات
                                            </span>

                                        </label>

                                        @foreach($categories as $cat)

                                            <label class="mobile-category-option">

                                                <input
                                                    type="radio"
                                                    name="category"
                                                    value="{{ $cat->slug }}"
                                                    @checked(request('category') === $cat->slug)
                                                    class="peer sr-only"
                                                >

                                                <span class="mobile-category-radio">
                                                    <span></span>
                                                </span>

                                                <span class="flex-1">
                                                    {{ $cat->name }}
                                                </span>

                                                <span class="text-[11px] text-zinc-400">
                                                    {{ format_number($cat->activeProducts()->count()) }}
                                                </span>

                                            </label>

                                        @endforeach

                                    </div>

                                </div>

                            </div>


                            {{-- قیمت --}}
                            <div class="mobile-filter-section">

                                <button
                                    type="button"
                                    @click="mobilePriceOpen = !mobilePriceOpen"
                                    class="mobile-filter-section-head"
                                >

                                    <div class="flex items-center gap-3">

                                        <span class="mobile-filter-section-icon">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </span>

                                        <div class="text-right">
                                            <span class="block">
                                                محدوده قیمت
                                            </span>

                                            <span
                                                class="block text-[10px] text-zinc-400 font-normal mt-0.5"
                                                x-text="(minPrice > 0 || maxPrice < priceLimit)
                                                    ? formatPrice(minPrice) + ' تا ' + formatPrice(maxPrice) + ' تومان'
                                                    : 'همه قیمت‌ها'"
                                            ></span>
                                        </div>

                                    </div>

                                    <svg
                                        class="h-4 w-4 text-zinc-400 transition-transform"
                                        :class="mobilePriceOpen ? 'rotate-180' : ''"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>

                                </button>


                                <div
                                    x-show="mobilePriceOpen"
                                    x-collapse
                                    class="mobile-filter-section-content"
                                >

                                    <div class="price-slider-box">

                                        <div class="price-values">

                                            <div>
                                                <span>از</span>
                                                <strong x-text="formatPrice(minPrice)"></strong>
                                                <small>تومان</small>
                                            </div>

                                            <div class="text-left">
                                                <span>تا</span>
                                                <strong x-text="formatPrice(maxPrice)"></strong>
                                                <small>تومان</small>
                                            </div>

                                        </div>


                                        <div class="dual-range">

                                            <div class="dual-range-track"></div>

                                            <div
                                                class="dual-range-progress"
                                                :style="`right:${minPercent}%; left:${100 - maxPercent}%`"
                                            ></div>

                                            <input
                                                type="range"
                                                min="0"
                                                max="{{ $sliderMaxPrice }}"
                                                step="1000"
                                                x-model.number="minPrice"
                                                @input="syncMinPrice()"
                                                aria-label="حداقل قیمت"
                                            >

                                            <input
                                                type="range"
                                                min="0"
                                                max="{{ $sliderMaxPrice }}"
                                                step="1000"
                                                x-model.number="maxPrice"
                                                @input="syncMaxPrice()"
                                                aria-label="حداکثر قیمت"
                                            >

                                        </div>


                                        <div class="price-range-limits">

                                            <span>
                                                ۰ تومان
                                            </span>

                                            <span>
                                                {{ format_number($sliderMaxPrice, false) }} تومان
                                            </span>

                                        </div>

                                    </div>


                                    <input
                                        type="hidden"
                                        name="min_price"
                                        :value="minPrice > 0 ? minPrice : ''"
                                    >

                                    <input
                                        type="hidden"
                                        name="max_price"
                                        :value="maxPrice < priceLimit ? maxPrice : ''"
                                    >

                                </div>

                            </div>


                            {{-- وضعیت --}}
                            <div class="mobile-filter-section">

                                <button
                                    type="button"
                                    @click="mobileStatusOpen = !mobileStatusOpen"
                                    class="mobile-filter-section-head"
                                >

                                    <div class="flex items-center gap-3">

                                        <span class="mobile-filter-section-icon">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </span>

                                        <span>
                                            وضعیت کالا
                                        </span>

                                    </div>

                                    <svg
                                        class="h-4 w-4 text-zinc-400 transition-transform"
                                        :class="mobileStatusOpen ? 'rotate-180' : ''"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>

                                </button>


                                <div
                                    x-show="mobileStatusOpen"
                                    x-collapse
                                    class="mobile-filter-section-content space-y-2"
                                >

                                    {{-- موجود --}}
                                    <label class="mobile-switch-option">

                                        <div class="flex items-center gap-3">

                                            <span class="mobile-option-icon mobile-option-icon-green">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </span>

                                            <span>
                                                فقط کالاهای موجود
                                            </span>

                                        </div>

                                        <span class="mobile-switch">

                                            <input
                                                type="checkbox"
                                                name="in_stock"
                                                value="1"
                                                @checked(request('in_stock'))
                                                class="peer sr-only"
                                            >

                                            <span class="mobile-switch-track"></span>

                                        </span>

                                    </label>


                                    {{-- تخفیف --}}
                                    <label class="mobile-switch-option">

                                        <div class="flex items-center gap-3">

                                            <span class="mobile-option-icon mobile-option-icon-red">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M12 8v4l2.5 2.5"/>
                                                </svg>
                                            </span>

                                            <span>
                                                فقط تخفیف‌دارها
                                            </span>

                                        </div>

                                        <span class="mobile-switch">

                                            <input
                                                type="checkbox"
                                                name="discount"
                                                value="1"
                                                @checked(request('discount'))
                                                class="peer sr-only"
                                            >

                                            <span class="mobile-switch-track"></span>

                                        </span>

                                    </label>

                                </div>

                            </div>


                            {{-- امتیاز --}}
                            <div class="mobile-filter-section">

                                <button
                                    type="button"
                                    @click="mobileRatingOpen = !mobileRatingOpen"
                                    class="mobile-filter-section-head"
                                >

                                    <div class="flex items-center gap-3">

                                        <span class="mobile-filter-section-icon mobile-filter-section-icon-star">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                        </span>

                                        <div>
                                            <span class="block">
                                                حداقل امتیاز
                                            </span>

                                            @if(request('min_rating'))
                                                <span class="block text-[10px] text-amber-600 font-normal mt-0.5">
                                                    {{ request('min_rating') }} ستاره به بالا
                                                </span>
                                            @endif
                                        </div>

                                    </div>

                                    <svg
                                        class="h-4 w-4 text-zinc-400 transition-transform"
                                        :class="mobileRatingOpen ? 'rotate-180' : ''"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>

                                </button>


                                <div
                                    x-show="mobileRatingOpen"
                                    x-collapse
                                    class="mobile-filter-section-content"
                                >

                                    <div class="grid grid-cols-3 gap-2">

                                        @foreach([4, 3, 2] as $rate)

                                            <label class="cursor-pointer">

                                                <input
                                                    type="radio"
                                                    name="min_rating"
                                                    value="{{ $rate }}"
                                                    @checked(request('min_rating') == $rate)
                                                    class="peer sr-only"
                                                >

                                                <span class="mobile-rating-option">

                                                    <svg class="h-4 w-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                    </svg>

                                                    <span>{{ $rate }}+</span>

                                                </span>

                                            </label>

                                        @endforeach

                                    </div>

                                </div>

                            </div>


                            {{-- برندها --}}
                            @if($brands->isNotEmpty())

                                <div class="mobile-filter-section">

                                    <button
                                        type="button"
                                        @click="mobileBrandsOpen = !mobileBrandsOpen"
                                        class="mobile-filter-section-head"
                                    >

                                        <div class="flex items-center gap-3">

                                            <span class="mobile-filter-section-icon">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
                                                </svg>
                                            </span>

                                            <div>
                                                <span class="block">
                                                    برندها
                                                </span>

                                                @if(count($activeBrands))
                                                    <span class="block text-[10px] text-shop-primary font-normal mt-0.5">
                                                        {{ format_number(count($activeBrands)) }} برند انتخاب شده
                                                    </span>
                                                @endif
                                            </div>

                                        </div>

                                        <svg
                                            class="h-4 w-4 text-zinc-400 transition-transform"
                                            :class="mobileBrandsOpen ? 'rotate-180' : ''"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M19 9l-7 7-7-7"/>
                                        </svg>

                                    </button>


                                    <div
                                        x-show="mobileBrandsOpen"
                                        x-collapse
                                        class="mobile-filter-section-content"
                                    >

                                        <div class="mobile-brand-list">

                                            @foreach($brands as $brand)

                                                <label class="mobile-brand-option">

                                                    <input
                                                        type="checkbox"
                                                        name="brands[]"
                                                        value="{{ $brand->slug }}"
                                                        @checked(in_array($brand->slug, $activeBrands))
                                                        class="peer sr-only"
                                                    >

                                                    <span class="mobile-checkbox">

                                                        <svg
                                                            class="h-3.5 w-3.5 opacity-0 scale-75 transition-all duration-200"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke="currentColor"
                                                            stroke-width="3"
                                                        >
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M4.5 12.75l6 6 9-13.5"/>
                                                        </svg>

                                                    </span>

                                                    <span class="flex-1 text-sm text-zinc-700">
                                                        {{ $brand->name }}
                                                    </span>

                                                </label>

                                            @endforeach

                                        </div>

                                    </div>

                                </div>

                            @endif


                            {{-- مرتب‌سازی --}}
                            <div class="mobile-filter-section">

                                <div class="mobile-sort-title">
                                    <span>مرتب‌سازی محصولات</span>
                                </div>

                                <select
                                    name="sort"
                                    class="mobile-sort-select"
                                >
                                    <option value="newest" @selected($sort === 'newest')>
                                        جدیدترین
                                    </option>

                                    <option value="bestseller" @selected($sort === 'bestseller')>
                                        پرفروش‌ترین
                                    </option>

                                    <option value="discount" @selected($sort === 'discount')>
                                        بیشترین تخفیف
                                    </option>

                                    <option value="price_asc" @selected($sort === 'price_asc')>
                                        ارزان‌ترین
                                    </option>

                                    <option value="price_desc" @selected($sort === 'price_desc')>
                                        گران‌ترین
                                    </option>
                                </select>

                            </div>


                            {{-- دکمه‌های پایین --}}
                            <div class="mobile-filter-actions">

                                @if($hasActiveFilters)

                                    <a
                                        href="{{ route('products.index', $inShoppingFlow ? ['shop' => 1] : []) }}"
                                        class="mobile-filter-reset"
                                    >
                                        پاک کردن همه
                                    </a>

                                @endif

                                <button
                                    type="submit"
                                    class="mobile-filter-apply"
                                >
                                    <span>نمایش نتایج</span>

                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M5 12h14M12 5l7 7-7 7"/>
                                    </svg>
                                </button>

                            </div>

                        </form>

                    </div>

                </div>


                {{-- =====================================================
                     TOOLBAR
                ====================================================== --}}
                <div class="products-toolbar">

                    <div class="products-toolbar-top">

                        <p class="products-results-meta">

                            @if($products->total() > 0)

                                نمایش
                                <strong>{{ format_number($products->firstItem()) }}</strong>
                                تا
                                <strong>{{ format_number($products->lastItem()) }}</strong>
                                از
                                <strong>{{ format_number($products->total()) }}</strong>
                                محصول

                            @else

                                محصولی یافت نشد

                            @endif

                        </p>

                    </div>


                    <form method="GET" class="shop-filter-bar">

                        @if($inShoppingFlow)
                            <input type="hidden" name="shop" value="1">
                        @endif

                        @if(request('category'))
                            <input
                                type="hidden"
                                name="category"
                                value="{{ request('category') }}"
                            >
                        @endif

                        @foreach((array) request('brands', []) as $b)
                            <input
                                type="hidden"
                                name="brands[]"
                                value="{{ $b }}"
                            >
                        @endforeach

                        @if(request('min_price'))
                            <input
                                type="hidden"
                                name="min_price"
                                value="{{ request('min_price') }}"
                            >
                        @endif

                        @if(request('max_price'))
                            <input
                                type="hidden"
                                name="max_price"
                                value="{{ request('max_price') }}"
                            >
                        @endif

                        @if(request('in_stock'))
                            <input
                                type="hidden"
                                name="in_stock"
                                value="1"
                            >
                        @endif

                        @if(request('discount'))
                            <input
                                type="hidden"
                                name="discount"
                                value="1"
                            >
                        @endif

                        @if(request('min_rating'))
                            <input
                                type="hidden"
                                name="min_rating"
                                value="{{ request('min_rating') }}"
                            >
                        @endif


                        <div class="shop-filter-search">

                            <svg
                                class="shop-filter-icon"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"
                                />
                            </svg>

                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                class="shop-filter-input"
                                placeholder="جستجوی محصول..."
                            >

                        </div>


                        <select name="sort" class="shop-filter-select">

                            <option value="newest" @selected($sort === 'newest')>
                                جدیدترین
                            </option>

                            <option value="bestseller" @selected($sort === 'bestseller')>
                                پرفروش‌ترین
                            </option>

                            <option value="discount" @selected($sort === 'discount')>
                                بیشترین تخفیف
                            </option>

                            <option value="price_asc" @selected($sort === 'price_asc')>
                                ارزان‌ترین
                            </option>

                            <option value="price_desc" @selected($sort === 'price_desc')>
                                گران‌ترین
                            </option>

                        </select>


                        <button
                            type="submit"
                            class="btn-primary shop-filter-submit"
                        >
                            اعمال
                        </button>

                    </form>

                </div>


                {{-- =====================================================
                     ACTIVE FILTER CHIPS
                ====================================================== --}}
                @if($hasActiveFilters)

                    <div class="products-active-filters">

                        @if(request('search'))

                            <span class="products-filter-chip">

                                جستجو: {{ request('search') }}

                                <a
                                    href="{{ route('products.index', array_diff_key($listParams, array_flip(['search']))) }}"
                                    class="products-filter-chip-remove"
                                    aria-label="حذف جستجو"
                                >
                                    <svg
                                        class="h-3.5 w-3.5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </a>

                            </span>

                        @endif


                        @if($activeCategory)

                            <span class="products-filter-chip">

                                {{ $activeCategory->name }}

                                <a
                                    href="{{ route('products.index', array_diff_key($listParams, array_flip(['category']))) }}"
                                    class="products-filter-chip-remove"
                                    aria-label="حذف دسته"
                                >
                                    <svg
                                        class="h-3.5 w-3.5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </a>

                            </span>

                        @endif


                        @foreach($activeBrands as $ab)

                            @php
                                $bn = $brands->firstWhere('slug', $ab)?->name ?? $ab;
                            @endphp

                            <span class="products-filter-chip">

                                برند: {{ $bn }}

                                <a
                                    href="{{ route('products.index', array_diff_key($listParams, array_flip([$ab]))) }}"
                                    class="products-filter-chip-remove"
                                    aria-label="حذف برند"
                                >
                                    <svg
                                        class="h-3.5 w-3.5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </a>

                            </span>

                        @endforeach


                        @if(request('min_price') || request('max_price'))

                            <span class="products-filter-chip">

                                قیمت:
                                {{ request('min_price') ? format_price((int) request('min_price')) : '۰' }}
                                تا
                                {{ request('max_price') ? format_price((int) request('max_price')) : '∞' }}

                                <a
                                    href="{{ route('products.index', array_diff_key($listParams, array_flip(['min_price', 'max_price']))) }}"
                                    class="products-filter-chip-remove"
                                    aria-label="حذف قیمت"
                                >
                                    <svg
                                        class="h-3.5 w-3.5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </a>

                            </span>

                        @endif


                        @if(request('in_stock'))

                            <span class="products-filter-chip">

                                فقط موجود

                                <a
                                    href="{{ route('products.index', array_diff_key($listParams, array_flip(['in_stock']))) }}"
                                    class="products-filter-chip-remove"
                                    aria-label="حذف موجود"
                                >
                                    <svg
                                        class="h-3.5 w-3.5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </a>

                            </span>

                        @endif


                        @if(request('discount'))

                            <span class="products-filter-chip">

                                تخفیف‌دار

                                <a
                                    href="{{ route('products.index', array_diff_key($listParams, array_flip(['discount']))) }}"
                                    class="products-filter-chip-remove"
                                    aria-label="حذف تخفیف"
                                >
                                    <svg
                                        class="h-3.5 w-3.5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </a>

                            </span>

                        @endif


                        @if(request('min_rating'))

                            <span class="products-filter-chip">

                                {{ request('min_rating') }}+ ستاره

                                <a
                                    href="{{ route('products.index', array_diff_key($listParams, array_flip(['min_rating']))) }}"
                                    class="products-filter-chip-remove"
                                    aria-label="حذف امتیاز"
                                >
                                    <svg
                                        class="h-3.5 w-3.5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </a>

                            </span>

                        @endif


                        @if($sort && $sort !== 'newest')

                            @php
                                $sortLabels = [
                                    'bestseller' => 'پرفروش‌ترین',
                                    'discount' => 'بیشترین تخفیف',
                                    'price_asc' => 'ارزان‌ترین',
                                    'price_desc' => 'گران‌ترین',
                                ];
                            @endphp

                            <span class="products-filter-chip">

                                {{ $sortLabels[$sort] ?? $sort }}

                                <a
                                    href="{{ route('products.index', array_diff_key($listParams, array_flip(['sort']))) }}"
                                    class="products-filter-chip-remove"
                                    aria-label="حذف مرتب‌سازی"
                                >
                                    <svg
                                        class="h-3.5 w-3.5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2.5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </a>

                            </span>

                        @endif


                        <a
                            href="{{ route('products.index', $inShoppingFlow ? ['shop' => 1] : []) }}"
                            class="products-clear-filters"
                        >
                            پاک کردن همه
                        </a>

                    </div>

                @endif


                {{-- =====================================================
                     PRODUCTS GRID
                ====================================================== --}}
                @if($products->count())

                    <div class="home-products-grid">

                        @foreach($products as $product)

                            <x-product-card
                                :product="$product"
                                :list-return-url="request()->fullUrl()"
                            />

                        @endforeach

                    </div>


                    @if($products->hasPages())

                        <div class="mt-10">
                            {{ $products->links() }}
                        </div>

                    @endif

                @else

                    <div class="products-empty">

                        <div class="products-empty-icon">

                            <svg
                                class="h-8 w-8"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"
                                />
                            </svg>

                        </div>

                        <h2 class="products-empty-title">
                            محصولی با این فیلترها پیدا نشد
                        </h2>

                        <p class="products-empty-desc">
                            فیلترها را تغییر دهید یا به لیست کامل محصولات برگردید.
                        </p>

                        <div class="products-empty-actions">

                            <a
                                href="{{ route('products.index', $inShoppingFlow ? ['shop' => 1] : []) }}"
                                class="btn-primary !rounded-xl !px-6 !py-3"
                            >
                                نمایش همه محصولات
                            </a>

                            <a
                                href="{{ route('home') }}"
                                class="link-shop"
                            >
                                بازگشت به خانه
                            </a>

                        </div>

                    </div>

                @endif

            </div>

        </div>

    </div>
</div>


<style>
    /* ============================================================
       CUSTOM SCROLLBAR
    ============================================================ */

    .custom-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: #d4d4d8 transparent;
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #d4d4d8;
        border-radius: 999px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #a1a1aa;
    }


    /* ============================================================
       PRICE RANGE SLIDER (Desktop)
    ============================================================ */

    .price-range-slider {
        position: relative;
        height: 34px;
        direction: ltr;
        margin: 0 4px;
    }

    .price-range-track,
    .price-range-progress {
        position: absolute;
        top: 50%;
        height: 5px;
        transform: translateY(-50%);
        border-radius: 999px;
        pointer-events: none;
    }

    .price-range-track {
        left: 0;
        right: 0;
        background: #e4e4e7;
    }

    .price-range-progress {
        background: currentColor;
        color: var(--shop-primary, #18181b);
        min-width: 5px;
    }

    .price-range-input {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 34px;
        margin: 0;
        appearance: none;
        -webkit-appearance: none;
        background: transparent;
        pointer-events: none;
        outline: none;
    }

    .price-range-input::-webkit-slider-runnable-track {
        height: 5px;
        background: transparent;
        border: none;
    }

    .price-range-input::-moz-range-track {
        height: 5px;
        background: transparent;
        border: none;
    }

    .price-range-input::-webkit-slider-thumb {
        appearance: none;
        -webkit-appearance: none;
        width: 18px;
        height: 18px;
        margin-top: -6.5px;
        border-radius: 999px;
        border: 3px solid #fff;
        background: currentColor;
        color: var(--shop-primary, #18181b);
        box-shadow:
            0 1px 3px rgba(0, 0, 0, 0.16),
            0 0 0 1px rgba(0, 0, 0, 0.08);
        pointer-events: auto;
        cursor: grab;
        transition:
            transform 0.18s ease,
            box-shadow 0.18s ease;
    }

    .price-range-input::-webkit-slider-thumb:hover {
        transform: scale(1.12);
        box-shadow:
            0 2px 5px rgba(0, 0, 0, 0.18),
            0 0 0 4px rgba(0, 0, 0, 0.06);
    }

    .price-range-input::-webkit-slider-thumb:active {
        cursor: grabbing;
        transform: scale(1.18);
    }

    .price-range-input::-moz-range-thumb {
        width: 18px;
        height: 18px;
        border-radius: 999px;
        border: 3px solid #fff;
        background: currentColor;
        color: var(--shop-primary, #18181b);
        box-shadow:
            0 1px 3px rgba(0, 0, 0, 0.16),
            0 0 0 1px rgba(0, 0, 0, 0.08);
        pointer-events: auto;
        cursor: grab;
        transition:
            transform 0.18s ease,
            box-shadow 0.18s ease;
    }

    .price-range-input::-moz-range-thumb:hover {
        transform: scale(1.12);
    }

    .price-range-min {
        z-index: 3;
    }

    .price-range-max {
        z-index: 4;
    }


    /* ============================================================
       FILTER SWITCH
    ============================================================ */

    .filter-switch-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-height: 42px;
        padding: 5px 0;
        cursor: pointer;
    }

    .filter-switch {
        position: relative;
        display: inline-flex;
        flex: 0 0 auto;
        width: 42px;
        height: 23px;
        direction: ltr;
    }

    .filter-switch-track {
        position: absolute;
        inset: 0;
        border-radius: 999px;
        background: #e4e4e7;
        transition:
            background-color 0.22s ease,
            box-shadow 0.22s ease;
    }

    .filter-switch-thumb {
        position: absolute;
        top: 3px;
        left: 3px;
        width: 17px;
        height: 17px;
        border-radius: 999px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.18);
        transition:
            transform 0.22s cubic-bezier(.4, 0, .2, 1),
            box-shadow 0.22s ease;
        pointer-events: none;
    }

    .filter-switch input:checked ~ .filter-switch-track {
        background: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.10);
    }

    .filter-switch input:checked ~ .filter-switch-thumb {
        transform: translateX(19px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.18);
    }

    .filter-switch-discount input:checked ~ .filter-switch-track {
        background: #f43f5e;
        box-shadow: 0 0 0 3px rgba(244, 63, 94, 0.10);
    }


    /* ============================================================
       BRAND CHECKBOX
    ============================================================ */

    .brand-filter-option {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 38px;
        padding: 6px 8px;
        border-radius: 10px;
        cursor: pointer;
        transition:
            background-color 0.18s ease,
            transform 0.18s ease;
    }

    .brand-filter-option:hover {
        background: #fafafa;
    }

    .brand-filter-box {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 19px;
        height: 19px;
        flex: 0 0 19px;
        border: 1.5px solid #d4d4d8;
        border-radius: 6px;
        background: #fff;
        transition:
            background-color 0.18s ease,
            border-color 0.18s ease,
            box-shadow 0.18s ease,
            transform 0.18s ease;
    }

    .brand-filter-check {
        width: 12px;
        height: 12px;
        color: #fff;
        opacity: 0;
        transform: scale(0.4);
        transition:
            opacity 0.18s ease,
            transform 0.18s cubic-bezier(.34, 1.56, .64, 1);
    }

    .brand-filter-option input:checked ~ .brand-filter-box {
        background: var(--shop-primary, #18181b);
        border-color: var(--shop-primary, #18181b);
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
        transform: scale(1.02);
    }

    .brand-filter-option input:checked ~ .brand-filter-box .brand-filter-check {
        opacity: 1;
        transform: scale(1);
    }

    .brand-filter-name {
        font-size: 0.875rem;
        color: #52525b;
        transition:
            color 0.18s ease,
            font-weight 0.18s ease;
    }

    .brand-filter-option:hover .brand-filter-name {
        color: #27272a;
    }

    .brand-filter-option input:checked ~ .brand-filter-name {
        color: #18181b;
        font-weight: 700;
    }


    /* ============================================================
       RATING FILTER
    ============================================================ */

    .rating-filter-option {
        cursor: pointer;
    }

    .rating-filter-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        min-height: 34px;
        padding: 6px 10px;
        border: 1px solid #e4e4e7;
        border-radius: 10px;
        background: #fff;
        color: #52525b;
        font-size: 0.875rem;
        transition:
            background-color 0.18s ease,
            border-color 0.18s ease,
            color 0.18s ease,
            box-shadow 0.18s ease,
            transform 0.18s ease;
    }

    .rating-filter-option:hover .rating-filter-pill {
        background: #fafafa;
        border-color: #d4d4d8;
    }

    .rating-filter-option input:checked ~ .rating-filter-pill {
        border-color: #fbbf24;
        background: #fffbeb;
        color: #b45309;
        font-weight: 700;
        box-shadow: 0 3px 10px rgba(245, 158, 11, 0.10);
    }

    .rating-filter-option input:checked ~ .rating-filter-pill svg {
        transform: rotate(-8deg) scale(1.12);
    }


    /* ============================================================
       SUBMIT BUTTON
    ============================================================ */

    .filter-submit-btn {
        transition:
            transform 0.18s ease,
            box-shadow 0.18s ease;
    }

    .filter-submit-btn:hover {
        transform: translateY(-1px);
    }

    .filter-submit-btn:active {
        transform: translateY(0);
    }


    /* ============================================================
       MOBILE FILTER OVERLAY & SHEET
    ============================================================ */

    .mobile-filter-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        background: rgba(9, 9, 11, .48);
        backdrop-filter: blur(3px);
        -webkit-backdrop-filter: blur(3px);
    }

    .mobile-filter-sheet {
        width: 100%;
        max-height: 92dvh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: #fafafa;
        border-radius: 22px 22px 0 0;
        box-shadow: 0 -15px 50px rgba(0, 0, 0, .18);
    }

    .mobile-filter-header {
        position: relative;
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 17px 17px 14px;
        background: #fff;
        border-bottom: 1px solid #f0f0f2;
    }

    .mobile-filter-header::before {
        content: "";
        position: absolute;
        top: 7px;
        left: 50%;
        width: 38px;
        height: 4px;
        transform: translateX(-50%);
        border-radius: 999px;
        background: #d4d4d8;
    }

    .mobile-filter-header-count {
        display: inline-flex;
        align-items: center;
        padding: 3px 7px;
        border-radius: 999px;
        background: #f4f4f5;
        color: #71717a;
        font-size: 9px;
        font-weight: 800;
    }

    .mobile-filter-close {
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background: #f4f4f5;
        color: #52525b;
        transition: all .2s ease;
    }

    .mobile-filter-close:active {
        transform: scale(.94);
        background: #e4e4e7;
    }


    .mobile-filter-form {
        min-height: 0;
        flex: 1 1 auto;
        overflow-y: auto;
        overscroll-behavior: contain;
        padding: 12px 14px 96px;
        scrollbar-width: thin;
    }

    .mobile-filter-search {
        height: 48px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0 13px;
        margin-bottom: 10px;
        background: #fff;
        border: 1px solid #e4e4e7;
        border-radius: 13px;
    }

    .mobile-filter-search input {
        width: 100%;
        min-width: 0;
        border: 0;
        outline: 0;
        background: transparent;
        color: #27272a;
        font-size: 13px;
    }

    .mobile-filter-search input::placeholder {
        color: #a1a1aa;
    }


    .mobile-filter-section {
        overflow: hidden;
        margin-bottom: 8px;
        background: #fff;
        border: 1px solid #ededf0;
        border-radius: 14px;
    }

    .mobile-filter-section-head {
        width: 100%;
        min-height: 58px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 0 13px;
        color: #27272a;
        text-align: right;
        font-size: 13px;
        font-weight: 800;
        background: transparent;
        cursor: pointer;
    }

    .mobile-filter-section-icon {
        width: 31px;
        height: 31px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: #f4f4f5;
        color: #71717a;
    }

    .mobile-filter-section-icon-star {
        color: #d97706;
        background: #fffbeb;
    }

    .mobile-filter-section-content {
        padding: 0 13px 13px;
        border-top: 1px solid #f4f4f5;
    }


    .mobile-category-list {
        padding-top: 7px;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .mobile-category-option {
        min-height: 42px;
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 0 8px;
        border-radius: 9px;
        color: #52525b;
        font-size: 12px;
        cursor: pointer;
        transition: background .2s ease;
    }

    .mobile-category-option:active {
        background: #f4f4f5;
    }

    .mobile-category-radio {
        width: 17px;
        height: 17px;
        flex: 0 0 17px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1.5px solid #d4d4d8;
        border-radius: 50%;
        transition: all .2s ease;
    }

    .mobile-category-radio span {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: transparent;
        transition: all .2s ease;
    }

    .peer:checked + .mobile-category-radio {
        border-color: #18181b;
    }

    .peer:checked + .mobile-category-radio span {
        background: #18181b;
    }


    /* Price slider in mobile */
    .price-slider-box {
        padding-top: 13px;
    }

    .price-values {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 21px;
    }

    .price-values > div {
        min-width: 0;
        display: flex;
        align-items: baseline;
        gap: 4px;
        color: #a1a1aa;
        font-size: 9px;
    }

    .price-values strong {
        color: #27272a;
        font-size: 12px;
        font-weight: 900;
        direction: ltr;
    }

    .price-values small {
        color: #a1a1aa;
        font-size: 8px;
    }

    .dual-range {
        position: relative;
        height: 26px;
        direction: ltr;
    }

    .dual-range-track,
    .dual-range-progress {
        position: absolute;
        top: 50%;
        height: 4px;
        transform: translateY(-50%);
        border-radius: 999px;
    }

    .dual-range-track {
        left: 0;
        right: 0;
        background: #e4e4e7;
    }

    .dual-range-progress {
        background: #18181b;
        z-index: 1;
    }

    .dual-range input {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 26px;
        margin: 0;
        padding: 0;
        appearance: none;
        -webkit-appearance: none;
        background: transparent;
        pointer-events: none;
        outline: none;
        z-index: 2;
    }

    .dual-range input::-webkit-slider-thumb {
        appearance: none;
        -webkit-appearance: none;
        width: 18px;
        height: 18px;
        border: 3px solid #fff;
        border-radius: 50%;
        background: #18181b;
        box-shadow: 0 2px 7px rgba(0,0,0,.2);
        cursor: grab;
        pointer-events: auto;
    }

    .dual-range input::-moz-range-thumb {
        width: 18px;
        height: 18px;
        border: 3px solid #fff;
        border-radius: 50%;
        background: #18181b;
        box-shadow: 0 2px 7px rgba(0,0,0,.2);
        cursor: grab;
        pointer-events: auto;
    }

    .dual-range input::-webkit-slider-runnable-track {
        background: transparent;
    }

    .dual-range input::-moz-range-track {
        background: transparent;
    }

    .price-range-limits {
        display: flex;
        justify-content: space-between;
        direction: ltr;
        margin-top: 2px;
        color: #a1a1aa;
        font-size: 8px;
    }


    .mobile-switch-option {
        min-height: 55px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 0 4px;
        color: #3f3f46;
        font-size: 12px;
        cursor: pointer;
    }

    .mobile-option-icon {
        width: 31px;
        height: 31px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
    }

    .mobile-option-icon-green {
        color: #059669;
        background: #ecfdf5;
    }

    .mobile-option-icon-red {
        color: #e11d48;
        background: #fff1f2;
    }

    .mobile-switch {
        position: relative;
        width: 42px;
        height: 24px;
        flex: 0 0 42px;
    }

    .mobile-switch-track {
        position: absolute;
        inset: 0;
        border-radius: 999px;
        background: #e4e4e7;
        transition: background .2s ease;
    }

    .mobile-switch-track::after {
        content: "";
        position: absolute;
        top: 3px;
        left: 3px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,.15);
        transition: transform .2s ease;
    }

    .mobile-switch input:checked + .mobile-switch-track {
        background: #18181b;
    }

    .mobile-switch input:checked + .mobile-switch-track::after {
        transform: translateX(18px);
    }


    .mobile-rating-option {
        min-height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        border: 1px solid #e4e4e7;
        border-radius: 10px;
        background: #fff;
        color: #52525b;
        font-size: 11px;
        font-weight: 700;
        transition: all .2s ease;
    }

    .peer:checked + .mobile-rating-option {
        border-color: #fbbf24;
        background: #fffbeb;
        color: #b45309;
        box-shadow: 0 2px 8px rgba(245,158,11,.1);
    }


    .mobile-brand-list {
        max-height: 220px;
        overflow-y: auto;
        padding-top: 6px;
        scrollbar-width: thin;
    }

    .mobile-brand-option {
        min-height: 44px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0 6px;
        border-bottom: 1px solid #f4f4f5;
        cursor: pointer;
    }

    .mobile-brand-option:last-child {
        border-bottom: 0;
    }

    .mobile-checkbox {
        width: 19px;
        height: 19px;
        flex: 0 0 19px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1.5px solid #d4d4d8;
        border-radius: 6px;
        background: #fff;
        color: #fff;
        transition: all .2s ease;
    }

    .peer:checked + .mobile-checkbox {
        border-color: #18181b;
        background: #18181b;
    }

    .peer:checked + .mobile-checkbox svg {
        opacity: 1;
        transform: scale(1);
    }


    .mobile-sort-title {
        padding: 13px 0 8px;
        color: #52525b;
        font-size: 11px;
        font-weight: 800;
    }

    .mobile-sort-select {
        width: 100%;
        height: 45px;
        padding: 0 11px;
        border: 1px solid #e4e4e7;
        border-radius: 10px;
        background: #fafafa;
        color: #3f3f46;
        outline: none;
        font-size: 12px;
    }


    .mobile-filter-actions {
        position: fixed;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 10;
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 10px 14px calc(10px + env(safe-area-inset-bottom));
        background: rgba(255,255,255,.96);
        border-top: 1px solid #e4e4e7;
        box-shadow: 0 -8px 25px rgba(0,0,0,.06);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .mobile-filter-apply {
        flex: 1;
        height: 47px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 12px;
        background: #18181b;
        color: #fff;
        font-size: 12px;
        font-weight: 900;
        transition: transform .2s ease, opacity .2s ease;
    }

    .mobile-filter-apply:active {
        transform: scale(.98);
        opacity: .92;
    }

    .mobile-filter-reset {
        height: 47px;
        padding: 0 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e4e4e7;
        border-radius: 12px;
        background: #fff;
        color: #71717a;
        white-space: nowrap;
        font-size: 11px;
        font-weight: 700;
    }


    .mobile-filter-trigger {
        width: 100%;
        min-height: 54px;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0 14px;
        border: 1px solid #e4e4e7;
        border-radius: 15px;
        background: #fff;
        color: #27272a;
        font-size: 14px;
        font-weight: 800;
        box-shadow: 0 4px 16px rgba(24, 24, 27, 0.04);
        transition: all .2s ease;
    }

    .mobile-filter-trigger:active {
        transform: scale(.985);
    }

    .mobile-filter-trigger-icon {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: rgba(0, 0, 0, .045);
        color: #52525b;
    }

    .mobile-filter-count {
        min-width: 23px;
        height: 23px;
        padding: 0 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #18181b;
        color: white;
        font-size: 11px;
        font-weight: 900;
    }


    @media (min-width: 768px) {
        .mobile-filter-overlay,
        .mobile-filter-trigger {
            display: none !important;
        }
    }

    @media (max-width: 380px) {

        .mobile-filter-sheet {
            max-height: 94dvh;
        }

        .mobile-filter-form {
            padding-left: 10px;
            padding-right: 10px;
        }

        .mobile-filter-section-head {
            min-height: 54px;
        }

        .mobile-filter-section-icon {
            width: 29px;
            height: 29px;
        }

        .mobile-filter-actions {
            padding-left: 10px;
            padding-right: 10px;
        }

    }

    [x-cloak] {
        display: none !important;
    }
</style>
@endsection