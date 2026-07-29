<?php

namespace App\Http\Controllers\User;

use App\Enums\ReturnStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreReturnRequest;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\OrderReturnItem;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function index()
    {
        $returns = auth()->user()
            ->orderReturns()
            ->with(['order', 'items.orderItem'])
            ->latest()
            ->paginate(10);

        return view('user.returns.index', compact('returns'));
    }

    public function store(StoreReturnRequest $request, Order $order)
    {
        $this->authorize('return', $order);

        if (! $order->canBeReturnedByUser()) {
            return back()->with('error', 'امکان ثبت درخواست مرجوعی برای این سفارش وجود ندارد.');
        }

        $itemQuantities = collect($request->validated('items', []))
            ->map(fn ($qty) => (int) $qty)
            ->filter(fn ($qty) => $qty > 0);

        $isPartial = $itemQuantities->isNotEmpty();
        $refundAmount = $isPartial
            ? $this->orders->calculatePartialRefundAmount($order, $itemQuantities->all())
            : $order->total;

        DB::transaction(function () use ($request, $order, $refundAmount, $isPartial, $itemQuantities) {
            $return = OrderReturn::create([
                'order_id' => $order->id,
                'reason' => $request->validated('reason'),
                'refund_amount' => $refundAmount,
                'status' => ReturnStatus::Pending,
                'is_partial' => $isPartial,
            ]);

            if ($isPartial) {
                foreach ($itemQuantities as $itemId => $qty) {
                    OrderReturnItem::create([
                        'order_return_id' => $return->id,
                        'order_item_id' => $itemId,
                        'quantity' => $qty,
                    ]);
                }
            }

            $this->orders->logActivity($order, 'return_requested', 'درخواست مرجوعی توسط مشتری ثبت شد.', [
                'return_id' => $return->id,
                'is_partial' => $isPartial,
            ]);
        });

        return back()->with('success', 'درخواست مرجوعی ثبت شد و پس از بررسی اطلاع‌رسانی می‌شود.');
    }
}
