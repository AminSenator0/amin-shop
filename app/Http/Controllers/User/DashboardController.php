<?php

namespace App\Http\Controllers\User;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Services\ContactMessageService;
use App\Support\ShoppingFlow;

class DashboardController extends Controller
{
    public function __construct(
        private ContactMessageService $messages,
    ) {}

    public function index()
    {
        ShoppingFlow::deactivate();

        $user = auth()->user();

        $unreadMessageCount = $this->messages->unreadCountForUser($user);
        $pendingReviewCount = $user->productsAwaitingReview()->count();
        $openReturnsCount = $user->openReturnsCount();

        $pendingPaymentOrders = $user->orders()
            ->where('payment_status', PaymentStatus::Pending)
            ->where('status', '!=', OrderStatus::Cancelled)
            ->latest()
            ->take(3)
            ->get();

        $activeOrders = $user->orders()
            ->whereIn('status', [OrderStatus::Paid, OrderStatus::Processing, OrderStatus::Shipped])
            ->latest()
            ->take(2)
            ->get();

        $actionOrders = $pendingPaymentOrders
            ->concat($activeOrders)
            ->unique('id')
            ->take(3);

        $unreadMessages = $unreadMessageCount > 0
            ? $this->messages->queryForUser($user)
                ->where('has_unread_reply_for_user', true)
                ->latest('last_replied_at')
                ->latest()
                ->take(2)
                ->get()
            : collect();

        $hasActions = $pendingPaymentOrders->isNotEmpty()
            || $unreadMessageCount > 0
            || $pendingReviewCount > 0
            || $openReturnsCount > 0
            || $actionOrders->isNotEmpty();

        return view('user.dashboard', compact(
            'unreadMessageCount',
            'pendingReviewCount',
            'openReturnsCount',
            'pendingPaymentOrders',
            'actionOrders',
            'unreadMessages',
            'hasActions',
        ));
    }
}
