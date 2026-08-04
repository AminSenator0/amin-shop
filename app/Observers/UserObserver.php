<?php

namespace App\Observers;

use App\Enums\LogAction;
use App\Enums\LogSeverity;
use App\Models\User;
use App\Services\AuditLogService;

class UserObserver
{
    public function created(User $user): void
    {
        AuditLogService::log(
            LogAction::USER_CREATED,
            auth()->id(),
            ['email' => $user->email, 'name' => $user->name],
            [],
            $user->toArray(),
            "کاربر {$user->name} ایجاد شد",
            'App\\Models\\User',
            $user->id
        );
    }

    public function updated(User $user): void
    {
        $changes = $user->getChanges();
        if (empty($changes)) return;

        $action = LogAction::USER_UPDATED;
        $description = "تغییر اطلاعات کاربر {$user->name}";

        if (isset($changes['email']) || isset($changes['phone'])) {
            $action = LogAction::EMAIL_PHONE_CHANGED;
            $description = "تغییر ایمیل/شماره کاربر {$user->name}";
        }

        if (isset($changes['is_active'])) {
            $action = LogAction::ACCOUNT_TOGGLED;
            $description = "حساب کاربر {$user->name} " . ($changes['is_active'] ? 'فعال' : 'غیرفعال') . " شد";
        }

        if (isset($changes['role']) || isset($changes['permissions'])) {
            $action = LogAction::ROLE_PERMISSION_CHANGED;
            $description = "تغییر Role/Permission کاربر {$user->name}";
        }

        AuditLogService::log(
            $action,
            auth()->id(),
            ['user_email' => $user->email],
            array_intersect_key($user->getOriginal(), $changes),
            $changes,
            $description,
            'App\\Models\\User',
            $user->id,
            severity: in_array($action, [LogAction::ROLE_PERMISSION_CHANGED, LogAction::ACCOUNT_TOGGLED]) 
                ? LogSeverity::HIGH 
                : LogSeverity::INFO
        );
    }

    public function deleted(User $user): void
    {
        AuditLogService::log(
            LogAction::USER_DELETED,
            auth()->id(),
            ['email' => $user->email, 'name' => $user->name],
            $user->toArray(),
            [],
            "حذف کاربر {$user->name}",
            'App\\Models\\User',
            $user->id,
            severity: LogSeverity::HIGH
        );
    }
}