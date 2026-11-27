<?php

namespace App\Http\Controllers\User;

use App\Enums\PresaleStatus;
use App\Http\Controllers\Controller;
use App\Models\PresaleOrder;
use Illuminate\Http\Request;

class PresaleOrderController extends Controller
{
    public function index()
    {
        $orders = auth()->user()
            ->presaleOrders()
            ->with(['campaign', 'items'])
            ->latest()
            ->paginate(10);

        return view('user.presale.orders.index', compact('orders'));
    }

    public function show(PresaleOrder $presaleOrder)
    {
        if ($presaleOrder->user_id !== auth()->id()) {
            abort(403);
        }

        $presaleOrder->load(['items.presaleProduct.product', 'payments', 'events.actor']);

        return view('user.presale.orders.show', compact('presaleOrder'));
    }

    /**
     * صفحه واریز مرحله‌ای (قسط).
     */
    public function pay(PresaleOrder $presaleOrder)
    {
        if ($presaleOrder->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $presaleOrder->hasFinalPrice()) {
            return redirect()->route('user.presale-orders.show', $presaleOrder)
                ->with('error', 'قیمت نهایی هنوز مشخص نشده است.');
        }

        if (! $presaleOrder->status->isOpen()) {
            return redirect()->route('user.presale-orders.show', $presaleOrder)
                ->with('error', 'این سفارش بسته شده است.');
        }

        if ($presaleOrder->remaining() <= 0) {
            return redirect()->route('user.presale-orders.show', $presaleOrder)
                ->with('success', 'تسویه قبلاً انجام شده است.');
        }

        $wallet = auth()->user()->wallet;

        return view('user.presale.orders.pay', compact('presaleOrder', 'wallet'));
    }
}