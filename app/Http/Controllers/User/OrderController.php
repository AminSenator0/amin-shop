<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orders) {}

    public function index(Request $request)
    {
        $query = auth()->user()->orders()->with('items')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('tracking_code', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('user.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        $order->load(['items.product', 'shippingMethod', 'returns.items.orderItem']);

        return view('user.orders.show', compact('order'));
    }

    public function cancel(Order $order)
    {
        $this->authorize('cancel', $order);

        try {
            $this->orders->cancelByUser($order);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('user.orders.index')
            ->with('success', 'سفارش با موفقیت لغو شد.');
    }

    public function reorder(Order $order, CartService $cart)
    {
        $this->authorize('reorder', $order);

        $result = $this->orders->reorder($order, $cart);

        if ($result['added'] === 0) {
            return back()->with('error', 'هیچ محصولی به سبد خرید اضافه نشد. ممکن است موجود نباشند.');
        }

        $message = 'محصولات به سبد خرید اضافه شدند.';

        if (! empty($result['skipped'])) {
            $message .= ' برخی محصولات به‌دلیل عدم موجودی اضافه نشدند: '.implode('، ', $result['skipped']);
        }

        return redirect()->route('user.cart.index')->with('success', $message);
    }
}
