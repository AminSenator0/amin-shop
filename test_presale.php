<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$o = \App\Models\Order::where('order_type','presale')->latest()->first();
if (!$o) {
    echo "NO PRESALE ORDER FOUND\n";
    exit;
}

echo "Order ID: {$o->id}\n";
echo "presale_status: " . ($o->presale_status ?? 'NULL') . "\n";
echo "presale_final_total: " . ($o->presale_final_total ?? 'NULL') . "\n";
echo "presale_penalties_total: " . ($o->presale_penalties_total ?? 'NULL') . "\n";
echo "presale_total_paid: " . ($o->presale_total_paid ?? 'NULL') . "\n";

$item = $o->items()->first();
if ($item) {
    echo "Item ID: {$item->id}\n";
    echo "presale_final_price: " . ($item->presale_final_price ?? 'NULL') . "\n";
    echo "presale_penalty: " . ($item->presale_penalty ?? 'NULL') . "\n";
} else {
    echo "NO ITEMS\n";
}