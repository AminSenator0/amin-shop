<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class RecentlyViewedService
{
    private const SESSION_KEY = 'recently_viewed';

    private const MAX_ITEMS = 12;

    public function track(int $productId): void
    {
        $ids = $this->ids();
        $ids = array_values(array_diff($ids, [$productId]));
        array_unshift($ids, $productId);
        $ids = array_slice($ids, 0, self::MAX_ITEMS);

        session([self::SESSION_KEY => $ids]);
    }

    /** @return list<int> */
    public function ids(): array
    {
        $ids = session(self::SESSION_KEY, []);

        return is_array($ids) ? array_map('intval', $ids) : [];
    }

    public function products(int $limit = 8): Collection
    {
        $ids = array_slice($this->ids(), 0, $limit);

        if (empty($ids)) {
            return collect();
        }

        $products = Product::with(['category', 'images'])
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        return collect($ids)
            ->map(fn ($id) => $products->get($id))
            ->filter();
    }

    public function lastCategoryId(): ?int
    {
        $product = $this->products(1)->first();

        return $product?->category_id;
    }
}
