<?php

namespace App\Console\Commands;

use App\Services\AuditLogService;
use Illuminate\Console\Command;

class PurgeAuditLogs extends Command
{
    protected $signature = 'audit-log:purge {--days=90 : تعداد روز نگهداری لاگ‌ها}';
    protected $description = 'پاکسازی لاگ‌های قدیمی برای بهینه‌سازی دیتابیس';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        
        $this->info("🧹 Purging audit logs older than {$days} days...");
        
        $deleted = AuditLogService::purgeOldLogs($days);
        
        $this->info("✅ {$deleted} records deleted.");
        
        return self::SUCCESS;
    }
}