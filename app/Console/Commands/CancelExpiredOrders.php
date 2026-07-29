<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Command;

class CancelExpiredOrders extends Command
{
    protected $signature = 'orders:cancel-expired';

    protected $description = 'لغو سفارش‌های پرداخت‌نشده منقضی‌شده و بازگردانی موجودی';

    public function handle(OrderService $orders): int
    {
        $count = $orders->cancelExpiredPendingOrders();

        $this->info("{$count} سفارش منقضی لغو شد.");

        return self::SUCCESS;
    }
}
