<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// آخرین OrderItem از presale
$item = \App\Models\OrderItem::whereHas('order', fn($q) => $q->where('order_type', 'presale'))->latest()->first();

if (!$item) {
    echo "NO PRESALE ORDER ITEM FOUND\n";
    exit;
}

echo "OrderItem ID: {$item->id}\n";
echo "Product: {$item->product_name}\n";
echo "custom_fields RAW: " . ($item->getAttributes()['custom_fields'] ?? 'NULL') . "\n";
echo "custom_fields CAST: " . json_encode($item->custom_fields) . "\n";
echo "PresaleProduct ID: {$item->presale_product_id}\n";

$pp = \App\Models\PresaleProduct::find($item->presale_product_id);
if ($pp) {
    echo "PP custom_fields: " . json_encode($pp->custom_fields) . "\n";
} else {
    echo "PP NOT FOUND\n";
}