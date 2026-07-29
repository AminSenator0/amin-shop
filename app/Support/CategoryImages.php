<?php

namespace App\Support;

class CategoryImages
{
    /**
     * شاخص تصاویر محلی — کلید: نام دقیق دسته در دیتابیس.
     *
     * @var array<string, int>
     */
    public const ASSET_INDICES = [
        'لوازم خانگی' => 1,
        'پوشاک' => 2,
        'کتاب' => 3,
        'لوازم الکترونیکی' => 4,
        'آرایشی بهداشتی' => 5,
        'ورزشی' => 6,
        'اسباب‌بازی' => 7,
        'لوازم التحریر' => 8,
        'کالای دیجیتال' => 9,
        'خانه و آشپزخانه' => 10,
        'ابزار و یراق' => 11,
        'زیورآلات' => 12,
        'کفش' => 13,
        'ساعت' => 14,
        'عطر و ادکلن' => 15,
    ];

    /**
     * @var array<string, list<string>>
     */
    private const KEYWORDS = [
        'لوازم خانگی' => ['لوازم خانگی', 'خانگی', 'جارو', 'یخچال', 'ماشین لباس'],
        'پوشاک' => ['پوشاک', 'لباس', 'مد', 'مانتو', 'پیراهن'],
        'کتاب' => ['کتاب', 'ادبیات', 'رمان'],
        'لوازم الکترونیکی' => ['لوازم الکترونیک', 'الکترونیک'],
        'آرایشی بهداشتی' => ['آرایش', 'بهداشت', 'زیبایی', 'مکاپ', 'کرم'],
        'ورزشی' => ['ورزش', 'ورزشی', 'فیتنس'],
        'اسباب‌بازی' => ['اسباب', 'بازی', 'اسباب‌بازی'],
        'لوازم التحریر' => ['التحریر', 'دفتر', 'مداد'],
        'کالای دیجیتال' => ['دیجیتال', 'موبایل', 'تبلت', 'گوشی'],
        'خانه و آشپزخانه' => ['آشپزخانه', 'خانه و آشپزخانه', 'ظروف', 'قابلمه'],
        'ابزار و یراق' => ['ابزار', 'یراق', 'دریل'],
        'زیورآلات' => ['زیور', 'جواهر', 'طلا', 'نقره'],
        'کفش' => ['کفش', 'کتانی'],
        'ساعت' => ['ساعت'],
        'عطر و ادکلن' => ['عطر', 'ادکلن', 'اسپرت'],
    ];

    public static function assetIndexForName(?string $name): int
    {
        $name = trim((string) $name);

        if ($name === '') {
            return 1;
        }

        if (isset(self::ASSET_INDICES[$name])) {
            return self::ASSET_INDICES[$name];
        }

        foreach (self::KEYWORDS as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_stripos($name, $keyword) !== false) {
                    return self::ASSET_INDICES[$category];
                }
            }
        }

        return 1;
    }

    /** @return array<int, int> */
    public static function productAssetIndicesForName(?string $name): array
    {
        $index = self::assetIndexForName($name);

        return array_values(array_unique([$index, $index + 1]));
    }

    public static function resolveCategoryKey(?string $name): ?string
    {
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        if (isset(self::ASSET_INDICES[$name])) {
            return $name;
        }

        foreach (self::KEYWORDS as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (mb_stripos($name, $keyword) !== false) {
                    return $category;
                }
            }
        }

        return null;
    }

    public static function storageBasename(?string $name, ?string $slug = null): string
    {
        $slug = trim((string) ($slug ?: \Illuminate\Support\Str::slug((string) $name)));

        return $slug !== '' ? 'category-'.$slug : 'category-default';
    }
}
