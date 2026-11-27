<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$order = \App\Models\Order::where('order_type', 'presale')->latest()->first();
if (!$order) {
    echo "NO PRESALE ORDER\n";
    exit;
}

echo "Order ID: {$order->id}\n";
echo "subtotal: {$order->subtotal}\n";
echo "shipping_cost: " . ($order->shipping_cost ?? 'NULL') . "\n";
echo "discount_amount: " . ($order->discount_amount ?? 'NULL') . "\n";
echo "total: {$order->total}\n";
echo "payable: {$order->payable}\n";