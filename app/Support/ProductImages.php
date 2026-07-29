<?php

namespace App\Support;

class ProductImages
{
    /**
     * گالری تصاویر هر محصول — کلید: SKU.
     *
     * @var array<string, list<int>>
     */
    public const GALLERIES_BY_SKU = [
        'SKU-00001' => [1, 2],
        'SKU-00002' => [2, 3],
        'SKU-00003' => [3, 4],
        'SKU-00004' => [4, 5],
        'SKU-00005' => [5, 6],
        'SKU-00006' => [6, 7],
        'SKU-00007' => [7, 8],
        'SKU-00008' => [8, 9],
        'SKU-00009' => [9, 10],
        'SKU-00010' => [10, 11],
        'SKU-00011' => [11, 12],
        'SKU-00012' => [12, 13],
        'SKU-00013' => [13, 14],
        'SKU-00014' => [14, 15],
        'SKU-00015' => [15, 1],
        'SKU-00016' => [1, 2],
        'SKU-00017' => [2, 3],
        'SKU-00018' => [3, 4],
    ];

    /**
     * @var array<string, list<int>>
     */
    private const GALLERIES_BY_KEYWORD = [
        'یخچال ساید' => [1, 2],
        'یخچال' => [1, 2],
        'ماشین لباسشویی' => [1, 2],
        'کاپشن' => [2, 3],
        'پلی‌استیشن' => [4, 5],
        'آیفون' => [9, 10],
        'گوشی اپل' => [9, 10],
        'اپل واچ' => [14, 15],
        'ساعت هوشمند' => [14, 15],
        'نایکی ایر' => [13, 14],
        'کفش ورزشی' => [13, 14],
        'هدفون' => [3, 4],
        'لپ‌تاپ' => [2, 3],
        'گردنبند طلا' => [12, 13],
        'عطر' => [15, 1],
        'ادکلن' => [15, 1],
        'دریل' => [11, 12],
        'قابلمه' => [10, 11],
        'دمبل' => [6, 7],
        'لگو' => [7, 8],
        'پازل ساختنی' => [7, 8],
        'خودکار' => [8, 9],
        'مرطوب‌کننده' => [5, 6],
        'سرم آبرسان' => [5, 6],
        'کتاب' => [3, 4],
    ];

    /** @return list<int> */
    public static function galleryAssetIndices(?string $sku, ?string $productName = null): array
    {
        $sku = trim((string) $sku);

        if ($sku !== '' && isset(self::GALLERIES_BY_SKU[$sku])) {
            return self::GALLERIES_BY_SKU[$sku];
        }

        $fromName = self::indicesFromProductName($productName);

        if ($fromName !== []) {
            return $fromName;
        }

        return CategoryImages::productAssetIndicesForName($productName);
    }

    public static function storageBasename(string $sku, int $index): string
    {
        $normalized = strtolower(str_replace(['SKU-', ' '], ['', '-'], $sku));

        return sprintf('product-%s-%d', $normalized, $index);
    }

    /** @return list<int> */
    private static function indicesFromProductName(?string $productName): array
    {
        $productName = trim((string) $productName);

        if ($productName === '') {
            return [];
        }

        $matched = [];
        $matchedLength = 0;

        foreach (self::GALLERIES_BY_KEYWORD as $keyword => $indices) {
            if (mb_stripos($productName, $keyword) === false) {
                continue;
            }

            $length = mb_strlen($keyword);

            if ($length > $matchedLength) {
                $matched = $indices;
                $matchedLength = $length;
            }
        }

        return $matched;
    }
}
