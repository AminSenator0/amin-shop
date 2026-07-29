<?php

namespace App\Support;

use Illuminate\Support\Str;

class SliderImages
{
    /** @var array<string, int> */
    public const ASSET_INDICES = [
        'تخفیف ویژه بهاره' => 1,
        'محصولات پرفروش' => 2,
        'ارسال رایگان' => 3,
        'جدیدترین‌ها' => 4,
        'پیشنهاد هفته' => 5,
        'لوازم خانگی' => 6,
        'کالای دیجیتال' => 7,
        'پوشاک زمستانه' => 8,
        'حراج تابستان' => 9,
        'برندهای برتر' => 10,
        'فروش ویژه' => 11,
        'محصولات جدید' => 12,
        'تخفیف آخر هفته' => 13,
        'کالکشن پاییز' => 14,
        'پیشنهاد ویژه اعضا' => 15,
    ];

    /** @var array<string, list<string>> */
    private const KEYWORDS = [
        'تخفیف ویژه بهاره' => ['بهاره', 'تخفیف ویژه'],
        'محصولات پرفروش' => ['پرفروش'],
        'ارسال رایگان' => ['ارسال رایگان', 'ارسال'],
        'جدیدترین‌ها' => ['جدیدترین', 'جدید'],
        'پیشنهاد هفته' => ['پیشنهاد هفته'],
        'لوازم خانگی' => ['لوازم خانگی', 'خانگی'],
        'کالای دیجیتال' => ['دیجیتال', 'گجت'],
        'پوشاک زمستانه' => ['پوشاک', 'زمستان'],
        'حراج تابستان' => ['تابستان', 'حراج'],
        'برندهای برتر' => ['برند'],
        'فروش ویژه' => ['فروش ویژه'],
        'محصولات جدید' => ['محصولات جدید'],
        'تخفیف آخر هفته' => ['آخر هفته', 'تخفیف'],
        'کالکشن پاییز' => ['پاییز', 'کالکشن'],
        'پیشنهاد ویژه اعضا' => ['اعضا', 'عضویت'],
    ];

    public static function assetIndexForTitle(?string $title): int
    {
        $title = trim((string) $title);

        if ($title === '') {
            return self::ASSET_INDICES['محصولات پرفروش'];
        }

        if (isset(self::ASSET_INDICES[$title])) {
            return self::ASSET_INDICES[$title];
        }

        foreach (self::KEYWORDS as $slide => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_stripos($title, $keyword) !== false) {
                    return self::ASSET_INDICES[$slide];
                }
            }
        }

        return self::ASSET_INDICES['محصولات پرفروش'];
    }

    public static function storageBasename(?string $title): string
    {
        $slug = Str::slug((string) $title);

        return $slug !== '' ? 'slider-'.$slug : 'slider-default';
    }
}
