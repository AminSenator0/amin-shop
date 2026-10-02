<?php

namespace Tests\Feature\Wallet;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\WalletDepositStatus;
use App\Enums\WalletGateway;
use App\Enums\WalletReferenceType;
use App\Enums\WalletWithdrawalStatus;
use App\Http\Requests\Shop\CheckoutRequest;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Models\WalletDeposit;
use App\Models\WalletTransaction;
use App\Models\WalletWithdrawal;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class WalletSecurityTest extends TestCase
{
    use RefreshDatabase;

    private WalletService $wallets;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wallets = app(WalletService::class);
    }

    // ─────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────

    private function createOrder(User $user, array $attrs = []): Order
    {
        $shipping = ShippingMethod::create([
            'name' => 'پست تست',
            'cost' => 0,
            'is_active' => true,
        ]);

        return Order::create(array_merge([
            'user_id' => $user->id,
            'order_number' => Order::generateOrderNumber(),
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Pending,
            'payment_method' => 'online',
            'shipping_method_id' => $shipping->id,
            'subtotal' => 100000,
            'shipping_cost' => 0,
            'discount_amount' => 0,
            'total' => 100000,
            'payable' => 100000,
            'shipping_address' => ['full_name' => 'تست', 'phone' => '09120000000'],
            'wallet_amount' => 0,
        ], $attrs));
    }

    // ─────────────────────────────────────────
    //  ضد دستکاری مبالغ
    // ─────────────────────────────────────────

    public function test_checkout_rejects_wallet_amount_field_from_client(): void
    {
        $user = User::factory()->create();

        $request = CheckoutRequest::create('/', 'POST');
        $request->setUserResolver(fn () => $user);
        $rules = $request->rules();

        $this->assertArrayHasKey('wallet_amount', $rules);

        $validator = Validator::make(
            ['wallet_amount' => 100000],
            ['wallet_amount' => $rules['wallet_amount']]
        );

        $this->assertTrue($validator->fails(), 'فیلد wallet_amount باید توسط CheckoutRequest رد شود');
    }
    public function test_gateway_payable_computed_server_side(): void
    {
        $user = User::factory()->create();

        // پرداخت ترکیبی: مابقی باید از کل کم شود
        $partial = $this->createOrder($user, ['total' => 200000, 'payable' => 200000, 'wallet_amount' => 100000]);
        $this->assertSame(100000, $partial->gatewayPayable());
        $this->assertFalse($partial->isFullyPaidByWallet());

        // پوشش کامل: مابقی صفر
        $full = $this->createOrder($user, ['total' => 200000, 'payable' => 200000, 'wallet_amount' => 200000]);
        $this->assertSame(0, $full->gatewayPayable());
        $this->assertTrue($full->isFullyPaidByWallet());

        // سفارش قدیمی payable=0 → fallback به total
        $legacy = $this->createOrder($user, ['total' => 150000, 'payable' => 0, 'wallet_amount' => 0]);
        $this->assertSame(150000, $legacy->gatewayPayable());
    }

    // ─────────────────────────────────────────
    //  Refund خودکار
    // ─────────────────────────────────────────

    public function test_failed_payment_refunds_wallet_part(): void
    {
        $user = User::factory()->create();
        $wallet = $this->wallets->getOrCreateForUser($user);
        $this->wallets->credit($wallet, 100000, WalletReferenceType::C2CDeposit, 1, 'شارژ');

        $order = $this->createOrder($user, ['wallet_amount' => 40000]);
        // شبیه‌سازی کم شدن سهم کیف پول هنگام checkout
        $this->wallets->debit($wallet, 40000, WalletReferenceType::OrderPayment, $order->id, 'پرداخت سفارش');
        $this->assertSame(60000, $wallet->refresh()->balance);

        // شکست پرداخت درگاه
        app(\App\Services\OrderService::class)->markPaymentFailed($order->fresh(), 'انصراف کاربر');

        $this->assertSame(100000, $wallet->refresh()->balance, 'سهم کیف پول باید برگردد');
        $this->assertSame(0, $order->refresh()->wallet_amount);
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'reference_type' => 'order_refund',
            'reference_id' => $order->id,
            'amount' => 40000,
        ]);
    }

    public function test_failed_payment_refund_is_idempotent(): void
    {
        $user = User::factory()->create();
        $wallet = $this->wallets->getOrCreateForUser($user);
        $this->wallets->credit($wallet, 100000, WalletReferenceType::C2CDeposit, 1, 'شارژ');

        $order = $this->createOrder($user, ['wallet_amount' => 40000]);
        $this->wallets->debit($wallet, 40000, WalletReferenceType::OrderPayment, $order->id, 'پرداخت');

        $orders = app(\App\Services\OrderService::class);
        $orders->markPaymentFailed($order->fresh(), 'خطای درگاه');
        $orders->markPaymentFailed($order->fresh(), 'تکرار خطا');
        $orders->refundWalletPart($order->fresh());

        $this->assertSame(100000, $wallet->refresh()->balance, 'refund نباید بیش از یک بار اعمال شود');
    }

    public function test_cancelling_pending_order_refunds_wallet_part(): void
    {
        $user = User::factory()->create();
        $wallet = $this->wallets->getOrCreateForUser($user);
        $this->wallets->credit($wallet, 100000, WalletReferenceType::C2CDeposit, 1, 'شارژ');

        $order = $this->createOrder($user, ['wallet_amount' => 30000]);
        $this->wallets->debit($wallet, 30000, WalletReferenceType::OrderPayment, $order->id, 'پرداخت');

        app(\App\Services\OrderService::class)->cancelByUser($order->fresh());

        $this->assertSame(100000, $wallet->refresh()->balance);
        $this->assertSame(OrderStatus::Cancelled, $order->refresh()->status);
    }

    // ─────────────────────────────────────────
    //  برداشت
    // ─────────────────────────

    public function test_withdraw_request_locks_funds_and_validates(): void
    {
        $user = User::factory()->create();
        $wallet = $this->wallets->getOrCreateForUser($user);
        $this->wallets->credit($wallet, 200000, WalletReferenceType::C2CDeposit, 1, 'شارژ');

        // ۱) بیشتر از موجودی → رد
        $this->actingAs($user)
            ->post(route('wallet.withdraw'), [
                'amount' => 300000,
                'sheba'  => 'IR060120000000002345678901',
            ])
            ->assertSessionHas('error');

        $this->assertSame(200000, $wallet->refresh()->balance);

        // ۲) شبای نامعتبر → رد (validation)
        $this->actingAs($user)
            ->post(route('wallet.withdraw'), [
                'amount' => 150000,
                'sheba'  => '060120000000002345678901',
            ])
            ->assertSessionHasErrors('sheba');

        // ۳) درخواست معتبر → بلوکه می‌شود
        $this->actingAs($user)
            ->post(route('wallet.withdraw'), [
                'amount' => 150000,
                'sheba'  => 'IR060120000000002345678901',
            ])
            ->assertRedirect(route('wallet.index'));

        $this->assertSame(50000, $wallet->refresh()->balance, 'مبلغ باید بلوکه شود');
        $this->assertDatabaseHas('wallet_withdrawals', [
            'user_id' => $user->id,
            'amount'  => 150000,
            'status'  => 'pending',
        ]);
    }

    public function test_admin_rejecting_withdrawal_refunds_funds(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $wallet = $this->wallets->getOrCreateForUser($user);
        $this->wallets->credit($wallet, 200000, WalletReferenceType::C2CDeposit, 1, 'شارژ');

        $this->actingAs($user)
            ->post(route('wallet.withdraw'), [
                'amount' => 150000,
                'sheba'  => 'IR060120000000002345678901',
            ]);

        $withdrawal = WalletWithdrawal::where('user_id', $user->id)->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.wallet.withdrawals.reject', $withdrawal), [
                'admin_note' => 'شبا نامعتبر است',
            ])
            ->assertRedirect();

        $this->assertSame(200000, $wallet->refresh()->balance, 'رد برداشت باید مبلغ را برگرداند');
        $this->assertSame(WalletWithdrawalStatus::Rejected, $withdrawal->refresh()->status);
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id'      => $wallet->id,
            'reference_type' => 'withdrawal_refund',
            'reference_id'   => $withdrawal->id,
            'amount'         => 150000,
        ]);
    }

    // ─────────────────────────────────────────
    //  تنظیم دستی ادمین
    // ─────────────────────────

    public function test_admin_adjust_wallet_requires_reason_and_respects_balance(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $user  = User::factory()->create();
        $wallet = $this->wallets->getOrCreateForUser($user);

        // واریز مثبت
        $this->actingAs($admin)
            ->post(route('admin.users.wallet.adjust', $user), [
                'amount' => 50000,
                'reason' => 'جبران مشکل سفارش',
            ])
            ->assertRedirect();
        $this->assertSame(50000, $wallet->refresh()->balance);

        // برداشت منفی
        $this->actingAs($admin)
            ->post(route('admin.users.wallet.adjust', $user), [
                'amount' => -20000,
                'reason' => 'کسر اشتباه واریز',
            ]);
        $this->assertSame(30000, $wallet->refresh()->balance);

        // دلیل کوتاه → رد
        $this->actingAs($admin)
            ->post(route('admin.users.wallet.adjust', $user), [
                'amount' => 10000,
                'reason' => 'بدون',
            ])
            ->assertSessionHasErrors('reason');

        // برداشت بیشتر از موجودی → رد
        $this->actingAs($admin)
            ->post(route('admin.users.wallet.adjust', $user), [
                'amount' => -999999,
                'reason' => 'کسر بیش از موجودی',
            ])
            ->assertSessionHas('error');

        $this->assertSame(30000, $wallet->refresh()->balance);
    }

    // ─────────────────────────────────────────
    //  Callback شارژ زرین‌پال (ضد replay)
    // ─────────────────────────────────────────

    public function test_wallet_callback_replay_does_not_double_credit(): void
    {
        $user = User::factory()->create();
        $wallet = $this->wallets->getOrCreateForUser($user);

        $deposit = WalletDeposit::create([
            'user_id'   => $user->id,
            'wallet_id' => $wallet->id,
            'amount'    => 50000,
            'gateway'   => WalletGateway::Zarinpal,
            'status'    => WalletDepositStatus::Pending,
            'authority' => 'AUTH-TEST-1',
        ]);

        $this->mock(\App\Services\ZarinpalService::class, function ($mock) {
            $mock->shouldReceive('verifyPayment')->once()->andReturn(['ref_id' => 123, 'code' => 100]);
        });

        // اولین callback → واریز
        $this->get(route('wallet.callback', ['Authority' => 'AUTH-TEST-1', 'Status' => 'OK']))
            ->assertRedirect(route('wallet.index'));

        $this->assertSame(50000, $wallet->refresh()->balance);
        $this->assertSame(WalletDepositStatus::Paid, $deposit->refresh()->status);

        // replay callback → نباید دوباره واریز شود (verify هم نباید دوباره صدا زده شود)
        $this->get(route('wallet.callback', ['Authority' => 'AUTH-TEST-1', 'Status' => 'OK']))
            ->assertRedirect(route('wallet.index'));

        $this->assertSame(50000, $wallet->refresh()->balance, 'replay نباید موجودی را زیاد کند');
        $this->assertSame(1, WalletTransaction::where('wallet_id', $wallet->id)->count());
    }

    // ─────────────────────────────────────────
    //  تأیید دوباره شارژ توسط ادمین
    // ─────────────────────────────────────────

    public function test_admin_double_verifying_deposit_does_not_double_credit(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $user  = User::factory()->create();
        $wallet = $this->wallets->getOrCreateForUser($user);

        $deposit = WalletDeposit::create([
            'user_id'   => $user->id,
            'wallet_id' => $wallet->id,
            'amount'    => 50000,
            'gateway'   => WalletGateway::C2C,
            'status'    => WalletDepositStatus::Pending,
            'receipt_path' => 'wallet-receipts/test.jpg',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.wallet.deposits.verify', $deposit))
            ->assertRedirect();

        $this->assertSame(50000, $wallet->refresh()->balance);

        // تأیید دوباره → رد + بدون تغییر موجودی
        $this->actingAs($admin)
            ->post(route('admin.wallet.deposits.verify', $deposit))
            ->assertSessionHas('error');

        $this->assertSame(50000, $wallet->refresh()->balance);
        $this->assertSame(1, WalletTransaction::where('wallet_id', $wallet->id)->count());
    }
}