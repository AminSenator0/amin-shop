<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Enums\ReturnStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreReturnRequest;
use App\Http\Requests\Admin\UpdateReturnStatusRequest;
use App\Models\Order;
use App\Models\OrderReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Enums\WalletReferenceType;
use App\Services\WalletService;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderReturn::with('order.user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', '%'.$search.'%')
                    ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', '%'.$search.'%'))
                    ->orWhereHas('order.user', fn ($uq) => $uq->where('name', 'like', '%'.$search.'%'));
            });
        }

        $returns = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => OrderReturn::count(),
            'pending' => OrderReturn::where('status', ReturnStatus::Pending)->count(),
            'approved' => OrderReturn::where('status', ReturnStatus::Approved)->count(),
            'refunded' => OrderReturn::where('status', ReturnStatus::Refunded)->count(),
        ];

        return view('admin.returns.index', compact('returns', 'stats'));
    }

    public function store(StoreReturnRequest $request, Order $order)
    {
        if ($order->returns()->whereIn('status', [ReturnStatus::Pending, ReturnStatus::Approved])->exists()) {
            return back()->with('error', 'این سفارش درخواست مرجوعی باز دارد.');
        }

        $data = $request->validated();

        OrderReturn::create([
            'order_id' => $order->id,
            'reason' => $data['reason'],
            'refund_amount' => $data['refund_amount'] ?? $order->total,
            'status' => ReturnStatus::Pending,
        ]);

        return back()->with('success', 'درخواست مرجوعی ثبت شد.');
    }

    public function updateStatus(UpdateReturnStatusRequest $request, OrderReturn $orderReturn)
    {
        $validated = $request->validated();
        $status = ReturnStatus::from($validated['status']);
        $updates = [
            'status' => $status,
            'admin_note' => $validated['admin_note'] ?? null,
        ];

        if (in_array($status, [ReturnStatus::Approved, ReturnStatus::Rejected, ReturnStatus::Refunded])) {
            $updates['processed_at'] = now();
        }

        DB::transaction(function () use ($orderReturn, $updates, $status) {
            $locked = OrderReturn::lockForUpdate()->findOrFail($orderReturn->id);

            // آیا قبلاً بازپرداخت شده؟ (جلوگیری از دوبار واریز)
            $alreadyRefunded = $locked->status === ReturnStatus::Refunded;

            $locked->update($updates);

            if ($status === ReturnStatus::Refunded && ! $alreadyRefunded) {
                $locked->order->update(['payment_status' => PaymentStatus::Refunded]);

                // ⬇️⬇️⬇️ بازگشت وجه به کیف پول مشتری ⬇️⬇️⬇️
                $wallet = app(\App\Services\WalletService::class)
                    ->getOrCreateForUser($locked->order->user);

                app(\App\Services\WalletService::class)->credit(
                    $wallet,
                    (int) $locked->refund_amount,
                    \App\Enums\WalletReferenceType::OrderReturnRefund,
                    $locked->id,
                    'بازگشت وجه مرجوعی سفارش '.$locked->order->order_number,
                    auth()->user() // ادمین انجام‌دهنده در دفتر کل ثبت می‌شود
                );
                // ⬆️⬆️⬆️ پایان بازگشت وجه ⬆️⬆️⬆️
            }
        });

        return back()->with('success', 'وضعیت مرجوعی به‌روزرسانی شد.');
    }
}
