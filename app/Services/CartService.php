<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class CartService
{
    private const SESSION_KEY = 'cart';

    public function lineKey(int $productId, ?string $size = null, ?string $color = null): string
    {
        return hash('xxh128', implode('|', [$productId, $size ?? '', $color ?? '']));
    }

    public function items(): Collection
    {
        $cart = session(self::SESSION_KEY, []);

        return collect($cart)->map(function (array $item, string $key) {
            $product = Product::with('category')->find($item['product_id']);

            if (! $product || ! $product->is_active) {
                return null;
            }

            $size = $item['size'] ?? null;
            $color = $item['color'] ?? null;

            return [
                'key' => $key,
                'product' => $product,
                'quantity' => $item['quantity'],
                'size' => $size,
                'color' => $color,
                'options_label' => $product->optionsLabel($size, $color),
                'subtotal' => $product->price * $item['quantity'],
            ];
        })->filter()->values();
    }

    public function count(): int
    {
        return $this->items()->sum('quantity');
    }

    public function quantityFor(int $productId): int
    {
        $cart = session(self::SESSION_KEY, []);

        return collect($cart)
            ->filter(fn (array $item) => ($item['product_id'] ?? null) === $productId)
            ->sum('quantity');
    }

    public function subtotal(): int
    {
        return $this->items()->sum('subtotal');
    }

    public function add(int $productId, int $quantity = 1, ?string $size = null, ?string $color = null): void
    {
        $product = Product::findOrFail($productId);

        if (! $product->is_active || $product->stock < 1) {
            throw new \RuntimeException('محصول موجود نیست.');
        }

        if ($product->hasSizes() && blank($size)) {
            throw new \RuntimeException('لطفاً سایز را انتخاب کنید.');
        }

        if ($product->hasColors() && blank($color)) {
            throw new \RuntimeException('لطفاً رنگ را انتخاب کنید.');
        }

        if (! $product->isValidSize($size) || ! $product->isValidColor($color)) {
            throw new \RuntimeException('گزینه انتخاب‌شده معتبر نیست.');
        }

        $cart = session(self::SESSION_KEY, []);
        $key = $this->lineKey($productId, $size, $color);
        $currentQty = $cart[$key]['quantity'] ?? 0;
        $newQty = $currentQty + $quantity;

        if ($newQty > $product->stock) {
            throw new \RuntimeException('تعداد درخواستی بیش از موجودی انبار است.');
        }

        $cart[$key] = [
            'product_id' => $productId,
            'quantity' => $newQty,
            'size' => $size,
            'color' => $color,
        ];

        session([self::SESSION_KEY => $cart]);
    }

    public function update(string $lineKey, int $quantity): void
    {
        $cart = session(self::SESSION_KEY, []);

        if (! isset($cart[$lineKey])) {
            throw new \RuntimeException('آیتم در سبد خرید یافت نشد.');
        }

        if ($quantity <= 0) {
            unset($cart[$lineKey]);
        } else {
            $product = Product::findOrFail($cart[$lineKey]['product_id']);

            if ($quantity > $product->stock) {
                throw new \RuntimeException('تعداد درخواستی بیش از موجودی انبار است.');
            }

            $cart[$lineKey]['quantity'] = $quantity;
        }

        session([self::SESSION_KEY => $cart]);
    }

    public function remove(string $lineKey): void
    {
        $cart = session(self::SESSION_KEY, []);
        unset($cart[$lineKey]);
        session([self::SESSION_KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function isEmpty(): bool
    {
        return $this->items()->isEmpty();
    }

    /**
     * بازگرداندن اقلام سفارش به سبد (برای پرداخت ناموفق / لغو درگاه).
     */
    public function restoreFromOrder(\App\Models\Order $order): void
    {
        $order->loadMissing('items');
        $cart = session(self::SESSION_KEY, []);

        foreach ($order->items as $item) {
            if (! $item->product_id) {
                continue;
            }

            $product = Product::query()->find($item->product_id);

            if (! $product || ! $product->is_active) {
                continue;
            }

            $size = $item->options['size'] ?? null;
            $color = $item->options['color'] ?? null;
            $key = $this->lineKey((int) $item->product_id, $size, $color);
            $quantity = max((int) ($cart[$key]['quantity'] ?? 0), (int) $item->quantity);

            if ($quantity > $product->stock) {
                $quantity = max(1, (int) $product->stock);
            }

            if ($quantity < 1) {
                continue;
            }

            $cart[$key] = [
                'product_id' => (int) $item->product_id,
                'quantity' => $quantity,
                'size' => $size,
                'color' => $color,
            ];
        }

        session([self::SESSION_KEY => $cart]);
    }
}
