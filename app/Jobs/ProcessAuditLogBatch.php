<?php

namespace App\Jobs;

use App\Services\AuditLogService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAuditLogBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $batch;
    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(array $batch)
    {
        $this->batch = $batch;
    }

    public function handle(): void
    {
        AuditLogService::insertBatch($this->batch);
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('AuditLog batch job failed: ' . $exception->getMessage(), [
            'batch_count' => count($this->batch),
        ]);
    }
}