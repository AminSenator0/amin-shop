<?php

namespace Tests\Feature\Wallet;

use App\Enums\WalletReferenceType;
use App\Exceptions\InsufficientFundsException;
use App\Exceptions\InvalidWalletOperationException;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    private WalletService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WalletService::class);
    }

    public function test_credit_increases_balance_and_writes_ledger(): void
    {
        $wallet = $this->service->getOrCreateForUser(User::factory()->create());

        $tx = $this->service->credit($wallet, 50000, WalletReferenceType::C2CDeposit, 1, 'شارژ تست');

        $wallet->refresh();
        $this->assertSame(50000, $wallet->balance);
        $this->assertSame(50000, $tx->balance_after);
        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'credit',
            'amount' => 50000,
            'balance_after' => 50000,
        ]);
    }

    public function test_debit_decreases_balance(): void
    {
        $wallet = $this->service->getOrCreateForUser(User::factory()->create());
        $this->service->credit($wallet, 100000, WalletReferenceType::C2CDeposit, 1);

        $this->service->debit($wallet, 40000, WalletReferenceType::OrderPayment, 10, 'پرداخت سفارش');

        $this->assertSame(60000, $wallet->refresh()->balance);
    }

    public function test_debit_throws_when_insufficient_and_balance_unchanged(): void
    {
        $wallet = $this->service->getOrCreateForUser(User::factory()->create());
        $this->service->credit($wallet, 10000, WalletReferenceType::C2CDeposit, 1);

        try {
            $this->service->debit($wallet, 50000, WalletReferenceType::OrderPayment, 10);
            $this->fail('باید InsufficientFundsException پرتاب می‌شد');
        } catch (InsufficientFundsException) {
            $this->assertSame(10000, $wallet->refresh()->balance);
        }
    }

    public function test_same_reference_is_idempotent(): void
    {
        $wallet = $this->service->getOrCreateForUser(User::factory()->create());

        $tx1 = $this->service->credit($wallet, 50000, WalletReferenceType::C2CDeposit, 1);
        $tx2 = $this->service->credit($wallet, 50000, WalletReferenceType::C2CDeposit, 1);

        $this->assertSame(50000, $wallet->refresh()->balance);
        $this->assertSame($tx1->id, $tx2->id);
        $this->assertSame(1, WalletTransaction::count());
    }

    public function test_zero_or_negative_amount_is_rejected(): void
    {
        $wallet = $this->service->getOrCreateForUser(User::factory()->create());

        $this->expectException(InvalidWalletOperationException::class);
        $this->service->credit($wallet, 0, WalletReferenceType::C2CDeposit, 1);
    }

    public function test_balance_never_goes_negative(): void
    {
        $wallet = $this->service->getOrCreateForUser(User::factory()->create());
        $this->service->credit($wallet, 10000, WalletReferenceType::C2CDeposit, 1);

        try {
            $this->service->debit($wallet, 99999, WalletReferenceType::OrderPayment, 10);
            $this->fail('باید خطا پرتاب می‌شد');
        } catch (InsufficientFundsException) {
            $this->assertGreaterThanOrEqual(0, $wallet->refresh()->balance);
        }
    }
}