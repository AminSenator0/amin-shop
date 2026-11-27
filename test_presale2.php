<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$item = \App\Models\OrderItem::find(117);
$pp = \App\Models\PresaleProduct::find($item->presale_product_id);

echo "PresaleProduct ID: {$pp->id}\n";
echo "Product name: {$pp->product->name}\n";
echo "PP final_price: " . ($pp->final_price ?? 'NULL') . "\n";
echo "PP penalty_amount: " . ($pp->penalty_amount ?? 'NULL') . "\n";
echo "Campaign status: {$pp->campaign->status}\n";
echo "Campaign closed_at: " . ($pp->campaign->closed_at ?? 'NULL') . "\n";

// ببینیم OrderItem آپدیت شده یا نه
echo "\nOrderItem presale_final_price: " . ($item->presale_final_price ?? 'NULL') . "\n";
echo "OrderItem presale_penalty: " . ($item->presale_penalty ?? 'NULL') . "\n";