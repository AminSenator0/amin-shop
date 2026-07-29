<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

class HomepageContent
{
    /** @return array{columns: list<string>, rows: list<array{size: string, values: list<string>}>} */
    public static function defaultSizeChart(): array
    {
        return [
            'columns' => ['دور سینه (cm)', 'دور کمر (cm)', 'قد (cm)'],
            'rows' => [
                ['size' => 'S', 'values' => ['86-90', '68-72', '65']],
                ['size' => 'M', 'values' => ['91-96', '73-78', '67']],
                ['size' => 'L', 'values' => ['97-102', '79-84', '69']],
                ['size' => 'XL', 'values' => ['103-108', '85-90', '71']],
                ['size' => 'XXL', 'values' => ['109-114', '91-96', '73']],
            ],
        ];
    }

    /** @return array{columns: list<string>, rows: list<array{size: string, values: list<string>}>} */
    public static function sizeChartSample(): array
    {
        $product = Product::query()
            ->where('is_active', true)
            ->whereNotNull('size_chart')
            ->first();

        if ($product?->hasSizeChart()) {
            return $product->size_chart;
        }

        return self::defaultSizeChart();
    }

    /**
     * @return list<array{title: string, subtitle: string, url: string, image: string, badge: ?string}>
     */
    public static function collections(
        Collection $categories,
        Collection $discountedProducts,
        Collection $bestsellerProducts,
        int $heroMaxDiscount,
        int $freeShippingThreshold,
    ): array {
        $items = [];

        if ($heroMaxDiscount > 0 && $discountedProducts->isNotEmpty()) {
            $thumb = $discountedProducts->first()?->thumbnailUrl();

            $items[] = [
                'title' => 'حراج و تخفیف',
                'subtitle' => 'تا '.to_persian_digits((string) $heroMaxDiscount).'٪ تخفیف',
                'url' => route('products.index', ['sort' => 'discount']),
                'image' => $thumb ?: hero_showcase_image_url(),
                'badge' => 'تخفیف',
            ];
        }

        if ($bestsellerProducts->isNotEmpty()) {
            $thumb = $bestsellerProducts->first()?->thumbnailUrl();

            $items[] = [
                'title' => 'پرفروش‌ترین‌ها',
                'subtitle' => 'محبوب‌ترین انتخاب مشتریان',
                'url' => route('products.index', ['sort' => 'bestseller']),
                'image' => $thumb ?: hero_showcase_image_url(),
                'badge' => 'پرفروش',
            ];
        }

        $items[] = [
            'title' => 'جدیدترین‌ها',
            'subtitle' => 'تازه‌ترین محصولات فروشگاه',
            'url' => route('products.index', ['sort' => 'newest']),
            'image' => hero_showcase_image_url(),
            'badge' => 'جدید',
        ];

        if ($freeShippingThreshold > 0) {
            $items[] = [
                'title' => 'ارسال رایگان',
                'subtitle' => 'سفارش بالای '.format_price($freeShippingThreshold),
                'url' => route('products.index'),
                'image' => hero_showcase_image_url(),
                'badge' => 'ویژه',
            ];
        }

        foreach ($categories->sortByDesc(fn ($category) => $category->activeProducts()->count())->take(4) as $index => $category) {
            $items[] = [
                'title' => $category->name,
                'subtitle' => $category->activeProducts()->count().' محصول',
                'url' => route('products.index', ['category' => $category->slug]),
                'image' => category_image_url($category->image, $index + 1, $category->name, $category->slug),
                'badge' => null,
            ];
        }

        return array_slice($items, 0, 6);
    }
}
