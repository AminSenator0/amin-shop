<?php

namespace App\Observers;

use App\Enums\LogAction;
use App\Enums\LogSeverity;
use App\Models\Product;
use App\Services\AuditLogService;

class ProductObserver
{
    public function created(Product $product): void
    {
        AuditLogService::log(
            LogAction::PRODUCT_CREATED,
            auth()->id(),
            ['name' => $product->name, 'price' => $product->price],
            [],
            $product->toArray(),
            "محصول {$product->name} ایجاد شد",
            'App\\Models\\Product',
            $product->id
        );
    }

    public function updated(Product $product): void
    {
        $changes = $product->getChanges();
        $original = $product->getOriginal();

        if (empty($changes)) return;

        $action = LogAction::PRODUCT_UPDATED;
        $description = "ویرایش محصول {$product->name}";

        if (isset($changes['price'])) {
            $action = LogAction::PRICE_CHANGED;
            $description = "تغییر قیمت {$product->name}: {$original['price']} → {$changes['price']}";
        }

        if (isset($changes['stock'])) {
            $action = LogAction::STOCK_CHANGED;
            $description = "تغییر موجودی {$product->name}: {$original['stock']} → {$changes['stock']}";
        }

        if (isset($changes['discount_price']) || isset($changes['discount_percent'])) {
            $action = LogAction::DISCOUNT_CHANGED;
        }

        AuditLogService::log(
            $action,
            auth()->id(),
            ['name' => $product->name],
            array_intersect_key($original, $changes),
            $changes,
            $description,
            'App\\Models\\Product',
            $product->id,
            severity: $action === LogAction::PRICE_CHANGED ? LogSeverity::WARNING : LogSeverity::INFO
        );
    }

    public function deleted(Product $product): void
    {
        AuditLogService::log(
            LogAction::PRODUCT_DELETED,
            auth()->id(),
            ['name' => $product->name],
            $product->toArray(),
            [],
            "حذف محصول {$product->name}",
            'App\\Models\\Product',
            $product->id
        );
    }
}