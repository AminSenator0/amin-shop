<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LogAction;
use App\Enums\LogSeverity;
use App\Enums\WalletDepositStatus;
use App\Enums\WalletReferenceType;
use App\Enums\WalletWithdrawalStatus;
use App\Http\Controllers\Controller;
use App\Models\WalletDeposit;
use App\Models\WalletWithdrawal;
use App\Services\AuditLogService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(private WalletService $wallets) {}

    /**
     * لیست شارژهای کیف پول (فیلتر: همه / در انتظار / درگاه).
     */
    public function deposits(Request $request): View
    {
        $query = WalletDeposit::query()->with('user')->latest();

        if ($request->filled('status') && in_array($request->status, ['pending', 'paid', 'rejected', 'expired'], true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('gateway') && in_array($request->gateway, ['zarinpal', 'c2c'], true)) {
            $query->where('gateway', $request->gateway);
        }

        $deposits = $query->paginate(20)->withQueryString();

        $pendingCount = WalletDeposit::pending()->count();

        return view('admin.wallet.deposits', compact('deposits', 'pendingCount'));
    }

    /**
     * تأیید شارژ کارت به کارت → واریز به کیف پول.
     * Idempotent: اگر قبلاً paid شده، همان را برمی‌گرداند (تراکنش تکراری نمی‌سازد).
     */
    public function verifyDeposit(WalletDeposit $deposit): RedirectResponse
    {
        if ($deposit->status !== WalletDepositStatus::Pending) {
            return back()->with('error', 'این درخواست قبلاً بررسی شده است.');
        }

        DB::transaction(function () use ($deposit) {
            $locked = WalletDeposit::lockForUpdate()->findOrFail($deposit->id);

            if ($locked->status !== WalletDepositStatus::Pending) {
                return; // رقابت بین دو ادمین — اولی برد
            }

            $this->wallets->credit(
                $locked->wallet,
                $locked->amount,
                $locked->gateway->value === 'zarinpal'
                    ? WalletReferenceType::ZarinpalDeposit
                    : WalletReferenceType::C2CDeposit,
                $locked->id,
                'شارژ کیف پول ('.$locked->gateway->label().')',
                auth()->user()
            );

            $locked->update([
                'status'       => WalletDepositStatus::Paid,
                'paid_at'      => now(),
                'processed_by' => auth()->id(),
            ]);
        });

        AuditLogService::log(
            LogAction::WALLET_DEPOSIT_VERIFIED,
            auth()->id(),
            ['deposit_id' => $deposit->id, 'user_id' => $deposit->user_id, 'amount' => $deposit->amount],
            severity: LogSeverity::HIGH,
        );

        return back()->with('success', 'شارژ تأیید و کیف پول واریز شد.');
    }

    /**
     * رد شارژ کارت به کارت.
     */
    public function rejectDeposit(Request $request, WalletDeposit $deposit): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        if ($deposit->status !== WalletDepositStatus::Pending) {
            return back()->with('error', 'این درخواست قبلاً بررسی شده است.');
        }

        $deposit->update([
            'status'           => WalletDepositStatus::Rejected,
            'rejection_reason' => $validated['rejection_reason'],
            'processed_by'     => auth()->id(),
        ]);

        AuditLogService::log(
            LogAction::WALLET_DEPOSIT_REJECTED,
            auth()->id(),
            ['deposit_id' => $deposit->id, 'user_id' => $deposit->user_id, 'reason' => $validated['rejection_reason']],
            severity: LogSeverity::HIGH,
        );

        return back()->with('success', 'درخواست شارژ رد شد.');
    }

    /**
     * لیست درخواست‌های برداشت.
     */
    public function withdrawals(Request $request): View
    {
        $query = WalletWithdrawal::query()->with('user')->latest();

        if ($request->filled('status') && in_array($request->status, ['pending', 'paid', 'rejected'], true)) {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->paginate(20)->withQueryString();
        $pendingCount = WalletWithdrawal::pending()->count();

        return view('admin.wallet.withdrawals', compact('withdrawals', 'pendingCount'));
    }

    /**
     * تأیید برداشت → ادمین دستی پول را به شبا واریز می‌کند.
     * (مبلغ هنگام درخواست از کیف پول کم شده؛ اینجا فقط وضعیت نهایی می‌شود)
     */
    public function approveWithdrawal(Request $request, WalletWithdrawal $withdrawal): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($withdrawal->status !== WalletWithdrawalStatus::Pending) {
            return back()->with('error', 'این درخواست قبلاً بررسی شده است.');
        }

        $withdrawal->update([
            'status'       => WalletWithdrawalStatus::Paid,
            'admin_note'   => $validated['admin_note'] ?? null,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        AuditLogService::log(
            LogAction::WALLET_WITHDRAWAL_PAID,
            auth()->id(),
            ['withdrawal_id' => $withdrawal->id, 'user_id' => $withdrawal->user_id, 'amount' => $withdrawal->amount, 'sheba' => $withdrawal->sheba],
            severity: LogSeverity::HIGH,
        );

        return back()->with('success', 'برداشت تأیید شد. حالا باید مبلغ را به شبای کاربر واریز کنید.');
    }

    /**
     * رد برداشت → برگشت مبلغ بلوکه‌شده به کیف پول.
     */
    public function rejectWithdrawal(Request $request, WalletWithdrawal $withdrawal): RedirectResponse
    {
        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        if ($withdrawal->status !== WalletWithdrawalStatus::Pending) {
            return back()->with('error', 'این درخواست قبلاً بررسی شده است.');
        }

        DB::transaction(function () use ($withdrawal, $validated) {
            $locked = WalletWithdrawal::lockForUpdate()->findOrFail($withdrawal->id);

            if ($locked->status !== WalletWithdrawalStatus::Pending) {
                return;
            }

            // برگشت مبلغ بلوکه‌شده به کیف پول
            $this->wallets->credit(
                $locked->wallet,
                $locked->amount,
                WalletReferenceType::WithdrawalRefund,
                $locked->id,
                'رد درخواست برداشت: '.$validated['admin_note'],
                auth()->user()
            );

            $locked->update([
                'status'       => WalletWithdrawalStatus::Rejected,
                'admin_note'   => $validated['admin_note'],
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);
        });

        AuditLogService::log(
            LogAction::WALLET_WITHDRAWAL_REJECTED,
            auth()->id(),
            ['withdrawal_id' => $withdrawal->id, 'user_id' => $withdrawal->user_id, 'reason' => $validated['admin_note']],
            severity: LogSeverity::HIGH,
        );

        return back()->with('success', 'برداشت رد شد و مبلغ به کیف پول کاربر برگشت.');
    }
}