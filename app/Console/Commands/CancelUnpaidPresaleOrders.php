<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CancelUnpaidPresaleOrders extends Command
{
    protected $signature = 'presale:cancel-unpaid';
    protected $description = 'لغو سفارش‌های پیش‌فروش پرداخت‌نشده';

    public function handle(): int
    {
        $count = 0;

        Order::query()
            ->where('order_type', 'presale')
            ->where('payment_status', PaymentStatus::Pending)
            ->where('status', OrderStatus::Pending)
            ->whereNotNull('presale_payment_deadline_at')
            ->where('presale_payment_deadline_at', '<', now())
            ->chunkById(50, function ($orders) use (&$count) {
                foreach ($orders as $order) {
                    try {
                        DB::transaction(function () use ($order) {
                            $order->update([
                                'status'         => OrderStatus::Cancelled,
                                'payment_status' => PaymentStatus::Failed,
                                'presale_status' => \App\Enums\PresaleStatus::Cancelled->value,
                                'cancelled_at'   => now(),
                            ]);
                        });
                        $count++;
                    } catch (\Throwable) {
                        // skip
                    }
                }
            });

        $this->info("{$count} سفارش پیش‌فروش پرداخت‌نشده لغو شد.");
        return self::SUCCESS;
    }
}