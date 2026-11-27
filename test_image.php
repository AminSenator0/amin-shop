<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$pp = \App\Models\PresaleProduct::with('product.images')->first();
if (!$pp) {
    echo "NO PP\n";
    exit;
}

$product = $pp->product;
$image = $product->images->first();
echo "Product: {$product->name}\n";
if ($image) {
    echo "Image path: {$image->path}\n";
    echo "Image url: " . ($image->url ?? 'NO URL') . "\n";
} else {
    echo "NO IMAGES\n";
}