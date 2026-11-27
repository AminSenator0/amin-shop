<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$pp = \App\Models\PresalePayment::where('order_id', 129)->where('method', 'c2c')->get();
echo 'Count: ' . $pp->count() . PHP_EOL;
foreach ($pp as $p) {
    echo 'ID=' . $p->id . ' amount=' . $p->amount . ' status=' . $p->status . PHP_EOL;
}