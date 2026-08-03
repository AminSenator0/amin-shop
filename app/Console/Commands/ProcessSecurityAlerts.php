<?php

namespace App\Console\Commands;

use App\Services\SecurityAlertService;
use Illuminate\Console\Command;

class ProcessSecurityAlerts extends Command
{
    protected $signature = 'security:check-alerts';
    protected $description = 'بررسی الگوهای مشکوک و تولید هشدارهای امنیتی';

    public function handle(): int
    {
        $this->info('🔍 Checking batch security patterns...');

        SecurityAlertService::checkBatchPatterns();

        $summary = SecurityAlertService::getDashboardSummary();
        
        $this->info("✅ Done!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Unresolved Alerts', $summary['total_unresolved']],
                ['Critical', $summary['critical']],
                ['High', $summary['high']],
                ['Warning', $summary['warning']],
                ['Today', $summary['today']],
            ]
        );

        return self::SUCCESS;
    }
}