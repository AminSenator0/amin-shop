<?php

namespace App\Http\Controllers\User;

use App\Enums\LogAction;
use App\Enums\LogSeverity;
use App\Enums\WalletDepositStatus;
use App\Enums\WalletGateway;
use App\Enums\WalletReferenceType;
use App\Http\Controllers\Controller;
use App\Models\WalletDeposit;
use App\Services\AuditLogService;
use App\Services\WalletService;
use App\Services\ZarinpalService;
use App\Support\StoreSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Enums\WalletWithdrawalStatus;
use App\Exceptions\InvalidWalletOperationException;
use App\Models\Wallet;
use App\Models\WalletWithdrawal;
use App\Exceptions\InsufficientFundsException;

class WalletController extends Controller
{
    public function __construct(
        private WalletService $wallets,
        private ZarinpalService $zarinpal,
    ) {}

    /**
     * صفحه‌ی کیف پول: موجودی + تراکنش‌ها + فرم شارژ + شارژهای در انتظار.
     */
    public function index(): View
    {
        $user = auth()->user();
        $wallet = $this->wallets->getOrCreateForUser($user);

        $transactions = $wallet->transactions()->paginate(15);

        // شارژهای اخیر کاربر (در انتظار تأیید / وضعیت)
        $recentDeposits = $wallet->deposits()->take(5)->get();

        return view('user.wallet.index', [
            'wallet'         => $wallet,
            'transactions'   => $transactions,
            'recentDeposits' => $recentDeposits,
            'minDeposit'     => (int) StoreSettings::get('wallet_min_deposit', 50000),     // تومان
            'maxDeposit'     => (int) StoreSettings::get('wallet_max_deposit', 10000000),  // تومان
        ]);
    }

    /**
     * ایجاد درخواست شارژ و هدایت به درگاه زرین‌پال.
     *
     * امنیت:
     * - مبلغ فقط از ورودی کاربر نیاد؛ حداقل/حداکثر از تنظیمات سرور چک می‌شه
     * - throttle روی route (جلوگیری از اسپم درخواست)
     */
    public function charge(Request $request): RedirectResponse
    {
        $min = (int) StoreSettings::get('wallet_min_deposit', 50000);     // تومان
        $max = (int) StoreSettings::get('wallet_max_deposit', 10000000);  // تومان

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:'.$min, 'max:'.$max],
        ], [
            'amount.min' => 'حداقل مبلغ شارژ '.format_price($min).' است.',
            'amount.max' => 'حداکثر مبلغ شارژ '.format_price($max).' است.',
        ]);

        $user   = auth()->user();
        $wallet = $this->wallets->getOrCreateForUser($user);

        if (! $this->zarinpal->isConfigured()) {
            return back()->with('error', 'درگاه زرین‌پال پیکربندی نشده است.');
        }

        $deposit = WalletDeposit::create([
            'user_id'   => $user->id,
            'wallet_id' => $wallet->id,
            'amount'    => (int) $validated['amount'],
            'gateway'   => WalletGateway::Zarinpal,
            'status'    => WalletDepositStatus::Pending,
        ]);

        AuditLogService::log(
            LogAction::WALLET_DEPOSIT_CREATED,
            $user->id,
            ['deposit_id' => $deposit->id, 'amount' => $deposit->amount, 'gateway' => 'zarinpal'],
            severity: LogSeverity::INFO,
        );

        try {
            $payment = $this->zarinpal->requestWalletPayment($deposit);
            $deposit->update(['authority' => $payment['authority']]);

            return redirect()->away($payment['redirect_url']);
        } catch (\RuntimeException $e) {
            $deposit->update(['status' => WalletDepositStatus::Expired]);

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Callback زرین‌پال برای شارژ کیف پول.
     *
     * الگوی امنیتی (دقیقاً مثل PaymentController سفارش‌ها):
     * - مبلغ فقط از دیتابیس خوانده می‌شود (نه از پارامتر درگاه)
     * - قفل lockForUpdate روی ردیف deposit
     * - idempotency: اگر قبلاً paid شده → بدون تغییر، موفق برمی‌گردد
     * - credit کیف پول با reference deposit → double-credit غیرممکن
     */
    public function callback(Request $request): RedirectResponse
    {
        $authority = trim((string) $request->query('Authority', ''));
        $status    = trim((string) $request->query('Status', ''));

        // ⚠️ closure — فقط وقتی ساخته می‌شود که واقعاً صدا زده شود
        // (اگر redirect آماده ساخته شود، ->with() در همان لحظه flash می‌شود و
        //  حتی در مسیر موفقیت هم پیام خطا نمایش داده می‌شود)
        $failedRedirect = function () {
            return redirect()
                ->route('wallet.index')
                ->with('error', 'پرداخت شارژ کیف پول انجام نشد یا توسط کاربر لغو شد.');
        };

        if ($authority === '' || $status === '') {
            return $failedRedirect();
        }

        $deposit = WalletDeposit::query()
            ->where('authority', $authority)
            ->where('gateway', WalletGateway::Zarinpal)
            ->first();

        if (! $deposit) {
            return $failedRedirect();
        }

        try {
            $result = DB::transaction(function () use ($deposit, $status, $authority) {
                $locked = WalletDeposit::lockForUpdate()->findOrFail($deposit->id);

                if ($locked->status === WalletDepositStatus::Paid) {
                    return ['type' => 'alreadyPaid'];
                }

                if ($locked->status !== WalletDepositStatus::Pending) {
                    return ['type' => 'invalid'];
                }

                if ($status !== 'OK') {
                    $locked->update(['status' => WalletDepositStatus::Expired]);

                    return ['type' => 'failed'];
                }

                // مبلغ فقط از دیتابیس — هرگز از پارامتر درگاه
                // ⚠️ deposit->amount به تومان است؛ verifyPayment داخلاً به ریال تبدیل می‌کند
                $verification = $this->zarinpal->verifyPayment($authority, (int) $locked->amount);

                // credit با reference=deposit_id → idempotent (replays دوم بی‌اثر است)
                $this->wallets->credit(
                    $locked->wallet,
                    (int) $locked->amount,
                    WalletReferenceType::ZarinpalDeposit,
                    $locked->id,
                    'شارژ کیف پول (زرین‌پال)',
                );

                $locked->update([
                    'status'       => WalletDepositStatus::Paid,
                    'paid_at'      => now(),
                ]);

                return ['type' => 'success', 'ref_id' => $verification['ref_id'] ?? null];
            });

            return match ($result['type']) {
                'alreadyPaid' => redirect()->route('wallet.index')->with('success', 'این شارژ قبلاً انجام شده است.'),
                'invalid'     => $failedRedirect(),
                'failed'      => $failedRedirect(),
                default       => redirect()->route('wallet.index')->with('success', 'کیف پول شما با موفقیت شارژ شد.'),
            };
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('wallet.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * درخواست شارژ کارت به کارت: آپلود رسید + کد رهگیری.
     *
     * امنیت:
     * - مبلغ با حداقل/حداکثر تنظیمات سرور validate می‌شود
     * - رسید فقط تصویر jpeg/png/jpg تا ۵ مگابایت
     * - مالکیت: فقط کیف پول کاربر جاری
     * - وضعیت pending تا تأیید ادمین — اعتبار فقط از مسیر Admin\WalletController@verifyDeposit
     */
    public function chargeC2c(Request $request): RedirectResponse
    {
        $min = (int) StoreSettings::get('wallet_min_deposit', 10000);     // تومان
        $max = (int) StoreSettings::get('wallet_max_deposit', 10000000);  // تومان

        $validated = $request->validate([
            'amount'       => ['required', 'integer', 'min:'.$min, 'max:'.$max],
            'tracking_code'=> ['nullable', 'string', 'max:50'],
            'receipt'      => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ], [
            'amount.min'  => 'حداقل مبلغ شارژ '.format_price($min).' است.',
            'amount.max'  => 'حداکثر مبلغ شارژ '.format_price($max).' است.',
            'receipt.required' => 'بارگذاری تصویر رسید الزامی است.',
            'receipt.image'    => 'رسید باید تصویر باشد.',
            'receipt.mimes'    => 'فرمت رسید فقط jpeg یا png است.',
            'receipt.max'      => 'حجم رسید حداکثر ۵ مگابایت است.',
        ]);

        $user   = auth()->user();
        $wallet = $this->wallets->getOrCreateForUser($user);

        // آپلود رسید — پوشه‌ی جدا از رسیدهای سفارش
        $path = $request->file('receipt')->store('wallet-receipts', 'public');

        $deposit = WalletDeposit::create([
            'user_id'       => $user->id,
            'wallet_id'     => $wallet->id,
            'amount'        => (int) $validated['amount'],
            'gateway'       => WalletGateway::C2C,
            'status'        => WalletDepositStatus::Pending,
            'receipt_path'  => $path,
            'tracking_code' => $validated['tracking_code'] ?? null,
        ]);

        AuditLogService::log(
            LogAction::WALLET_DEPOSIT_CREATED,
            $user->id,
            [
                'deposit_id'    => $deposit->id,
                'amount'        => $deposit->amount,
                'gateway'       => 'c2c',
                'tracking_code' => $deposit->tracking_code,
            ],
            severity: LogSeverity::INFO,
        );

        return redirect()
            ->route('wallet.index')
            ->with('success', 'رسید شما ثبت شد. پس از تأیید کارشناسان، مبلغ به کیف پول شما اضافه می‌شود.');
    }
        /**
     * درخواست برداشت از کیف پول.
     *
     * جریان امن:
     * ۱) مبلغ فقط با حداقل تنظیمات validate می‌شود (سقف = موجودی واقعی)
     * ۲) کیف پول با lockForUpdate قفل می‌شود
     * ۳) مبلغ بلافاصله debit (بلوکه) می‌شود — پول قفل است ناپدید نیست
     * ۴) اگر ادمین رد کند → WithdrawalRefund برمی‌گردد (فاز ۲ آماده است)
     * ۵) سفارش ساخت withdrawal و debit در یک transaction → هر خطایی کل عملیات را rollback می‌کند
     */
    public function withdraw(Request $request): RedirectResponse
    {
        $min = (int) StoreSettings::get('wallet_min_withdrawal', 200000); // تومان

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:'.$min],
            'sheba'  => ['required', 'string', 'regex:/^IR\d{24}$/'],
        ], [
            'amount.min'  => 'حداقل مبلغ برداشت '.format_price($min).' است.',
            'sheba.regex' => 'شماره شبا باید با IR شروع شده و ۲۴ رقم داشته باشد (مثل IR060120000000002345678901).',
        ]);

        $user = auth()->user();

        try {
            $withdrawal = DB::transaction(function () use ($user, $validated) {
                $wallet = Wallet::query()
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if (! $wallet || ! $wallet->is_active) {
                    throw new InvalidWalletOperationException('کیف پول شما فعال نیست.');
                }

                $withdrawal = WalletWithdrawal::create([
                    'user_id'   => $user->id,
                    'wallet_id' => $wallet->id,
                    'amount'    => (int) $validated['amount'],
                    'sheba'     => $validated['sheba'],
                    'status'    => WalletWithdrawalStatus::Pending,
                ]);

                // بلوکه کردن مبلغ — اگر موجودی کافی نباشد exception و rollback
                $this->wallets->debit(
                    $wallet,
                    (int) $validated['amount'],
                    WalletReferenceType::WithdrawalHold,
                    $withdrawal->id,
                    'درخواست برداشت از کیف پول (برداشت شماره '.$withdrawal->id.')',
                );

                return $withdrawal;
            });

            AuditLogService::log(
                LogAction::WALLET_WITHDRAWAL_REQUESTED,
                $user->id,
                [
                    'withdrawal_id' => $withdrawal->id,
                    'amount'        => $withdrawal->amount,
                    'sheba'         => $withdrawal->sheba,
                ],
                severity: LogSeverity::INFO,
            );

            return redirect()
                ->route('wallet.index')
                ->with('success', 'درخواست برداشت شما ثبت شد. پس از بررسی، مبلغ به شبای شما واریز می‌شود.');
        } catch (InsufficientFundsException $e) {
            return back()->with('error', $e->getMessage());
        } catch (InvalidWalletOperationException $e) {
            return back()->with('error', $e->getMessage());
        }
    }       
}