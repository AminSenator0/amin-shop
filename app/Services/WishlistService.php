<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Collection;

class WishlistService
{
    private const SESSION_KEY = 'wishlist';

    public function ids(): array
    {
        if (auth()->check()) {
            return Wishlist::where('user_id', auth()->id())->pluck('product_id')->all();
        }

        return array_map('intval', session(self::SESSION_KEY, []));
    }

    public function count(): int
    {
        return count($this->ids());
    }

    public function has(int $productId): bool
    {
        return in_array($productId, $this->ids(), true);
    }

    public function toggle(Product $product): bool
    {
        if ($this->has($product->id)) {
            $this->remove($product->id);

            return false;
        }

        $this->add($product);

        return true;
    }

    public function add(Product $product): void
    {
        if (auth()->check()) {
            Wishlist::firstOrCreate(['user_id' => auth()->id(), 'product_id' => $product->id]);

            return;
        }

        $list = session(self::SESSION_KEY, []);
        if (! in_array($product->id, $list, true)) {
            $list[] = $product->id;
            session([self::SESSION_KEY => $list]);
        }
    }

    public function remove(int $productId): void
    {
        if (auth()->check()) {
            Wishlist::where('user_id', auth()->id())->where('product_id', $productId)->delete();

            return;
        }

        session([self::SESSION_KEY => array_values(array_filter(
            session(self::SESSION_KEY, []),
            fn ($id) => (int) $id !== $productId
        ))]);
    }

    public function products(): Collection
    {
        $ids = $this->ids();
        if (empty($ids)) {
            return collect();
        }

        return Product::with('category')->whereIn('id', $ids)->where('is_active', true)->get();
    }
}
