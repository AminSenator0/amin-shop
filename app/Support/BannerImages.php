<?php

namespace App\Support;

use Illuminate\Support\Str;

class BannerImages
{
    /** @var array<string, int> */
    public const ASSET_INDICES = [
        'ارسال سریع' => 1,
        'گارانتی اصالت' => 2,
        'پشتیبانی ۲۴ ساعته' => 3,
        'بازگشت ۷ روزه' => 4,
        'تخفیف اعضا' => 5,
        'پرداخت امن' => 6,
        'محصولات ویژه' => 7,
        'فروش فصلی' => 8,
        'ارسال رایگان' => 9,
        'ضمانت بازگشت' => 10,
        'پشتیبانی تلفنی' => 11,
        'تحویل درب منزل' => 12,
        'تخفیف اولین خرید' => 13,
        'برندهای معتبر' => 14,
        'پرداخت در محل' => 15,
    ];

    /** @var array<string, list<string>> */
    private const KEYWORDS = [
        'ارسال سریع' => ['ارسال سریع', 'تحویل'],
        'گارانتی اصالت' => ['گارانتی', 'اصالت'],
        'پشتیبانی ۲۴ ساعته' => ['پشتیبانی ۲۴', 'پشتیبانی'],
        'بازگشت ۷ روزه' => ['بازگشت', 'مرجوعی'],
        'تخفیف اعضا' => ['تخفیف اعضا', 'عضویت'],
        'پرداخت امن' => ['پرداخت امن', 'درگاه'],
        'محصولات ویژه' => ['محصولات ویژه', 'ویژه'],
        'فروش فصلی' => ['فروش فصلی', 'فصلی', 'حراج'],
        'ارسال رایگان' => ['ارسال رایگان', 'رایگان'],
        'ضمانت بازگشت' => ['ضمانت بازگشت'],
        'پشتیبانی تلفنی' => ['پشتیبانی تلفنی', 'تماس'],
        'تحویل درب منزل' => ['تحویل درب', 'درب منزل'],
        'تخفیف اولین خرید' => ['اولین خرید'],
        'برندهای معتبر' => ['برند'],
        'پرداخت در محل' => ['پرداخت در محل', 'نقد'],
    ];

    public static function assetIndexForTitle(?string $title): int
    {
        $title = trim((string) $title);

        if ($title === '') {
            return self::ASSET_INDICES['محصولات ویژه'];
        }

        if (isset(self::ASSET_INDICES[$title])) {
            return self::ASSET_INDICES[$title];
        }

        foreach (self::KEYWORDS as $banner => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_stripos($title, $keyword) !== false) {
                    return self::ASSET_INDICES[$banner];
                }
            }
        }

        return self::ASSET_INDICES['محصولات ویژه'];
    }

    public static function storageBasename(?string $title): string
    {
        $slug = Str::slug((string) $title);

        return $slug !== '' ? 'banner-'.$slug : 'banner-default';
    }
}
