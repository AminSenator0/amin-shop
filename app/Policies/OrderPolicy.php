<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin() || $order->user_id === $user->id;
    }

    public function cancel(User $user, Order $order): bool
    {
        return $order->user_id === $user->id && $order->canBeCancelledByUser();
    }

    public function adminCancel(User $user, Order $order): bool
    {
        return $user->isAdmin() && $order->canBeCancelledByAdmin();
    }

    public function invoice(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $order->user_id === $user->id
            && $order->payment_status === PaymentStatus::Paid;
    }

    public function reorder(User $user, Order $order): bool
    {
        return $order->user_id === $user->id
            && $order->status !== OrderStatus::Cancelled;
    }

    public function return(User $user, Order $order): bool
    {
        return $order->user_id === $user->id && $order->canBeReturnedByUser();
    }

    public function manage(User $user): bool
    {
        return $user->isAdmin();
    }
}
