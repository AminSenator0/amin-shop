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
echo "payment_status: " . (is_string($order->payment_status) ? $order->payment_status : $order->payment_status->value) . "\n";
echo "presale_status: " . (is_string($order->presale_status) ? $order->presale_status : $order->presale_status->value) . "\n";
echo "presale_payment_deadline_at: " . ($order->presale_payment_deadline_at ?? 'NULL') . "\n";
echo "created_at: {$order->created_at}\n";