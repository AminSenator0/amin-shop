@extends('layouts.admin')

@section('header', 'مدیریت صفحه اصلی')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-zinc-500">مدیریت محتوا، وضعیت بخش‌ها و تنظیمات صفحه اصلی فروشگاه</p>
    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="admin-btn-secondary text-xs">مشاهده فروشگاه</a>
</div>

<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5 mb-8">
    <x-admin.stat-card label="اسلایدر فعال" :value="$stats['sliders']" color="blue" :href="route('admin.sliders.index')" />
    <x-admin.stat-card label="بنر فعال" :value="$stats['banners']" color="violet" :href="route('admin.banners.index')" />
    <x-admin.stat-card label="سوالات متداول" :value="$stats['faqs']" color="default" :href="route('admin.faqs.index')" />
    <x-admin.stat-card label="مقالات" :value="$stats['blog_posts']" color="default" :href="route('admin.blog-posts.index')" />
    <x-admin.stat-card label="خبرنامه" :value="$stats['newsletter_subscribers']" color="emerald" :href="route('admin.newsletter.index')" />
</div>

<div class="grid gap-6 lg:grid-cols-2 mb-8">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">مدیریت محتوا</h2>
        </div>
        <div class="divide-y divide-zinc-100">
            @foreach([
                ['route' => 'admin.sliders.index', 'label' => 'اسلایدرها', 'count' => $stats['sliders'], 'hint' => 'کاروسل تبلیغاتی صفحه اصلی'],
                ['route' => 'admin.banners.index', 'label' => 'بنرها', 'count' => $stats['banners'], 'hint' => 'بنرهای پرومو (موقعیت: home)'],
                ['route' => 'admin.faqs.index', 'label' => 'سوالات متداول', 'count' => $stats['faqs'], 'hint' => 'بخش FAQ صفحه اصلی'],
                ['route' => 'admin.blog-posts.index', 'label' => 'مقالات', 'count' => $stats['blog_posts'], 'hint' => 'بلاگ صفحه اصلی'],
                ['route' => 'admin.brands.index', 'label' => 'برندها', 'count' => $stats['brands'], 'hint' => 'نمایش لوگوی برندها'],
                ['route' => 'admin.coupons.index', 'label' => 'کدهای تخفیف', 'count' => $stats['active_coupons'], 'hint' => 'بنر کد تخفیف بالای صفحه'],
                ['route' => 'admin.reviews.index', 'label' => 'نظرات', 'count' => $stats['approved_reviews'], 'hint' => 'نظرات تأییدشده مشتریان'],
                ['route' => 'admin.newsletter.index', 'label' => 'اعضای خبرنامه', 'count' => $stats['newsletter_subscribers'], 'hint' => 'ایمیل‌های ثبت‌شده'],
            ] as $item)
                <a href="{{ route($item['route']) }}" class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-zinc-50">
                    <div class="min-w-0">
                        <p class="font-bold text-zinc-900">{{ $item['label'] }}</p>
                        <p class="text-xs text-zinc-500">{{ $item['hint'] }}</p>
                    </div>
                    <span class="shrink-0 rounded-lg bg-zinc-100 px-2.5 py-1 text-xs font-bold text-zinc-700">{{ to_persian_digits((string) $item['count']) }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">محصولات صفحه اصلی</h2>
        </div>
        <div class="divide-y divide-zinc-100">
            <a href="{{ route('admin.products.index') }}" class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-zinc-50">
                <div>
                    <p class="font-bold text-zinc-900">محصولات ویژه</p>
                    <p class="text-xs text-zinc-500">بخش «محصولات ویژه» — فلگ is_featured</p>
                </div>
                <span class="rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700">{{ to_persian_digits((string) $stats['featured_products']) }}</span>
            </a>
            <a href="{{ route('admin.products.index') }}" class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-zinc-50">
                <div>
                    <p class="font-bold text-zinc-900">محصولات تخفیف‌دار</p>
                    <p class="text-xs text-zinc-500">compare_price بالاتر از price</p>
                </div>
                <span class="rounded-lg bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700">{{ to_persian_digits((string) $stats['discounted_products']) }}</span>
            </a>
            <a href="{{ route('admin.categories.index') }}" class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-zinc-50">
                <div>
                    <p class="font-bold text-zinc-900">دسته‌بندی‌ها</p>
                    <p class="text-xs text-zinc-500">کاشی دسته‌ها در صفحه اصلی</p>
                </div>
            </a>
        </div>
        <div class="border-t border-zinc-100 px-5 py-4">
            <a href="{{ route('admin.settings.edit', ['tab' => 'business']) }}" class="text-sm font-bold text-indigo-600 hover:underline">تنظیم بنر تبلیغاتی و آستانه ارسال رایگان ←</a>
        </div>
    </div>
</div>

<form id="settings" method="POST" action="{{ route('admin.homepage.update') }}" enctype="multipart/form-data" class="admin-card w-full scroll-mt-6">
    @csrf
    @method('PUT')

    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">تنظیمات بخش‌های صفحه اصلی</h2>
            <p class="mt-0.5 text-xs text-zinc-500">فعال/غیرفعال کردن بخش‌ها و تنظیم محتوای تکمیلی</p>
        </div>
    </div>

    <div class="space-y-5 p-6">
        @include('admin.homepage._settings-form')

        <div class="flex flex-wrap gap-3 border-t border-zinc-100 pt-6">
            <button type="submit" class="admin-btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                ذخیره تنظیمات
            </button>
        </div>
    </div>
</form>
@endsection
