<?php

namespace App\Services;

use App\Models\PresaleCampaign;
use App\Models\PresaleProduct;
use Illuminate\Support\Collection;

class PresaleCartService
{
    private const SESSION_KEY = 'presale_cart';

    /**
     * آیا سبد پیش‌فروش خالی است؟
     */
    public function isEmpty(): bool
    {
        return empty(session(self::SESSION_KEY, []));
    }

    /**
     * تعداد آیتم‌ها (تعداد خطوط، نه مجموع quantity)
     */
    public function count(): int
    {
        return count(session(self::SESSION_KEY, []));
    }

    /**
     * لیست آیتم‌های سبد با اطلاعات کامل
     */
    public function items(): Collection
    {
        $cart = session(self::SESSION_KEY, []);

        if (empty($cart)) {
            return collect();
        }

        $ids = collect($cart)->keys()->map(fn ($k) => (int) $k)->unique()->values()->all();

        $products = PresaleProduct::with(['product.images', 'campaign'])
            ->whereIn('id', $ids)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        return collect($cart)->map(function (array $item, string $key) use ($products) {
            $presaleProduct = $products->get((int) $key);

            if (! $presaleProduct || ! $presaleProduct->campaign?->isOpenNow()) {
                return null;
            }

            $qty = (int) ($item['quantity'] ?? 1);

            return [
                'key'             => $key,
                'presale_product' => $presaleProduct,
                'product'         => $presaleProduct->product,
                'quantity'        => $qty,
                'presale_price'   => (int) $presaleProduct->presale_price,
                'subtotal'        => (int) $presaleProduct->presale_price * $qty,
            ];
        })->filter()->values();
    }

    /**
     * مجموع پیش‌پرداخت سبد
     */
    public function subtotal(): int
    {
        return $this->items()->sum(fn ($item) => $item['subtotal']);
    }

    /**
     * افزودن محصول پیش‌فروش به سبد
     */
    public function add(int $presaleProductId, int $quantity = 1): void
    {
        if ($quantity < 1) {
            throw new \RuntimeException('تعداد باید حداقل ۱ باشد.');
        }

        $presaleProduct = PresaleProduct::with('campaign')->findOrFail($presaleProductId);

        if (! $presaleProduct->is_active) {
            throw new \RuntimeException('این محصول در حال حاضر در دسترس نیست.');
        }

        if (! $presaleProduct->campaign->isOpenNow()) {
            throw new \RuntimeException('مهلت پیش‌فروش این محصول به پایان رسیده است.');
        }

        // چک: سبد عادی باید خالی باشد
        $regularCart = session('cart', []);
        if (! empty($regularCart)) {
            throw new \RuntimeException(
                'سبد خرید عادی شما پر است. لطفاً ابتدا سبد عادی را خالی کنید یا خرید خود را تکمیل کنید.'
            );
        }

        $cart = session(self::SESSION_KEY, []);
        $currentQty = $cart[$presaleProductId]['quantity'] ?? 0;
        $newQty = $currentQty + $quantity;

        $cart[$presaleProductId] = [
            'presale_product_id' => $presaleProductId,
            'quantity'           => $newQty,
        ];

        session([self::SESSION_KEY => $cart]);
    }

    /**
     * آپدیت تعداد یک آیتم
     */
    public function update(int $presaleProductId, int $quantity): void
    {
        $cart = session(self::SESSION_KEY, []);

        if (! isset($cart[$presaleProductId])) {
            throw new \RuntimeException('محصول در سبد پیش‌فروش یافت نشد.');
        }

        if ($quantity <= 0) {
            unset($cart[$presaleProductId]);
        } else {
            $cart[$presaleProductId]['quantity'] = $quantity;
        }

        session([self::SESSION_KEY => $cart]);
    }

    /**
     * حذف یک آیتم
     */
    public function remove(int $presaleProductId): void
    {
        $cart = session(self::SESSION_KEY, []);
        unset($cart[$presaleProductId]);
        session([self::SESSION_KEY => $cart]);
    }

    /**
     * خالی کردن سبد
     */
    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * بازیابی سبد از سفارش (مثلاً بعد از پرداخت ناموفق)
     */
    public function restoreFromOrder(\App\Models\PresaleOrder $order): void
    {
        $order->loadMissing('items');

        $cart = [];
        foreach ($order->items as $item) {
            if (! $item->presale_product_id) {
                continue;
            }

            $cart[$item->presale_product_id] = [
                'presale_product_id' => $item->presale_product_id,
                'quantity'           => (int) $item->quantity,
            ];
        }

        session([self::SESSION_KEY => $cart]);
    }
}