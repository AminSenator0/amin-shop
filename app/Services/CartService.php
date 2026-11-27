<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\PresaleProduct;
use App\Models\Product;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function add(int $productId, int $quantity = 1, ?string $selectedSize = null, ?string $selectedColor = null, array $customFields = []): void
    {
        $product = Product::with(['category', 'brand', 'images'])->findOrFail($productId);

        if (!$product->is_active) {
            throw new \RuntimeException('این محصول در حال حاضر غیرفعال است.');
        }

        if (!$product->isInStock($quantity)) {
            throw new \RuntimeException('موجودی این محصول کافی نیست.');
        }

        $cart = Session::get('cart', []);

        // جلوگیری از اختلاط
        if ($this->hasPresaleItems($cart)) {
            throw new \RuntimeException('سبد خرید شما حاوی محصولات پیش‌فروش است. لطفاً ابتدا آن‌ها را تکمیل یا حذف کنید.');
        }

        $price = $product->finalPrice();

        $lineKey = $this->buildLineKey($productId, $selectedSize, $selectedColor, $customFields);

        if (isset($cart[$lineKey])) {
            $newQty = $cart[$lineKey]['quantity'] + $quantity;
            if (!$product->isInStock($newQty)) {
                throw new \Exception('موجودی این محصول کافی نیست.');
            }
            $cart[$lineKey]['quantity'] = $newQty;
        } else {
            $cart[$lineKey] = [
                'product_id'        => $productId,
                'quantity'          => $quantity,
                'price'             => $price,
                'selected_size'     => $selectedSize,
                'selected_color'    => $selectedColor,
                'custom_fields'     => $customFields,
                'sale_type'         => 'normal',
                'presale_product_id'=> null,
            ];
        }

        Session::put('cart', $cart);
    }

 

    public function addPresale(int $presaleProductId, int $quantity = 1): void
    {
        $presaleProduct = PresaleProduct::with(['product', 'campaign'])->findOrFail($presaleProductId);

        if (!$presaleProduct->is_active || !$presaleProduct->campaign->isOpenNow()) {
            throw new \RuntimeException('این محصول در حال حاضر قابل پیش‌فروش نیست.');
        }

        $cart = Session::get('cart', []);

        // جلوگیری از اختلاط
        if ($this->hasNormalItems($cart)) {
            throw new \RuntimeException('سبد خرید شما حاوی محصولات عادی است. لطفاً ابتدا آن‌ها را تکمیل یا حذف کنید.');
        }

        $lineKey = 'presale_' . $presaleProductId;

        if (isset($cart[$lineKey])) {
            $cart[$lineKey]['quantity'] += $quantity;
        } else {
            $cart[$lineKey] = [
                'product_id'         => $presaleProduct->product_id,
                'quantity'           => $quantity,
                'price'              => $presaleProduct->presale_price,
                'selected_size'      => null,
                'selected_color'     => null,
                'custom_fields'      => [],
                'sale_type'          => 'presale',
                'presale_product_id' => $presaleProduct->id,
            ];
        }

        Session::put('cart', $cart);
    }

    public function update(string $lineKey, int $quantity): void
    {
        $cart = Session::get('cart', []);
        if (!isset($cart[$lineKey])) {
            return;
        }

        $item = $cart[$lineKey];

        if ($quantity < 1) {
            unset($cart[$lineKey]);
            Session::put('cart', $cart);
            return;
        }

        // فقط برای عادی چک موجودی
        if (($item['sale_type'] ?? 'normal') === 'normal') {
            $product = Product::find($item['product_id']);
            if (!$product || !$product->isInStock($quantity)) {
                throw new \RuntimeException('موجودی این محصول کافی نیست.');
            }
        }

        $cart[$lineKey]['quantity'] = $quantity;
        Session::put('cart', $cart);
    }

    public function remove(string $lineKey): void
    {
        $cart = Session::get('cart', []);
        unset($cart[$lineKey]);
        Session::put('cart', $cart);
    }

    public function items(): \Illuminate\Support\Collection
    {
        $cart = Session::get('cart', []);
        $items = collect();

        foreach ($cart as $lineKey => $item) {
            $saleType = $item['sale_type'] ?? 'normal';

             if ($saleType === 'presale') {
                $presaleProduct = PresaleProduct::with(['product.images', 'product.brand', 'product.category'])->find($item['presale_product_id'] ?? null);
                if (!$presaleProduct) {
                    continue;
                }
                $product = $presaleProduct->product;

                $items->push([
                    'line_key'           => $lineKey,
                    'product'            => $product,
                    'quantity'           => $item['quantity'],
                    'price'              => $item['price'],
                    'total'              => $item['price'] * $item['quantity'],
                    'selected_size'      => null,
                    'selected_color'     => null,
                    'custom_fields'      => $presaleProduct->custom_fields ?? [],
                    'sale_type'          => 'presale',
                    'presale_product'    => $presaleProduct,
                    'presale_product_id' => $presaleProduct->id,
                ]);
            } else {
                $product = Product::with(['images', 'brand', 'category'])->find($item['product_id']);
                if (!$product) {
                    continue;
                }

                $items->push([
                    'line_key'        => $lineKey,
                    'product'         => $product,
                    'quantity'        => $item['quantity'],
                    'price'           => $item['price'],
                    'total'           => $item['price'] * $item['quantity'],
                    'selected_size'   => $item['selected_size'],
                    'selected_color'  => $item['selected_color'],
                    'custom_fields'   => $item['custom_fields'],
                    'sale_type'       => 'normal',
                    'presale_product' => null,
                    'presale_product_id' => null,
                ]);
            }
        }

        return $items;
    }

    public function count(): int
    {
        $cart = Session::get('cart', []);
        return collect($cart)->sum('quantity');
    }

    public function subtotal(): int
    {
        $cart = Session::get('cart', []);
        return collect($cart)->sum(function ($item) {
            return ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
        });
    }

    public function total(): int
    {
        return $this->subtotal();
    }

    public function clear(): void
    {
        Session::forget('cart');
    }

    public function isEmpty(): bool
    {
        return empty(Session::get('cart'));
    }

    public function hasNormalItems(?array $cart = null): bool
    {
        $cart = $cart ?? Session::get('cart', []);
        return collect($cart)->contains(fn ($item) => ($item['sale_type'] ?? 'normal') === 'normal');
    }

    public function hasPresaleItems(?array $cart = null): bool
    {
        $cart = $cart ?? Session::get('cart', []);
        return collect($cart)->contains(fn ($item) => ($item['sale_type'] ?? 'normal') === 'presale');
    }
    public function quantityFor(int $productId): int
    {
        $cart = Session::get('cart', []);
        return collect($cart)
            ->where('product_id', $productId)
            ->sum('quantity');
    }

    public function getShippingMethods(): \Illuminate\Support\Collection
    {
        $items = $this->items();
        $totalWeight = $items->sum(function ($item) {
            return ($item['product']->weight ?? 0) * $item['quantity'];
        });

        return ShippingMethod::where('is_active', true)
            ->where('min_order', '<=', $this->total())
            ->where(function ($q) use ($totalWeight) {
                $q->whereNull('max_weight')->orWhere('max_weight', '>=', $totalWeight);
            })
            ->orderBy('sort_order')
            ->get();
    }

    public function calculateShipping(): int
    {
        $methods = $this->getShippingMethods();
        return $methods->isNotEmpty() ? $methods->first()->cost : 0;
    }

    public function calculatePayable(?int $shippingCost = null): int
    {
        return $this->total() + ($shippingCost ?? $this->calculateShipping());
    }

    public function calculateTax(): int
    {
        $taxSettings = [
            'enabled' => config('store.tax_enabled', false),
            'rate'    => (float) config('store.tax_rate', 0),
            'label'   => config('store.tax_label', 'مالیات'),
        ];

        if (!$taxSettings['enabled']) {
            return 0;
        }

        return (int) round($this->subtotal() * ($taxSettings['rate'] / 100));
    }

    public function getCouponDiscount(): int
    {
        return Session::get('coupon_discount', 0);
    }

    public function applyCoupon(string $code): array
    {
        $coupon = Coupon::where('code', $code)
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->first();

        if (!$coupon) {
            return ['success' => false, 'message' => 'کد تخفیف نامعتبر است.'];
        }

        $subtotal = $this->subtotal();

        if ($coupon->min_purchase && $subtotal < $coupon->min_purchase) {
            return ['success' => false, 'message' => 'حداقل مبلغ خرید برای این کد تخفیف رعایت نشده است.'];
        }

        if ($coupon->type === 'percentage') {
            $discount = (int) round($subtotal * ($coupon->value / 100));
            if ($coupon->max_discount && $discount > $coupon->max_discount) {
                $discount = $coupon->max_discount;
            }
        } else {
            $discount = $coupon->value;
        }

        Session::put('coupon', [
            'code'     => $coupon->code,
            'discount' => $discount,
            'type'     => $coupon->type,
            'value'    => $coupon->value,
        ]);

        return ['success' => true, 'message' => 'کد تخفیف با موفقیت اعمال شد.', 'discount' => $discount];
    }

    public function removeCoupon(): void
    {
        Session::forget('coupon');
    }

    public function getCoupon(): ?array
    {
        return Session::get('coupon');
    }

    public function restoreOnRetry(int $orderId): void
    {
        $order = \App\Models\Order::with('items.product')->find($orderId);
        if (!$order || $order->payment_status !== \App\Enums\PaymentStatus::Pending->value) {
            return;
        }

        $cart = [];
        foreach ($order->items as $item) {
            $lineKey = $this->buildLineKey(
                $item->product_id,
                $item->selected_size,
                $item->selected_color,
                json_decode($item->custom_fields, true) ?? []
            );

            $cart[$lineKey] = [
                'product_id'      => $item->product_id,
                'quantity'        => $item->quantity,
                'price'           => $item->price,
                'selected_size'   => $item->selected_size,
                'selected_color'  => $item->selected_color,
                'custom_fields'   => json_decode($item->custom_fields, true) ?? [],
                'sale_type'       => $item->presale_product_id ? 'presale' : 'normal',
                'presale_product_id' => $item->presale_product_id,
            ];
        }

        Session::put('cart', $cart);
    }

    private function buildLineKey(int $productId, ?string $size, ?string $color, array $customFields): string
    {
        $parts = [$productId, $size, $color];
        if (!empty($customFields)) {
            $parts[] = md5(json_encode($customFields));
        }
        return implode('|', array_filter($parts, fn ($v) => $v !== null));
    }
}