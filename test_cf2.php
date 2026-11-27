<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$pp = \App\Models\PresaleProduct::find(11);
$order = \App\Models\Order::where('order_type', 'presale')->latest()->first();

if (!$order || !$pp) {
    echo "NO DATA\n";
    exit;
}

$item = \App\Models\OrderItem::create([
    'order_id' => $order->id,
    'product_id' => $pp->product_id,
    'product_name' => 'TEST',
    'product_sku' => 'TEST',
    'price' => 100,
    'quantity' => 1,
    'total' => 100,
    'custom_fields' => $pp->custom_fields,
    'presale_product_id' => $pp->id,
]);

echo "Created Item ID: {$item->id}\n";
echo "custom_fields: " . json_encode($item->custom_fields) . "\n";