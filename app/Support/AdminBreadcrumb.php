<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class AdminBreadcrumb
{
    /** @var array<string, array{label: string, index: string}> */
    private const SECTIONS = [
        'dashboard' => ['label' => 'داشبورد', 'index' => 'admin.dashboard'],
        'orders' => ['label' => 'سفارشات', 'index' => 'admin.orders.index'],
        'returns' => ['label' => 'مرجوعی‌ها', 'index' => 'admin.returns.index'],
        'financial' => ['label' => 'گزارش مالی', 'index' => 'admin.financial.index'],
        'products' => ['label' => 'محصولات', 'index' => 'admin.products.index'],
        'categories' => ['label' => 'دسته‌بندی‌ها', 'index' => 'admin.categories.index'],
        'brands' => ['label' => 'برندها', 'index' => 'admin.brands.index'],
        'reviews' => ['label' => 'نظرات', 'index' => 'admin.reviews.index'],
        'coupons' => ['label' => 'کدهای تخفیف', 'index' => 'admin.coupons.index'],
        'sliders' => ['label' => 'اسلایدرها', 'index' => 'admin.sliders.index'],
        'banners' => ['label' => 'بنرها', 'index' => 'admin.banners.index'],
        'homepage' => ['label' => 'مدیریت صفحه اصلی', 'index' => 'admin.homepage.index'],
        'faqs' => ['label' => 'سوالات متداول', 'index' => 'admin.faqs.index'],
        'blog-posts' => ['label' => 'مقالات', 'index' => 'admin.blog-posts.index'],
        'newsletter' => ['label' => 'اعضای خبرنامه', 'index' => 'admin.newsletter.index'],
        'users' => ['label' => 'کاربران', 'index' => 'admin.users.index'],
        'messages' => ['label' => 'پیام‌ها', 'index' => 'admin.messages.index'],
        'shipping' => ['label' => 'روش‌های ارسال', 'index' => 'admin.shipping.index'],
        'settings' => ['label' => 'تنظیمات فروشگاه', 'index' => 'admin.settings.edit'],
    ];

    /** @var array<string, string> */
    private const GROUPS = [
        'dashboard' => 'اصلی',
        'orders' => 'اصلی',
        'returns' => 'اصلی',
        'financial' => 'مالی',
        'products' => 'کاتالوگ',
        'categories' => 'کاتالوگ',
        'brands' => 'کاتالوگ',
        'reviews' => 'کاتالوگ',
        'coupons' => 'بازاریابی',
        'sliders' => 'بازاریابی',
        'banners' => 'بازاریابی',
        'homepage' => 'صفحه اصلی',
        'faqs' => 'صفحه اصلی',
        'blog-posts' => 'صفحه اصلی',
        'newsletter' => 'صفحه اصلی',
        'users' => 'مشتریان',
        'messages' => 'مشتریان',
        'shipping' => 'تنظیمات',
        'settings' => 'تنظیمات',
    ];

    /**
     * @return array<int, array{label: string, url: string|null}>
     */
    public static function items(?string $currentTitle = null): array
    {
        $items = [
            ['label' => 'پنل مدیریت', 'url' => route('admin.dashboard')],
        ];

        $routeName = Route::currentRouteName();

        if (! $routeName || ! str_starts_with($routeName, 'admin.')) {
            return $items;
        }

        if ($routeName === 'admin.dashboard') {
            $items[] = ['label' => $currentTitle ?? 'داشبورد', 'url' => null];

            return $items;
        }

        $suffix = substr($routeName, strlen('admin.'));
        $parts = explode('.', $suffix);
        $resource = $parts[0] ?? null;
        $action = $parts[1] ?? 'index';

        if (! $resource || ! isset(self::SECTIONS[$resource])) {
            if ($currentTitle) {
                $items[] = ['label' => $currentTitle, 'url' => null];
            }

            return $items;
        }

        $section = self::SECTIONS[$resource];
        $isIndex = $action === 'index'
            || ($resource === 'settings' && $action === 'edit')
            || ($resource === 'homepage' && $action === 'update');

        if ($isIndex) {
            $items[] = ['label' => $currentTitle ?? $section['label'], 'url' => null];

            return $items;
        }

        $items[] = ['label' => $section['label'], 'url' => route($section['index'])];
        $items[] = ['label' => $currentTitle ?? self::actionLabel($resource, $action), 'url' => null];

        return $items;
    }

    public static function group(): ?string
    {
        $routeName = Route::currentRouteName();

        if (! $routeName || ! str_starts_with($routeName, 'admin.')) {
            return null;
        }

        if ($routeName === 'admin.dashboard') {
            return self::GROUPS['dashboard'];
        }

        $resource = explode('.', substr($routeName, strlen('admin.')))[0] ?? null;

        return $resource ? (self::GROUPS[$resource] ?? null) : null;
    }

    private static function actionLabel(string $resource, string $action): string
    {
        return match ($action) {
            'create' => 'جدید',
            'edit' => 'ویرایش',
            'show' => 'مشاهده',
            default => self::SECTIONS[$resource]['label'],
        };
    }
}
