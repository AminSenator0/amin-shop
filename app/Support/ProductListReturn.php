<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductListReturn
{
    public const SESSION_KEY = 'products_list_return_url';

    public static function remember(Request $request): void
    {
        session([self::SESSION_KEY => $request->fullUrl()]);
    }

    public static function resolve(Request $request, Product $product): string
    {
        foreach (self::candidates($request) as $url) {
            if (self::isProductsListUrl($url)) {
                return $url;
            }
        }

        return route('products.index', array_filter([
            'category' => $product->category->slug,
            'shop' => ShoppingFlow::isActive() ? 1 : null,
        ]));
    }

    public static function label(string $url): string
    {
        $query = parse_url($url, PHP_URL_QUERY);

        if (! is_string($query) || $query === '') {
            return 'بازگشت به همه محصولات';
        }

        parse_str($query, $params);

        if (! empty($params['category'])) {
            $category = Category::query()
                ->where('slug', $params['category'])
                ->value('name');

            if ($category) {
                return 'بازگشت به '.$category;
            }
        }

        if (! empty($params['search'])) {
            return 'بازگشت به نتایج جستجو';
        }

        return 'بازگشت به لیست محصولات';
    }

    /**
     * @return array<int, string|null>
     */
    private static function candidates(Request $request): array
    {
        return [
            $request->query('back'),
            url()->previous(),
            session(self::SESSION_KEY),
        ];
    }

    private static function isProductsListUrl(?string $url): bool
    {
        if (blank($url)) {
            return false;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if ($path === null && str_starts_with($url, '/products')) {
            $path = strtok($url, '?') ?: '';
        }

        if (rtrim((string) $path, '/') !== '/products') {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return $host === null || $host === request()->getHost();
    }

    public static function productUrl(Product $product, ?string $listReturnUrl = null): string
    {
        $url = route('products.show', $product->slug);

        if ($listReturnUrl && self::isProductsListUrl($listReturnUrl)) {
            $url .= '?back='.urlencode($listReturnUrl);
        }

        return $url;
    }
}
