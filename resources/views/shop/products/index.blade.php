@extends('layouts.shop')

@section('title', 'محصولات')

@section('content')
@php
    $inShoppingFlow = \App\Support\ShoppingFlow::isActive();
    $activeCategory = $categories->firstWhere('slug', request('category'));
    $activeBrand = $brands->firstWhere('slug', request('brand'));
    $sort = request('sort', 'newest');
    $hasActiveFilters = request()->filled('search')
        || request()->filled('category')
        || request()->filled('brand')
        || ($sort && $sort !== 'newest');
    $listParams = array_filter([
        'shop' => $inShoppingFlow ? 1 : null,
        'search' => request('search'),
        'category' => request('category'),
        'brand' => request('brand'),
        'sort' => $sort !== 'newest' ? $sort : null,
    ], fn ($value) => $value !== null && $value !== '');
@endphp

<div class="products-page">
    <div class="home-section-container py-6 sm:py-8">
        @unless($inShoppingFlow)
            <nav class="products-breadcrumb" aria-label="مسیر صفحه">
                <a href="{{ route('home') }}">خانه</a>
                <span class="products-breadcrumb-sep" aria-hidden="true">/</span>
                @if($activeCategory)
                    <a href="{{ route('products.index', request()->except('page', 'category')) }}">محصولات</a>
                    <span class="products-breadcrumb-sep" aria-hidden="true">/</span>
                    <span class="products-breadcrumb-current">{{ $activeCategory->name }}</span>
                @else
                    <span class="products-breadcrumb-current">محصولات</span>
                @endif
            </nav>
        @endunless

        @if($inShoppingFlow)
            <div class="shop-flow-intro mb-6">
                <div class="shop-flow-intro-content">
                    <span class="shop-flow-step-badge">مرحله ۱ از ۴</span>
                    <h1 class="shop-flow-title">محصولات مورد نظر خود را انتخاب کنید</h1>
                    <p class="shop-flow-desc">محصولات را به سبد اضافه کنید و در مرحله بعد تسویه حساب را انجام دهید.</p>
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

        <div class="products-layout" x-data="{ filtersOpen: false }">
            <aside class="products-sidebar" aria-label="فیلتر دسته‌بندی">
                <div class="products-sidebar-card">
                    <h2 class="products-sidebar-title">دسته‌بندی‌ها</h2>
                    <ul class="products-sidebar-list">
                        <li>
                            <a
                                href="{{ route('products.index', array_merge($listParams, ['category' => null])) }}"
                                class="products-sidebar-link {{ ! request('category') ? 'is-active' : '' }}"
                            >
                                همه محصولات
                            </a>
                        </li>
                        @foreach($categories as $cat)
                            <li>
                                <a
                                    href="{{ route('products.index', array_merge($listParams, ['category' => $cat->slug])) }}"
                                    class="products-sidebar-link {{ request('category') === $cat->slug ? 'is-active' : '' }}"
                                >
                                    <span>{{ $cat->name }}</span>
                                    <span class="products-sidebar-count">{{ format_number($cat->activeProducts()->count()) }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    @if($brands->isNotEmpty())
                        <h2 id="brands" class="products-sidebar-title mt-6">برندها</h2>
                        <ul class="products-sidebar-list">
                            <li>
                                <a
                                    href="{{ route('products.index', array_merge($listParams, ['brand' => null])) }}"
                                    class="products-sidebar-link {{ ! request('brand') ? 'is-active' : '' }}"
                                >
                                    همه برندها
                                </a>
                            </li>
                            @foreach($brands as $brand)
                                <li>
                                    <a
                                        href="{{ route('products.index', array_merge($listParams, ['brand' => $brand->slug])) }}"
                                        class="products-sidebar-link {{ request('brand') === $brand->slug ? 'is-active' : '' }}"
                                    >
                                        {{ $brand->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </aside>

            <div class="products-main">
                <button
                    type="button"
                    class="products-mobile-filters-btn mb-4"
                    @click="filtersOpen = !filtersOpen"
                    :aria-expanded="filtersOpen"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m0 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                    فیلترها و جستجو
                </button>

                <div
                    class="products-mobile-filters mb-4"
                    x-show="filtersOpen"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                >
                    <form method="GET" class="shop-filter-bar">
                        @if($inShoppingFlow)
                            <input type="hidden" name="shop" value="1">
                        @endif
                        <div class="shop-filter-search w-full">
                            <svg class="shop-filter-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                            <input type="text" name="search" value="{{ request('search') }}" class="shop-filter-input" placeholder="جستجوی محصول...">
                        </div>
                        <select name="category" class="shop-filter-select w-full">
                            <option value="">همه دسته‌بندی‌ها</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->slug }}" @selected(request('category') === $cat->slug)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @if($brands->isNotEmpty())
                            <select name="brand" class="shop-filter-select w-full">
                                <option value="">همه برندها</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->slug }}" @selected(request('brand') === $brand->slug)>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        @endif
                        <select name="sort" class="shop-filter-select w-full">
                            <option value="newest" @selected($sort === 'newest')>جدیدترین</option>
                            <option value="bestseller" @selected($sort === 'bestseller')>پرفروش‌ترین</option>
                            <option value="discount" @selected($sort === 'discount')>بیشترین تخفیف</option>
                            <option value="price_asc" @selected($sort === 'price_asc')>ارزان‌ترین</option>
                            <option value="price_desc" @selected($sort === 'price_desc')>گران‌ترین</option>
                        </select>
                        <button type="submit" class="btn-primary shop-filter-submit w-full">اعمال فیلترها</button>
                    </form>
                </div>

                <div class="products-toolbar">
                    <div class="products-toolbar-top">
                        <p class="products-results-meta">
                            @if($products->total() > 0)
                                نمایش <strong>{{ format_number($products->firstItem()) }}</strong>
                                تا <strong>{{ format_number($products->lastItem()) }}</strong>
                                از <strong>{{ format_number($products->total()) }}</strong> محصول
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
                            <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif
                        @if(request('brand'))
                            <input type="hidden" name="brand" value="{{ request('brand') }}">
                        @endif
                        <div class="shop-filter-search">
                            <svg class="shop-filter-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                            <input type="text" name="search" value="{{ request('search') }}" class="shop-filter-input" placeholder="جستجوی محصول...">
                        </div>
                        <select name="sort" class="shop-filter-select">
                            <option value="newest" @selected($sort === 'newest')>جدیدترین</option>
                            <option value="bestseller" @selected($sort === 'bestseller')>پرفروش‌ترین</option>
                            <option value="discount" @selected($sort === 'discount')>بیشترین تخفیف</option>
                            <option value="price_asc" @selected($sort === 'price_asc')>ارزان‌ترین</option>
                            <option value="price_desc" @selected($sort === 'price_desc')>گران‌ترین</option>
                        </select>
                        <button type="submit" class="btn-primary shop-filter-submit">اعمال</button>
                    </form>
                </div>

                @if($hasActiveFilters)
                    <div class="products-active-filters">
                        @if(request('search'))
                            <span class="products-filter-chip">
                                جستجو: {{ request('search') }}
                                <a href="{{ route('products.index', array_merge($listParams, ['search' => null])) }}" class="products-filter-chip-remove" aria-label="حذف فیلتر جستجو">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            </span>
                        @endif
                        @if($activeCategory)
                            <span class="products-filter-chip">
                                {{ $activeCategory->name }}
                                <a href="{{ route('products.index', array_merge($listParams, ['category' => null])) }}" class="products-filter-chip-remove" aria-label="حذف فیلتر دسته">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            </span>
                        @endif
                        @if($activeBrand)
                            <span class="products-filter-chip">
                                {{ $activeBrand->name }}
                                <a href="{{ route('products.index', array_merge($listParams, ['brand' => null])) }}" class="products-filter-chip-remove" aria-label="حذف فیلتر برند">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
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
                                <a href="{{ route('products.index', array_merge($listParams, ['sort' => null])) }}" class="products-filter-chip-remove" aria-label="حذف مرتب‌سازی">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            </span>
                        @endif
                        <a href="{{ route('products.index', $inShoppingFlow ? ['shop' => 1] : []) }}" class="products-clear-filters">پاک کردن همه</a>
                    </div>
                @endif

                @if($products->count())
                    <div class="home-products-grid">
                        @foreach($products as $product)
                            <x-product-card :product="$product" :list-return-url="request()->fullUrl()" />
                        @endforeach
                    </div>

                    @if($products->hasPages())
                        <div class="mt-10">{{ $products->links() }}</div>
                    @endif
                @else
                    <div class="products-empty">
                        <div class="products-empty-icon">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                        </div>
                        <h2 class="products-empty-title">محصولی با این فیلترها پیدا نشد</h2>
                        <p class="products-empty-desc">فیلترها را تغییر دهید یا به لیست کامل محصولات برگردید.</p>
                        <div class="products-empty-actions">
                            <a href="{{ route('products.index', $inShoppingFlow ? ['shop' => 1] : []) }}" class="btn-primary !rounded-xl !px-6 !py-3">نمایش همه محصولات</a>
                            <a href="{{ route('home') }}" class="link-shop">بازگشت به خانه</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
