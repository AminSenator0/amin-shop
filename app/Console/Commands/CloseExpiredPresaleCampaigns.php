<?php

namespace App\Console\Commands;

use App\Models\PresaleCampaign;
use Illuminate\Console\Command;

class CloseExpiredPresaleCampaigns extends Command
{
    protected $signature = 'presale:close-expired';
    protected $description = 'بستن کمپین‌های پیش‌فروش منقضی شده';

    public function handle(): void
    {
        $count = PresaleCampaign::where('status', 'active')
            ->where('ends_at', '<', now())
            ->whereNull('closed_at')
            ->update([
                'status'    => 'closed',
                'closed_at' => now(),
            ]);

        if ($count > 0) {
            $this->info("{$count} کمپین پیش‌فروش بسته شد.");
        } else {
            $this->info('هیچ کمپین منقضی شده‌ای یافت نشد.');
        }
    }
}