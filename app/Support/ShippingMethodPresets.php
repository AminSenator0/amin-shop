<?php

namespace App\Support;

class ShippingMethodPresets
{
    /**
     * @return array<int, array{id: string, name: string, description: string, cost: int, free_above: int|null, estimated_days: int}>
     */
    public static function all(): array
    {
        return [
            [
                'id' => 'post-pishtaz',
                'name' => 'پست پیشتاز',
                'description' => 'ارسال سراسری با پست پیشتاز',
                'cost' => 50_000,
                'free_above' => null,
                'estimated_days' => 5,
            ],
            [
                'id' => 'post-sefareshi',
                'name' => 'پست سفارشی',
                'description' => 'ارسال مطمئن با رهگیری پستی',
                'cost' => 65_000,
                'free_above' => null,
                'estimated_days' => 3,
            ],
            [
                'id' => 'peyk-motori',
                'name' => 'پیک موتوری',
                'description' => 'تحویل سریع درون‌شهری',
                'cost' => 80_000,
                'free_above' => null,
                'estimated_days' => 1,
            ],
            [
                'id' => 'tipax',
                'name' => 'تیپاکس',
                'description' => 'ارسال با باربری تیپاکس',
                'cost' => 70_000,
                'free_above' => null,
                'estimated_days' => 4,
            ],
            [
                'id' => 'post-adi',
                'name' => 'پست عادی',
                'description' => 'گزینه اقتصادی با زمان بیشتر',
                'cost' => 35_000,
                'free_above' => null,
                'estimated_days' => 7,
            ],
            [
                'id' => 'express',
                'name' => 'ارسال اکسپرس',
                'description' => 'تحویل فوری در همان روز یا روز بعد',
                'cost' => 120_000,
                'free_above' => null,
                'estimated_days' => 1,
            ],
            [
                'id' => 'pickup',
                'name' => 'تحویل در محل',
                'description' => 'دریافت سفارش از فروشگاه یا انبار',
                'cost' => 0,
                'free_above' => null,
                'estimated_days' => 1,
            ],
            [
                'id' => 'free-shipping',
                'name' => 'ارسال رایگان',
                'description' => 'رایگان برای سفارش‌های بالای حد مشخص',
                'cost' => 0,
                'free_above' => 500_000,
                'estimated_days' => 5,
            ],
        ];
    }
}
