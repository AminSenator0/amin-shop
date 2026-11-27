<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$pp = \App\Models\PresaleProduct::find(1);
$service = app(\App\Services\PresalePricingService::class);

try {
    $service->setProductPricing($pp, 10000000, 100000, 1);
    echo "SUCCESS\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

$item = \App\Models\OrderItem::find(117);
echo "Item presale_final_price: " . ($item->presale_final_price ?? 'NULL') . "\n";

$order = \App\Models\Order::find(108);
echo "Order presale_final_total: " . ($order->presale_final_total ?? 'NULL') . "\n";
echo "Order presale_status: " . ($order->presale_status ?? 'NULL') . "\n";