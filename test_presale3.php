<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$campaign = \App\Models\PresaleCampaign::where('status', 'active')
    ->where('ends_at', '>=', now())
    ->first();

if (!$campaign) {
    echo "NO ACTIVE CAMPAIGN\n";
    exit;
}

echo "Campaign ID: {$campaign->id}\n";
echo "Campaign Title: {$campaign->title}\n";

$pps = \App\Models\PresaleProduct::where('campaign_id', $campaign->id)
    ->where('is_active', true)
    ->get();

echo "Direct count: {$pps->count()}\n";

foreach ($pps as $pp) {
    $product = $pp->product;
    echo "  PP ID: {$pp->id} product_id={$pp->product_id} is_active=" . ($pp->is_active ? 'yes' : 'no');
    echo " product_name=" . ($product ? $product->name : 'DELETED') . "\n";
}