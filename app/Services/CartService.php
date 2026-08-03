<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class CartService
{
    private const SESSION_KEY = 'cart';

    public function lineKey(int $productId, ?string $size = null, ?string $color = null, array $customFields = []): string
    {
        $parts = [$productId, $size ?? '', $color ?? ''];

        if (! empty($customFields)) {
            ksort($customFields);
            $parts[] = json_encode($customFields, JSON_UNESCAPED_UNICODE);
        }

        return hash('xxh128', implode('|', $parts));
    }

    public function items(): Collection
    {
        $cart = session(self::SESSION_KEY, []);

        if (empty($cart)) {
            return collect();
        }

        // ✅ همه محصولات رو یه‌جا لود کن (به جای find توی loop)
        $productIds = collect($cart)->pluck('product_id')->unique()->values()->all();

        $products = Product::with(['category', 'customFields', 'images'])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        return collect($cart)->map(function (array $item, string $key) use ($products) {
            $product = $products->get($item['product_id']);

            if (! $product || ! $product->is_active) {
                return null;
            }

            $size = $item['size'] ?? null;
            $color = $item['color'] ?? null;
            $customFields = $item['custom_fields'] ?? [];

            return [
                'key' => $key,
                'product' => $product,
                'quantity' => $item['quantity'],
                'size' => $size,
                'color' => $color,
                'custom_fields' => $customFields,
                'custom_fields_display' => $this->enrichCustomFields($product, $customFields),
                'options_label' => $product->optionsLabel($size, $color),
                'subtotal' => $product->price * $item['quantity'],
            ];
        })->filter()->values();
    }

    // ✅ مستقیم از session بخون — نیازی به لود محصول نیست
    public function count(): int
    {
        $cart = session(self::SESSION_KEY, []);
        return collect($cart)->sum('quantity');
    }

    public function quantityFor(int $productId): int
    {
        $cart = session(self::SESSION_KEY, []);

        return collect($cart)
            ->filter(fn (array $item) => ($item['product_id'] ?? null) === $productId)
            ->sum('quantity');
    }

    // ✅ کوئری سبک فقط برای price — بدون لود relation
    public function subtotal(): int
    {
        $cart = session(self::SESSION_KEY, []);

        if (empty($cart)) {
            return 0;
        }

        $productIds = collect($cart)->pluck('product_id')->unique()->values()->all();
        $prices = Product::whereIn('id', $productIds)->pluck('price', 'id');

        return collect($cart)->sum(function ($item) use ($prices) {
            return ($prices->get($item['product_id'], 0) * $item['quantity']);
        });
    }

    public function add(int $productId, int $quantity = 1, ?string $size = null, ?string $color = null, array $customFields = []): void
    {
        $product = Product::with('customFields')->findOrFail($productId);

        if (! $product->is_active || ! $product->isInStock()) {
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

        $validatedCustomFields = $this->validateCustomFields($product, $customFields);

        $cart = session(self::SESSION_KEY, []);
        $key = $this->lineKey($productId, $size, $color, $validatedCustomFields);
        $currentQty = $cart[$key]['quantity'] ?? 0;
        $newQty = $currentQty + $quantity;

        if (! $product->hasEnoughStock($newQty, $size, $color)) {
            throw new \RuntimeException('تعداد درخواستی بیش از موجودی انبار است.');
        }

        $cart[$key] = [
            'product_id' => $productId,
            'quantity' => $newQty,
            'size' => $size,
            'color' => $color,
            'custom_fields' => $validatedCustomFields,
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
            $size = $cart[$lineKey]['size'] ?? null;
            $color = $cart[$lineKey]['color'] ?? null;

            if (! $product->hasEnoughStock($quantity, $size, $color)) {
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

    public function restoreFromOrder(\App\Models\Order $order): void
    {
        $order->loadMissing('items');
        $cart = session(self::SESSION_KEY, []);

        // ✅ batch load همه محصولات
        $productIds = $order->items->pluck('product_id')->filter()->unique()->values()->all();

        if (empty($productIds)) {
            return;
        }

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        foreach ($order->items as $item) {
            if (! $item->product_id) {
                continue;
            }

            $product = $products->get($item->product_id);

            if (! $product || ! $product->is_active) {
                continue;
            }

            $size = $item->options['size'] ?? null;
            $color = $item->options['color'] ?? null;
            $customFields = $item->custom_fields ?? [];
            $key = $this->lineKey((int) $item->product_id, $size, $color, $customFields);
            $quantity = max((int) ($cart[$key]['quantity'] ?? 0), (int) $item->quantity);

            if (! $product->hasEnoughStock($quantity, $size, $color)) {
                $quantity = max(1, (int) $product->variantStock($size, $color));
            }

            if ($quantity < 1) {
                continue;
            }

            $cart[$key] = [
                'product_id' => (int) $item->product_id,
                'quantity' => $quantity,
                'size' => $size,
                'color' => $color,
                'custom_fields' => $customFields,
            ];
        }

        session([self::SESSION_KEY => $cart]);
    }

    private function validateCustomFields(Product $product, array $inputFields): array
    {
        if (! $product->relationLoaded('customFields')) {
            $product->load('customFields');
        }

        $validated = [];

        foreach ($product->customFields as $field) {
            $value = $inputFields[$field->id] ?? null;

            if ($field->is_required && blank($value)) {
                throw new \RuntimeException("فیلد «{$field->label}» الزامی است.");
            }

            if ($field->type === 'select' && filled($value)) {
                $options = $field->options ?? [];
                if (! in_array($value, $options, true)) {
                    throw new \RuntimeException("گزینه انتخاب‌شده برای «{$field->label}» معتبر نیست.");
                }
            }

            if (filled($value)) {
                $validated[$field->id] = [
                    'label' => $field->label,
                    'value' => $value,
                ];
            }
        }

        return $validated;
    }

    private function enrichCustomFields(Product $product, array $storedFields): array
    {
        if (empty($storedFields)) {
            return [];
        }

        $enriched = [];

        foreach ($storedFields as $fieldId => $data) {
            $enriched[] = [
                'id' => $fieldId,
                'label' => $data['label'] ?? 'فیلد سفارشی',
                'value' => $data['value'] ?? '',
            ];
        }

        return $enriched;
    }
}