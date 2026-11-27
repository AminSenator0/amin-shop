<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PresaleStatus;
use App\Enums\WalletReferenceType;
use App\Http\Controllers\Controller;
use App\Models\PresaleOrder;
use App\Services\PresalePricingService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresaleOrderController extends Controller
{
    public function __construct(
        private PresalePricingService $pricing,
        private WalletService $wallets,
    ) {}

    public function index(Request $request)
    {
        $query = PresaleOrder::with(['user', 'campaign'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('admin.presale.orders.index', compact('orders'));
    }

    public function show(PresaleOrder $presaleOrder)
    {
        $presaleOrder->load(['items', 'payments', 'events.actor', 'user']);

        return view('admin.presale.orders.show', compact('presaleOrder'));
    }

    /**
     * لغو سفارش پیش‌فروش + برگشت کل وجه به کیف پول.
     */
    public function cancel(Request $request, PresaleOrder $presaleOrder)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        if (! $presaleOrder->status->isOpen()) {
            return back()->with('error', 'این سفارش قبلاً بسته شده و قابل لغو نیست.');
        }

        DB::transaction(function () use ($presaleOrder, $validated) {
            $lockedOrder = PresaleOrder::lockForUpdate()->findOrFail($presaleOrder->id);

            $totalPaid = (int) $lockedOrder->total_paid;

            // ۱) برگشت وجه به کیف پول (اگر چیزی پرداخت کرده)
            if ($totalPaid > 0) {
                $wallet = $this->wallets->getOrCreateForUser($lockedOrder->user);

                $this->wallets->credit(
                    $wallet,
                    $totalPaid,
                    WalletReferenceType::PresaleRefund,
                    $lockedOrder->id,
                    "برگشت وجه پیش‌فروش لغوشده: {$lockedOrder->order_number}",
                    auth()->user(),
                );
            }

            // ۲) لغو سفارش
            $lockedOrder->update([
                'status'        => PresaleStatus::Cancelled,
                'cancelled_at'  => now(),
                'cancel_reason' => $validated['reason'],
            ]);

            // ۳) لاگ
            \App\Models\PresaleEvent::create([
                'presale_order_id' => $lockedOrder->id,
                'actor_id'         => auth()->id(),
                'action'           => 'cancelled_by_admin',
                'payload'          => [
                    'reason'      => $validated['reason'],
                    'refund_amount' => $totalPaid,
                ],
                'ip'               => request()->ip(),
            ]);
        }, 3);

        return redirect()->route('admin.presale.orders.index')
            ->with('success', 'سفارش لغو و وجه به کیف پول کاربر برگشت داده شد.');
    }

    /** تمدید مهلت یک سفارش خاص */
    public function extendDeadline(Request $request, PresaleOrder $presaleOrder)
    {
        $validated = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $this->pricing->extendDeadline($presaleOrder, $validated['days'], auth()->id());

        return back()->with('success', "مهلت تسویه {$validated['days']} روز تمدید شد.");
    }
}