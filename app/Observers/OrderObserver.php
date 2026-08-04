<?php

namespace App\Observers;

use App\Enums\LogAction;
use App\Enums\LogSeverity;
use App\Models\Order;
use App\Services\AuditLogService;

class OrderObserver
{
    public function created(Order $order): void
    {
        AuditLogService::log(
            LogAction::ORDER_CREATED,
            $order->user_id,
            ['order_code' => $order->code ?? $order->id, 'total' => $order->total],
            [],
            [],
            "سفارش #{$order->code} ایجاد شد",
            'App\\Models\\Order',
            $order->id
        );
    }

    public function updated(Order $order): void
    {
        $changes = $order->getChanges();
        if (empty($changes)) return;

        $original = $order->getOriginal();

        // ═══════════════════════════════════════════════
        // Status Changed
        // ═══════════════════════════════════════════════
        if (isset($changes['status'])) {
            $oldStatus = $original['status'] ?? 'unknown';
            $newStatus = $changes['status'];

            // تبدیل enum به string
            $oldStatusStr = is_object($oldStatus) && enum_exists(get_class($oldStatus)) ? $oldStatus->value : $oldStatus;
            $newStatusStr = is_object($newStatus) && enum_exists(get_class($newStatus)) ? $newStatus->value : $newStatus;

            // Order Paid
            if ($newStatusStr === 'paid' || $newStatusStr === 'completed') {
                AuditLogService::log(
                    LogAction::ORDER_PAID,
                    $order->user_id,
                    ['order_code' => $order->code, 'amount' => $order->paid_amount ?? $order->total],
                    ['status' => $oldStatusStr],
                    ['status' => $newStatusStr],
                    "پرداخت سفارش #{$order->code}",
                    'App\\Models\\Order',
                    $order->id
                );
                return;
            }

            // Order Cancelled (by admin/system)
            if ($newStatusStr === 'cancelled' || $newStatusStr === 'canceled') {
                AuditLogService::log(
                    LogAction::ORDER_CANCELLED,
                    auth()->id(),
                    ['order_code' => $order->code, 'reason' => 'Admin/System cancel'],
                    ['status' => $oldStatusStr],
                    ['status' => $newStatusStr],
                    "لغو سفارش #{$order->code}",
                    'App\\Models\\Order',
                    $order->id,
                    severity: LogSeverity::HIGH
                );
                return;
            }

            // Generic status change
            AuditLogService::log(
                LogAction::ORDER_STATUS_CHANGED_AUTO,
                auth()->id(),
                ['order_code' => $order->code],
                ['status' => $oldStatusStr],
                ['status' => $newStatusStr],
                "تغییر وضعیت سفارش #{$order->code}: {$oldStatusStr} → {$newStatusStr}",
                'App\\Models\\Order',
                $order->id
            );
        }

        // ═══════════════════════════════════════════════
        // Payment Status Changed (manual)
        // ═══════════════════════════════════════════════
        if (isset($changes['payment_status'])) {
            $oldPayStatus = $original['payment_status'] ?? null;
            $newPayStatus = $changes['payment_status'];
            
            $oldPayStr = is_object($oldPayStatus) && enum_exists(get_class($oldPayStatus)) ? $oldPayStatus->value : $oldPayStatus;
            $newPayStr = is_object($newPayStatus) && enum_exists(get_class($newPayStatus)) ? $newPayStatus->value : $newPayStatus;

            AuditLogService::log(
                LogAction::PAYMENT_STATUS_MANUAL,
                auth()->id(),
                ['order_code' => $order->code],
                ['payment_status' => $oldPayStr],
                ['payment_status' => $newPayStr],
                "تغییر دستی وضعیت پرداخت سفارش #{$order->code}",
                'App\\Models\\Order',
                $order->id,
                severity: LogSeverity::HIGH
            );
        }

        // ═══════════════════════════════════════════════
        // Paid Amount Changed (tampering detection)
        // ═══════════════════════════════════════════════
        if (isset($changes['paid_amount']) && isset($original['paid_amount'])) {
            if ($changes['paid_amount'] != $original['total']) {
                AuditLogService::log(
                    LogAction::ORDER_AMOUNT_TAMPERED,
                    auth()->id(),
                    [
                        'order_code' => $order->code,
                        'expected' => $original['total'],
                        'actual' => $changes['paid_amount'],
                    ],
                    ['paid_amount' => $original['paid_amount']],
                    ['paid_amount' => $changes['paid_amount']],
                    "تغییر مشکوک مبلغ سفارش #{$order->code}",
                    'App\\Models\\Order',
                    $order->id,
                    severity: LogSeverity::CRITICAL
                );
            }
        }
    }

    public function deleted(Order $order): void
    {
        AuditLogService::log(
            LogAction::ORDER_CANCELLED,
            auth()->id(),
            ['order_code' => $order->code],
            $order->toArray(),
            [],
            "حذف سفارش #{$order->code}",
            'App\\Models\\Order',
            $order->id,
            severity: LogSeverity::HIGH
        );
    }
}