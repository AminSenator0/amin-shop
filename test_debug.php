<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$pp = \App\Models\PresaleProduct::find(1);
$order = \App\Models\Order::find(108);

echo "BEFORE:\n";
echo "  order presale_status: " . ($order->presale_status ?? 'NULL') . "\n";
echo "  order presale_final_total: " . ($order->presale_final_total ?? 'NULL') . "\n";

$item = \App\Models\OrderItem::find(117);
echo "  item presale_final_price: " . ($item->presale_final_price ?? 'NULL') . "\n";

echo "\nCampaign: status=" . $pp->campaign->status . ", id=" . $pp->campaign->id . "\n";
echo "Order campaign_id: " . $order->presale_campaign_id . "\n";

// آپدیت دستی تست
$item->update(['presale_final_price' => 999]);
echo "\nAFTER MANUAL: " . $item->fresh()->presale_final_price . "\n";

// ریست کن
$item->update(['presale_final_price' => null]);
echo "RESET: " . ($item->fresh()->presale_final_price ?? 'NULL') . "\n";