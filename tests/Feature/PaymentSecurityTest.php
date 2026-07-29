<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\ZarinpalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function configureZarinpal(): void
    {
        StoreSetting::set('zarinpal_merchant_id', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', 'payment');
        StoreSetting::set('zarinpal_sandbox', '1', 'payment', 'boolean');
        StoreSetting::set('currency', 'تومان', 'appearance');
    }

    private function clearZarinpal(): void
    {
        StoreSetting::query()->whereIn('key', ['zarinpal_merchant_id', 'zarinpal_sandbox'])->delete();
        config([
            'zarinpal.merchant_id' => null,
            'zarinpal.sandbox' => false,
            'zarinpal.sandbox_merchant_id' => '',
        ]);
    }

    private function makeOrder(User $user, array $overrides = []): Order
    {
        $category = Category::firstOrCreate(
            ['slug' => 'sec-test'],
            ['name' => 'تست امنیتی', 'is_active' => true]
        );
        $product = Product::firstOrCreate(
            ['sku' => 'SEC-1'],
            [
                'category_id' => $category->id,
                'name' => 'محصول امنیتی',
                'slug' => 'sec-product',
                'price' => 100000,
                'stock' => 100,
                'is_active' => true,
            ]
        );
        $shipping = ShippingMethod::firstOrCreate(
            ['name' => 'پست امن'],
            ['cost' => 50000, 'is_active' => true]
        );

        $order = Order::create(array_merge([
            'user_id' => $user->id,
            'order_number' => Order::generateOrderNumber(),
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Pending,
            'shipping_method_id' => $shipping->id,
            'subtotal' => 100000,
            'shipping_cost' => 50000,
            'discount_amount' => 0,
            'total' => 150000,
            'shipping_address' => [
                'full_name' => 'کاربر',
                'phone' => '09121234567',
                'province' => 'تهران',
                'city' => 'تهران',
                'address' => 'آدرس',
                'postal_code' => '1234567890',
            ],
        ], $overrides));

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'price' => $product->price,
            'quantity' => 1,
            'total' => $product->price,
        ]);

        // هم‌تراز با checkout واقعی: موجودی هنگام ثبت سفارش رزرو می‌شود
        if (($overrides['payment_status'] ?? PaymentStatus::Pending) === PaymentStatus::Pending
            && ($overrides['status'] ?? OrderStatus::Pending) === OrderStatus::Pending) {
            $product->decrement('stock', 1);
        }

        return $order;
    }

    private function fakeVerifySuccess(int $refId = 9001): void
    {
        Http::fake([
            'sandbox.zarinpal.com/pg/v4/payment/verify.json' => Http::response([
                'data' => [
                    'code' => 100,
                    'message' => 'Verified',
                    'ref_id' => $refId,
                    'card_pan' => '502229******5995',
                    'card_hash' => 'HASH',
                    'fee_type' => 'Merchant',
                    'fee' => 0,
                ],
                'errors' => [],
            ], 200),
            'sandbox.zarinpal.com/pg/v4/payment/request.json' => Http::response([
                'data' => [
                    'code' => 100,
                    'authority' => 'A00000000000000000000000000000REQ1',
                    'fee_type' => 'Merchant',
                    'fee' => 0,
                ],
                'errors' => [],
            ], 200),
        ]);
    }

    public function test_other_user_cannot_start_payment_for_foreign_order(): void
    {
        $this->configureZarinpal();
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $order = $this->makeOrder($owner);

        Http::fake();

        $this->actingAs($attacker)
            ->get(route('checkout.payment', $order))
            ->assertForbidden();

        Http::assertNothingSent();
        $this->assertNull($order->fresh()->payment_authority);
    }

    public function test_other_user_cannot_use_test_payment_on_foreign_order(): void
    {
        $this->clearZarinpal();
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $order = $this->makeOrder($owner);

        $this->actingAs($attacker)
            ->post(route('checkout.payment.process', $order))
            ->assertForbidden();

        $this->assertSame(PaymentStatus::Pending, $order->fresh()->payment_status);
    }

    public function test_guest_cannot_start_or_process_payment(): void
    {
        $this->clearZarinpal();
        $order = $this->makeOrder(User::factory()->create());

        $this->get(route('checkout.payment', $order))->assertRedirect(route('login'));
        $this->post(route('checkout.payment.process', $order))->assertRedirect(route('login'));
        $this->assertSame(PaymentStatus::Pending, $order->fresh()->payment_status);
    }

    public function test_internal_test_payment_is_removed_and_redirects_to_gateway(): void
    {
        $this->configureZarinpal();
        $user = User::factory()->create();
        $order = $this->makeOrder($user);

        Http::fake([
            'sandbox.zarinpal.com/*' => Http::response([
                'data' => [
                    'code' => 100,
                    'authority' => 'A00000000000000000000000000000GW01',
                    'fee_type' => 'Merchant',
                    'fee' => 0,
                ],
                'errors' => [],
            ], 200),
        ]);

        $this->actingAs($user)
            ->post(route('checkout.payment.process', $order))
            ->assertRedirect(route('checkout.payment', $order));

        $this->assertSame(PaymentStatus::Pending, $order->fresh()->payment_status);
        $this->assertNull($order->fresh()->payment_ref);

        $this->actingAs($user)
            ->get(route('checkout.payment', $order))
            ->assertRedirect('https://sandbox.zarinpal.com/pg/StartPay/A00000000000000000000000000000GW01');
    }

    public function test_cancelled_order_cannot_be_paid(): void
    {
        $this->configureZarinpal();
        $user = User::factory()->create();
        $order = $this->makeOrder($user, [
            'status' => OrderStatus::Cancelled,
            'payment_authority' => 'A00000000000000000000000000000CAN1',
        ]);

        Http::fake();

        $this->actingAs($user)
            ->get(route('checkout.payment', $order))
            ->assertRedirect(route('user.orders.show', $order));

        Http::assertNothingSent();

        $this->fakeVerifySuccess();

        $this->actingAs($user)
            ->get(route('payment.callback', [
                'Authority' => 'A00000000000000000000000000000CAN1',
                'Status' => 'OK',
            ]))
            ->assertRedirect();

        $this->assertSame(PaymentStatus::Failed, $order->fresh()->payment_status);
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
    }

    public function test_fake_status_ok_without_valid_verify_marks_payment_failed(): void
    {
        $this->configureZarinpal();
        $user = User::factory()->create();
        $order = $this->makeOrder($user, [
            'payment_authority' => 'A00000000000000000000000000000FAKE',
        ]);

        Http::fake([
            'sandbox.zarinpal.com/pg/v4/payment/verify.json' => Http::response([
                'data' => ['code' => -50, 'message' => 'Failed'],
                'errors' => [['code' => -50, 'message' => 'Session is not valid']],
            ], 200),
        ]);

        $this->actingAs($user)
            ->get(route('payment.callback', [
                'Authority' => 'A00000000000000000000000000000FAKE',
                'Status' => 'OK',
            ]));

        $order->refresh();
        $this->assertSame(PaymentStatus::Failed, $order->payment_status);
        $this->assertSame(OrderStatus::Failed, $order->status);
        $this->assertNull($order->paid_at);
        $this->assertNull($order->payment_ref);
    }

    public function test_callback_ignores_forged_amount_query_and_uses_db_total(): void
    {
        $this->configureZarinpal();
        $user = User::factory()->create();
        $order = $this->makeOrder($user, [
            'total' => 250000,
            'payment_authority' => 'A00000000000000000000000000000AMT1',
        ]);

        Http::fake([
            'sandbox.zarinpal.com/pg/v4/payment/verify.json' => Http::response([
                'data' => [
                    'code' => 100,
                    'ref_id' => 777,
                    'card_pan' => '502229******5995',
                    'fee_type' => 'Merchant',
                    'fee' => 0,
                ],
                'errors' => [],
            ], 200),
        ]);

        $this->actingAs($user)
            ->get(route('payment.callback', [
                'Authority' => 'A00000000000000000000000000000AMT1',
                'Status' => 'OK',
                'Amount' => '1',
                'amount' => '1',
                'total' => '1',
            ]))
            ->assertRedirect(route('user.orders.show', $order));

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'verify.json')
                && $request->data()['amount'] === 2500000;
        });

        $this->assertSame(PaymentStatus::Paid, $order->fresh()->payment_status);
    }

    public function test_unknown_authority_with_ok_does_not_pay_any_order(): void
    {
        $this->configureZarinpal();
        $user = User::factory()->create();
        $order = $this->makeOrder($user, [
            'payment_authority' => 'A00000000000000000000000000000REAL',
        ]);

        $this->fakeVerifySuccess();

        $this->get(route('payment.callback', [
            'Authority' => 'A00000000000000000000000000000UNKNOWN',
            'Status' => 'OK',
        ]))->assertRedirect(route('home'));

        $this->assertSame(PaymentStatus::Pending, $order->fresh()->payment_status);
        Http::assertNothingSent();
    }

    public function test_status_case_sensitivity_rejects_ok_lowercase(): void
    {
        $this->configureZarinpal();
        $user = User::factory()->create();
        $order = $this->makeOrder($user, [
            'payment_authority' => 'A00000000000000000000000000000CASE',
        ]);

        $this->fakeVerifySuccess();

        $this->actingAs($user)
            ->get(route('payment.callback', [
                'Authority' => 'A00000000000000000000000000000CASE',
                'Status' => 'ok',
            ]))
            ->assertRedirect(route('user.orders.index'));

        $this->assertSame(PaymentStatus::Failed, $order->fresh()->payment_status);
        $this->assertSame(OrderStatus::Failed, $order->fresh()->status);
    }

    public function test_double_callback_does_not_change_ref_or_double_apply(): void
    {
        $this->configureZarinpal();
        $user = User::factory()->create();
        $order = $this->makeOrder($user, [
            'payment_authority' => 'A00000000000000000000000000000DBL1',
        ]);

        $this->fakeVerifySuccess(555);

        $this->actingAs($user)
            ->get(route('payment.callback', [
                'Authority' => 'A00000000000000000000000000000DBL1',
                'Status' => 'OK',
            ]));

        $this->assertSame('555', $order->fresh()->payment_ref);

        Http::fake([
            'sandbox.zarinpal.com/pg/v4/payment/verify.json' => Http::response([
                'data' => [
                    'code' => 101,
                    'ref_id' => 999999,
                    'card_pan' => '502229******5995',
                    'fee_type' => 'Merchant',
                    'fee' => 0,
                ],
                'errors' => [],
            ], 200),
        ]);

        $this->actingAs($user)
            ->get(route('payment.callback', [
                'Authority' => 'A00000000000000000000000000000DBL1',
                'Status' => 'OK',
            ]))
            ->assertRedirect(route('user.orders.show', $order));

        $order->refresh();
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame('555', $order->payment_ref);
        Http::assertNothingSent();
    }

    public function test_authority_from_order_a_cannot_pay_order_b(): void
    {
        $this->configureZarinpal();
        $user = User::factory()->create();
        $orderA = $this->makeOrder($user, [
            'payment_authority' => 'A00000000000000000000000000000ORDA',
            'total' => 100000,
        ]);
        $orderB = $this->makeOrder($user, [
            'payment_authority' => 'A00000000000000000000000000000ORDB',
            'total' => 200000,
        ]);

        $this->fakeVerifySuccess(111);

        $this->actingAs($user)
            ->get(route('payment.callback', [
                'Authority' => 'A00000000000000000000000000000ORDA',
                'Status' => 'OK',
            ]));

        $this->assertSame(PaymentStatus::Paid, $orderA->fresh()->payment_status);
        $this->assertSame(PaymentStatus::Pending, $orderB->fresh()->payment_status);
    }

    public function test_refunded_order_cannot_be_paid_again(): void
    {
        $this->configureZarinpal();
        $user = User::factory()->create();
        $order = $this->makeOrder($user, [
            'status' => OrderStatus::Paid,
            'payment_status' => PaymentStatus::Refunded,
            'payment_authority' => 'A00000000000000000000000000000REF1',
            'payment_ref' => 'OLD-1',
        ]);

        $this->fakeVerifySuccess(222);

        $this->actingAs($user)
            ->get(route('checkout.payment', $order))
            ->assertRedirect(route('user.orders.show', $order));

        $this->actingAs($user)
            ->get(route('payment.callback', [
                'Authority' => 'A00000000000000000000000000000REF1',
                'Status' => 'OK',
            ]));

        $order->refresh();
        $this->assertSame(PaymentStatus::Refunded, $order->payment_status);
        $this->assertSame('OLD-1', $order->payment_ref);
    }

    public function test_customer_cannot_update_zarinpal_settings(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->put(route('admin.settings.update'), [
                'store_name' => 'هک',
                'currency' => 'تومان',
                'color_preset' => 'teal',
                'return_days' => 7,
                'zarinpal_merchant_id' => 'attacker-merchant-id-xxxxxxxxxxxx',
                'zarinpal_sandbox' => '0',
            ])
            ->assertRedirect(route('user.dashboard'));

        $this->assertNotSame(
            'attacker-merchant-id-xxxxxxxxxxxx',
            StoreSetting::query()->where('key', 'zarinpal_merchant_id')->value('value')
        );
        $this->assertNull(
            StoreSetting::query()->where('key', 'zarinpal_merchant_id')->value('value')
        );
    }

    public function test_merchant_id_not_leaked_on_public_pages(): void
    {
        $this->configureZarinpal();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertDontSee('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');
    }

    public function test_verify_rejects_success_without_ref_id(): void
    {
        $this->configureZarinpal();

        Http::fake([
            'sandbox.zarinpal.com/pg/v4/payment/verify.json' => Http::response([
                'data' => [
                    'code' => 100,
                    'message' => 'Verified',
                ],
                'errors' => [],
            ], 200),
        ]);

        $this->expectException(\RuntimeException::class);
        app(ZarinpalService::class)->verifyPayment('A00000000000000000000000000000NOREF', 150000);
    }

    public function test_nok_does_not_fail_already_paid_order(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrder($user, [
            'status' => OrderStatus::Paid,
            'payment_status' => PaymentStatus::Paid,
            'payment_authority' => 'A00000000000000000000000000000PAID',
            'payment_ref' => 'KEEP-ME',
            'paid_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('payment.callback', [
                'Authority' => 'A00000000000000000000000000000PAID',
                'Status' => 'NOK',
            ]));

        $order->refresh();
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame('KEEP-ME', $order->payment_ref);
    }

    public function test_empty_authority_ok_does_nothing(): void
    {
        $this->configureZarinpal();
        $user = User::factory()->create();
        $order = $this->makeOrder($user);

        $this->fakeVerifySuccess();

        $this->actingAs($user)
            ->get(route('payment.callback', [
                'Authority' => '',
                'Status' => 'OK',
            ]))
            ->assertRedirect(route('user.orders.index'));

        $this->assertSame(PaymentStatus::Pending, $order->fresh()->payment_status);
        Http::assertNothingSent();
    }

    public function test_nok_restores_cart_and_stock_and_marks_failed(): void
    {
        $this->configureZarinpal();
        $user = User::factory()->create();
        $order = $this->makeOrder($user, [
            'payment_authority' => 'A00000000000000000000000000000NOK2',
        ]);

        $product = Product::where('sku', 'SEC-1')->firstOrFail();
        $stockBeforeFail = $product->fresh()->stock;

        $this->actingAs($user);
        app(\App\Services\CartService::class)->clear();
        $this->assertTrue(app(\App\Services\CartService::class)->isEmpty());

        $this->actingAs($user)
            ->get(route('payment.callback', [
                'Authority' => 'A00000000000000000000000000000NOK2',
                'Status' => 'NOK',
            ]))
            ->assertRedirect(route('user.orders.index'));

        $order->refresh();
        $this->assertSame(PaymentStatus::Failed, $order->payment_status);
        $this->assertSame(OrderStatus::Failed, $order->status);
        $this->assertSame($stockBeforeFail + 1, $product->fresh()->stock);
        $this->assertFalse(app(\App\Services\CartService::class)->isEmpty());
        $this->assertSame(1, app(\App\Services\CartService::class)->quantityFor($product->id));
    }

    public function test_successful_payment_clears_cart(): void
    {
        $this->configureZarinpal();
        $user = User::factory()->create();
        $order = $this->makeOrder($user, [
            'payment_authority' => 'A00000000000000000000000000000OK01',
        ]);

        $this->actingAs($user);
        $cart = app(\App\Services\CartService::class);
        $cart->clear();
        $cart->restoreFromOrder($order);
        $this->assertFalse($cart->isEmpty());

        $this->fakeVerifySuccess(4242);

        $this->actingAs($user)
            ->get(route('payment.callback', [
                'Authority' => 'A00000000000000000000000000000OK01',
                'Status' => 'OK',
            ]))
            ->assertRedirect(route('user.orders.show', $order));

        $this->assertSame(PaymentStatus::Paid, $order->fresh()->payment_status);
        $this->assertTrue(app(\App\Services\CartService::class)->isEmpty());
    }
}
