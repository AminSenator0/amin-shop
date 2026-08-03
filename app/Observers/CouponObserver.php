<?php

namespace App\Observers;

use App\Enums\LogAction;
use App\Models\Coupon;
use App\Services\AuditLogService;

class CouponObserver
{
    public function created(Coupon $coupon): void
    {
        AuditLogService::log(
            LogAction::COUPON_CREATED_ADMIN,
            auth()->id(),
            ['code' => $coupon->code, 'discount' => $coupon->discount],
            [],
            $coupon->toArray(),
            "کد تخفیف {$coupon->code} ایجاد شد",
            'App\\Models\\Coupon',
            $coupon->id
        );
    }

    public function updated(Coupon $coupon): void
    {
        $changes = $coupon->getChanges();
        if (empty($changes)) return;

        $action = LogAction::COUPON_CREATED_ADMIN; // default
        $description = "ویرایش کد تخفیف {$coupon->code}";

        if (isset($changes['discount_percent'])) {
            $action = LogAction::COUPON_PERCENT_CHANGED;
            $description = "تغییر درصد تخفیف {$coupon->code}";
        }
        if (isset($changes['discount_amount'])) {
            $action = LogAction::COUPON_AMOUNT_CHANGED;
            $description = "تغییر مبلغ تخفیف {$coupon->code}";
        }
        if (isset($changes['expires_at'])) {
            $action = LogAction::COUPON_EXPIRY_CHANGED;
            $description = "تغییر تاریخ انقضای {$coupon->code}";
        }
        if (isset($changes['usage_limit'])) {
            $action = LogAction::COUPON_LIMIT_CHANGED;
            $description = "تغییر محدودیت استفاده {$coupon->code}";
        }

        AuditLogService::log(
            $action,
            auth()->id(),
            ['code' => $coupon->code],
            array_intersect_key($coupon->getOriginal(), $changes),
            $changes,
            $description,
            'App\\Models\\Coupon',
            $coupon->id
        );
    }

    public function deleted(Coupon $coupon): void
    {
        AuditLogService::log(
            LogAction::COUPON_DELETED_ADMIN,
            auth()->id(),
            ['code' => $coupon->code],
            $coupon->toArray(),
            [],
            "حذف کد تخفیف {$coupon->code}",
            'App\\Models\\Coupon',
            $coupon->id
        );
    }
}