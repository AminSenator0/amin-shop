<?php

namespace App\Listeners;

use App\Enums\LogAction;
use App\Enums\LogSeverity;
use App\Services\AuditLogService;

class LogPaymentEvents
{
    public function handlePaymentStarted($event): void
    {
        AuditLogService::log(
            LogAction::PAYMENT_STARTED,
            $event->order->user_id ?? null,
            [
                'order_id' => $event->order->id,
                'amount' => $event->amount,
                'gateway' => $event->gateway ?? 'zarinpal',
            ],
            severity: LogSeverity::INFO
        );
    }

    public function handlePaymentSuccess($event): void
    {
        AuditLogService::log(
            LogAction::PAYMENT_SUCCESS,
            $event->order->user_id ?? null,
            [
                'order_id' => $event->order->id,
                'transaction_id' => $event->transaction_id,
                'amount' => $event->amount,
            ],
            [],
            [],
            "پرداخت موفق — تراکنش: {$event->transaction_id}",
            'App\\Models\\Order',
            $event->order->id
        );
    }

    public function handlePaymentFailed($event): void
    {
        AuditLogService::log(
            LogAction::PAYMENT_FAILED,
            $event->order->user_id ?? null,
            [
                'order_id' => $event->order->id ?? null,
                'error' => $event->error ?? 'Unknown error',
                'gateway' => $event->gateway ?? 'zarinpal',
            ],
            severity: LogSeverity::WARNING
        );
    }

    public function handlePaymentCallback($event): void
    {
        AuditLogService::log(
            LogAction::PAYMENT_CALLBACK,
            $event->order->user_id ?? null,
            [
                'order_id' => $event->order->id,
                'authority' => $event->authority,
                'status' => $event->status,
            ],
            severity: LogSeverity::INFO
        );
    }
}