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
           'refund_amount' => $data['refund_amount'] ?? $order->returnablePaidAmount(),
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

        if (in_array($status, [
            ReturnStatus::Approved,
            ReturnStatus::Rejected,
            ReturnStatus::Refunded,
        ], true)) {
            $updates['processed_at'] = now();
        }

        DB::transaction(function () use ($orderReturn, $updates, $status) {
            $locked = OrderReturn::with('order.user')
                ->lockForUpdate()
                ->findOrFail($orderReturn->id);

            // جلوگیری از واریز مجدد
            $alreadyRefunded = $locked->status === ReturnStatus::Refunded;

            $locked->update($updates);

            if ($status === ReturnStatus::Refunded && ! $alreadyRefunded) {
                $grossAmount = (int) $locked->refund_amount;
                $deduction = $locked->deductionAmount();
                $netAmount = $locked->netRefundAmount();

                if ($netAmount <= 0) {
                    throw new \RuntimeException(
                        'مبلغ قابل واریز برای این مرجوعی صفر است.'
                    );
                }

                $locked->order->update([
                    'payment_status' => PaymentStatus::Refunded,
                ]);

                $walletService = app(WalletService::class);
                $wallet = $walletService->getOrCreateForUser(
                    $locked->order->user
                );

                $walletService->credit(
                    $wallet,
                    $netAmount,
                    WalletReferenceType::OrderReturnRefund,
                    $locked->id,
                    "بازگشت وجه مرجوعی سفارش {$locked->order->order_number}"
                    . " — مبلغ پایه: " . number_format($grossAmount)
                    . " تومان، کسر ۳۰٪: " . number_format($deduction)
                    . " تومان",
                    auth()->user()
                );
            }
        });

        return back()->with(
            'success',
            $status === ReturnStatus::Refunded
                ? 'بازگشت وجه انجام شد؛ ۳۰٪ کسر و مابقی به کیف پول مشتری واریز شد.'
                : 'وضعیت مرجوعی به‌روزرسانی شد.'
        );
    }
}
