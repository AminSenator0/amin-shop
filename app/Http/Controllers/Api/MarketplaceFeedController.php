<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class MarketplaceFeedController extends Controller
{
    /**
     * واحد قیمت در خروجی فید.
     * پروژه قیمت را به ریال ذخیره می‌کند و ترب/ایمالز هم ریال می‌خواهند → 1
     * اگر روزی واحد دیتابیس تومان شد، فقط اینجا را 10 کنید.
     */
    private const PRICE_UNIT = 1;

    private const CACHE_TTL = 3600; // ۱ ساعت

    // ---------------------------------------------------------------
    //  ترب (Torob) — /api/v3/torob/products
    // ---------------------------------------------------------------
    public function torob(): JsonResponse
    {
        $payload = Cache::remember('marketplace.feed.torob', self::CACHE_TTL, function () {
            return [
                'products' => $this->feedProducts()->map(fn (array $p) => [
                    'product_id' => (string) $p['id'],
                    'title'      => $p['name'],
                    'category'   => $p['category'],
                    'price'      => (int) round($p['price'] * self::PRICE_UNIT),
                    'old_price'  => $p['old_price'] !== null
                        ? (int) round($p['old_price'] * self::PRICE_UNIT)
                        : null,
                    'availability' => $p['in_stock'] ? 'instock' : 'outofstock',
                    'image' => $p['image'],
                    'url'   => $p['url'],
                ])->values()->all(),
            ];
        });

        return response()->json($payload, 200, [], JSON_UNESCAPED_UNICODE);
    }

    // ---------------------------------------------------------------
    //  ایمالز (Emalls) — /api/v1/emalls/products
    // ---------------------------------------------------------------
    public function emalls(): JsonResponse
    {
        $payload = Cache::remember('marketplace.feed.emalls', self::CACHE_TTL, function () {
            return [
                'products' => $this->feedProducts()->map(fn (array $p) => [
                    'id'           => (string) $p['id'],
                    'title'        => $p['name'],
                    'category'     => $p['category'],
                    'price'        => (int) round($p['price'] * self::PRICE_UNIT),
                    'old_price'    => $p['old_price'] !== null
                        ? (int) round($p['old_price'] * self::PRICE_UNIT)
                        : null,
                    'availability' => $p['in_stock'] ? 'instock' : 'outofstock',
                    'image'        => $p['image'],
                    'url'          => $p['url'],
                ])->values()->all(),
            ];
        });

        return response()->json($payload, 200, [], JSON_UNESCAPED_UNICODE);
    }

    // ---------------------------------------------------------------
    //  لود یکجای محصولات — بدون N+1 (همان منطق isInStock مدل)
    // ---------------------------------------------------------------
    private function feedProducts(): \Illuminate\Support\Collection
    {
        return Product::query()
            ->select(['id', 'name', 'slug', 'price', 'compare_price', 'stock', 'category_id', 'image'])
            ->where('is_active', true)
            ->with('category:id,name')
            ->with('images:id,product_id,path')
            ->withSum('variants as variants_stock_sum', 'stock')
            ->cursor()
            ->map(function (Product $p) {
                // دقیقاً معادل Product::isInStock() ولی بدون کوئری اضافه:
                // hasVariants() یعنی variants_stock_sum !== null
                $inStock = $p->variants_stock_sum !== null
                    ? $p->variants_stock_sum > 0
                    : $p->stock > 0;

                $path = $p->image ?: $p->images->first()?->path;

                return [
                    'id'        => $p->id,
                    'name'      => $p->name,
                    'category'  => $p->category?->name,
                    'price'     => (int) $p->price,
                    'old_price' => $p->hasDiscount() ? (int) $p->compare_price : null,
                    'in_stock'  => $inStock,
                    'image'     => $path ? asset('storage/'.$path) : null,
                    'url'       => route('products.show', $p->slug),
                ];
            })
            ->values()->collect();
    }
}