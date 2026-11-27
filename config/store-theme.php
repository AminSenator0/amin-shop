<?php

/**
 * تم فروشگاه — تنظیمات مرکزی رنگ
 *
 * برای شخصی‌سازی پروژه:
 * 1. پالت‌های جدید را در `presets` اضافه/ویرایش کنید
 * 2. فرمول ساخت رنگ‌های مشتق‌شده را در `derivation` تنظیم کنید
 * 3. کلاس‌های Tailwind: shop-primary, shop-accent, shop-background, ...
 *    و متغیرهای CSS: --shop-primary, --shop-accent, ...
 */

return [

    'default_preset' => 'teal',

    /*
    |--------------------------------------------------------------------------
    | پالت‌های از پیش‌تعریف‌شده (فقط primary + accent)
    |--------------------------------------------------------------------------
    */
    'presets' => [
        'teal' => [
            'name' => 'فیروزه‌ای',
            'description' => 'رنگ پیش‌فرض فروشگاه',
            'primary' => '#16827D',
            'accent' => '#2EC4B6',
        ],

        'indigo' => [
            'name' => 'نیلی',
            'description' => 'حرفه‌ای و مدرن',
            'primary' => '#4338CA',
            'accent' => '#818CF8',
        ],
        'amber' => [
            'name' => 'کهربایی',
            'description' => 'گرم و پرانرژی',
            'primary' => '#B45309',
            'accent' => '#F59E0B',
        ],
        'rose' => [
            'name' => 'گیلاسی',
            'description' => 'شیک و جذاب',
            'primary' => '#9D174D',
            'accent' => '#EC4899',
        ],
        'Cream' => [
            'name' => 'کرم',
            'description' => 'مدرن و لوکس',
            'primary' => '#8B6048',
            'accent' => '#EED8C7',
        ],
        'ab30' => [
    'name' => 'کرم شکلاتی',
    'description' => 'لوکس، گرم و مینیمال',
    'primary' => '#A77B5A',
    'accent' => '#F9E0BB',
],
'mobina' => [
    'name' => 'کرم قهوه‌ای',
    'description' => 'قهوه‌ای و کرم؛ لوکس و گرم',
    'primary' => '#4A2F18',
    'accent' => '#C4956D',
],
'cream_gold' => [
    'name' => 'کرم طلایی',
    'description' => 'کرم روشن با جزئیات قهوه‌ای تیره',
    'primary' => '#F9E0BB',
    'accent' => '#4A2F18',
],
        'ocean' => [
            'name' => 'اقیانوسی',
            'description' => 'آرام و قابل اعتماد',
            'primary' => '#075985',
            'accent' => '#0EA5E9',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | فرمول ساخت پالت کامل از primary + accent
    |--------------------------------------------------------------------------
    */
 'derivation' => [
    'surface' => '#F9E0BB',

    'primary_hover_darken' => 0.12,
    'primary_dark_darken' => 0.25,
    'primary_deeper_darken' => 0.42,

    'accent_hover_darken' => 0.10,

    'background_lighten' => 0.95,
    'border_lighten' => 0.78,
    'on_hero_lighten' => 0.88,

    'text_base' => '#362419',
    'text_mix_weight' => 0.18,

    'muted_base' => '#4A2F18',
    'muted_mix_weight' => 0.12,
],

];
