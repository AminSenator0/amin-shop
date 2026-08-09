<?php
// app/Console/Commands/GenerateSellerApiKey.php

namespace App\Console\Commands;

use App\Models\SellerDevice;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateSellerApiKey extends Command
{
    protected $signature = 'seller:generate-key {--name=MainDevice}';
    protected $description = 'Generate API key for seller device';

    public function handle(): void
    {
        $key = 'ask_' . Str::random(60); // ask = AminShop Key

        $device = SellerDevice::create([
            'device_name' => $this->option('name'),
            'fcm_token' => 'pending', // دیگه استفاده نمی‌شه ولی not-null هست
            'api_key' => $key,
            'is_active' => true,
        ]);

        $this->info('API Key generated successfully!');
        $this->newLine();
        $this->line('<fg=yellow>' . $key . '</fg=yellow>');
        $this->newLine();
        $this->line('Copy this key into your Android app.');
    }
}