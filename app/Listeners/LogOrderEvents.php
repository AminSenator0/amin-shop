<?php

namespace App\Listeners;

use App\Enums\LogAction;
use App\Enums\LogSeverity;
use App\Services\AuditLogService;

class LogOrderEvents
{
    public function handleOrderCreated($event): void
    {
        $order = $event->order ?? $event;

        AuditLogService::log(
            LogAction::ORDER_CREATED,
            $order->user_id,
            ['order_code' => $order->code, 'total' => $order->total],
            [],
            [],
            "سفارش #{$order->code} ایجاد شد",
            'App\\Models\\Order',
            $order->id
        );
    }

    public function handleOrderPaid($event): void
    {
        $order = $event->order ?? $event;

        AuditLogService::log(
            LogAction::ORDER_PAID,
            $order->user_id,
            ['order_code' => $order->code, 'amount' => $order->paid_amount],
            ['status' => 'pending'],
            ['status' => 'paid'],
            "پرداخت سفارش #{$order->code}",
            'App\\Models\\Order',
            $order->id
        );
    }

    public function handleOrderCancelled($event): void
    {
        $order = $event->order ?? $event;

        AuditLogService::log(
            LogAction::ORDER_CANCELLED_USER,
            $order->user_id,
            ['order_code' => $order->code, 'reason' => $event->reason ?? null],
            ['status' => $order->getOriginal('status') ?? 'pending'],
            ['status' => 'cancelled'],
            "لغو سفارش #{$order->code}",
            'App\\Models\\Order',
            $order->id
        );
    }
}