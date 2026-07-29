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
use App\Support\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ZarinpalPaymentTest extends TestCase
{
    use RefreshDatabase;

    private function seedPendingOrder(): array
    {
        $user = User::factory()->create(['phone' => '09121234567']);
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $category = Category::create(['name' => 'تست', 'slug' => 'test', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'محصول تست',
            'slug' => 'product-test',
            'price' => 100000,
            'sku' => 'SKU-TEST-1',
            'stock' => 10,
            'is_active' => true,
        ]);
        $shipping = ShippingMethod::create([
            'name' => 'پست',
            'cost' => 50000,
            'is_active' => true,
        ]);
        $order = Order::create([
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
                'full_name' => 'کاربر تست',
                'phone' => '09121234567',
                'province' => 'تهران',
                'city' => 'تهران',
                'address' => 'خیابان تست',
                'postal_code' => '1234567890',
            ],
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'price' => $product->price,
            'quantity' => 1,
            'total' => $product->price,
        ]);

        return compact('user', 'admin', 'order');
    }

    public function test_admin_can_save_zarinpal_settings(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $preset = StoreSettings::defaultColorPresetId();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'store_name' => 'فروشگاه تست',
                'currency' => 'تومان',
                'color_preset' => $preset,
                'return_days' => 7,
                'zarinpal_merchant_id' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
                'zarinpal_sandbox' => '1',
                'zarinpal_callback_base_url' => 'https://shop.example.com',
                'active_tab' => 'payment',
            ])
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'payment']));

        $this->assertSame('xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', StoreSettings::zarinpalMerchantId());
        $this->assertTrue(StoreSettings::zarinpalSandbox());
        $this->assertTrue(StoreSettings::zarinpalIsConfigured());
        $this->assertSame('https://shop.example.com', StoreSettings::zarinpalCallbackBaseUrl());
        $this->assertSame('https://shop.example.com/payment/callback', StoreSettings::zarinpalCallbackUrl());
        $this->assertSame('shop.example.com', StoreSettings::zarinpalCallbackDomain());
    }

    public function test_admin_can_switch_to_production_mode(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $preset = StoreSettings::defaultColorPresetId();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'store_name' => 'فروشگاه تست',
                'currency' => 'تومان',
                'color_preset' => $preset,
                'return_days' => 7,
                'zarinpal_merchant_id' => '1344b5d4-0048-11e8-94db-005056a205be',
                'zarinpal_sandbox' => '0',
                'active_tab' => 'payment',
            ])
            ->assertRedirect();

        $this->assertFalse(StoreSettings::zarinpalSandbox());
    }

    public function test_settings_page_shows_payment_tab(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.settings.edit', ['tab' => 'payment']))
            ->assertOk()
            ->assertSee('درگاه زرین‌پال')
            ->assertSee('مرچنت‌کد')
            ->assertSee('تست (Sandbox)')
            ->assertSee('آدرس Callback فعلی')
            ->assertSee('/payment/callback', false)
            ->assertSee('دامنهٔ درگاه را روی این مقدار ست کنید');
    }

    public function test_request_payment_uses_sandbox_api_and_rials(): void
    {
        StoreSetting::set('zarinpal_merchant_id', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', 'payment');
        StoreSetting::set('zarinpal_sandbox', '1', 'payment', 'boolean');
        StoreSetting::set('currency', 'تومان', 'appearance');

        ['user' => $user, 'order' => $order] = $this->seedPendingOrder();

        Http::fake([
            'sandbox.zarinpal.com/pg/v4/payment/request.json' => Http::response([
                'data' => [
                    'code' => 100,
                    'message' => 'Success',
                    'authority' => 'A00000000000000000000000000000000001',
                    'fee_type' => 'Merchant',
                    'fee' => 0,
                ],
                'errors' => [],
            ], 200),
        ]);

        $result = app(ZarinpalService::class)->requestPayment($order->fresh('user'));

        $this->assertSame('A00000000000000000000000000000000001', $result['authority']);
        $this->assertStringContainsString('sandbox.zarinpal.com/pg/StartPay/', $result['redirect_url']);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->url() === 'https://sandbox.zarinpal.com/pg/v4/payment/request.json'
                && $data['merchant_id'] === 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'
                && $data['amount'] === 1500000
                && $data['description'] !== ''
                && isset($data['callback_url'])
                && ($data['metadata']['mobile'] ?? null) === '09121234567';
        });
    }

    public function test_checkout_payment_redirects_to_zarinpal_when_configured(): void
    {
        StoreSetting::set('zarinpal_merchant_id', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', 'payment');
        StoreSetting::set('zarinpal_sandbox', '1', 'payment', 'boolean');
        StoreSetting::set('zarinpal_callback_base_url', 'https://myshop.test', 'payment');

        ['user' => $user, 'order' => $order] = $this->seedPendingOrder();

        Http::fake([
            'sandbox.zarinpal.com/*' => Http::response([
                'data' => [
                    'code' => 100,
                    'authority' => 'A00000000000000000000000000000000002',
                    'fee_type' => 'Merchant',
                    'fee' => 0,
                ],
                'errors' => [],
            ], 200),
        ]);

        $this->actingAs($user)
            ->get(route('checkout.payment', $order))
            ->assertRedirect('https://sandbox.zarinpal.com/pg/StartPay/A00000000000000000000000000000000002');

        $this->assertSame('A00000000000000000000000000000000002', $order->fresh()->payment_authority);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return str_contains($request->url(), 'payment/request.json')
                && ($data['callback_url'] ?? null) === 'https://myshop.test/payment/callback';
        });
    }

    public function test_payment_callback_verifies_and_marks_order_paid(): void
    {
        StoreSetting::set('zarinpal_merchant_id', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', 'payment');
        StoreSetting::set('zarinpal_sandbox', '1', 'payment', 'boolean');

        ['user' => $user, 'order' => $order] = $this->seedPendingOrder();
        $order->update(['payment_authority' => 'A00000000000000000000000000000000003']);

        Http::fake([
            'sandbox.zarinpal.com/pg/v4/payment/verify.json' => Http::response([
                'data' => [
                    'code' => 100,
                    'message' => 'Verified',
                    'card_hash' => 'HASH',
                    'card_pan' => '502229******5995',
                    'ref_id' => 201,
                    'fee_type' => 'Merchant',
                    'fee' => 0,
                ],
                'errors' => [],
            ], 200),
        ]);

        $this->actingAs($user)
            ->get(route('payment.callback', [
                'Authority' => 'A00000000000000000000000000000000003',
                'Status' => 'OK',
            ]))
            ->assertRedirect(route('user.orders.show', $order));

        $order->refresh();
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertSame('201', $order->payment_ref);
        $this->assertNotNull($order->paid_at);
    }

    public function test_verify_accepts_code_101_already_verified(): void
    {
        StoreSetting::set('zarinpal_merchant_id', 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', 'payment');
        StoreSetting::set('zarinpal_sandbox', '1', 'payment', 'boolean');

        Http::fake([
            'sandbox.zarinpal.com/pg/v4/payment/verify.json' => Http::response([
                'data' => [
                    'code' => 101,
                    'message' => 'Verified',
                    'ref_id' => 202,
                    'card_pan' => '502229******5995',
                    'fee_type' => 'Merchant',
                    'fee' => 0,
                ],
                'errors' => [],
            ], 200),
        ]);

        $result = app(ZarinpalService::class)->verifyPayment('A00000000000000000000000000000000004', 150000);

        $this->assertTrue($result['already_verified']);
        $this->assertSame(202, $result['ref_id']);
    }

    public function test_callback_nok_marks_payment_failed(): void
    {
        ['user' => $user, 'order' => $order] = $this->seedPendingOrder();
        $order->update(['payment_authority' => 'A00000000000000000000000000000000005']);

        $this->actingAs($user)
            ->get(route('payment.callback', [
                'Authority' => 'A00000000000000000000000000000000005',
                'Status' => 'NOK',
            ]))
            ->assertRedirect(route('user.orders.index'));

        $this->assertSame(PaymentStatus::Failed, $order->fresh()->payment_status);
        $this->assertSame(OrderStatus::Failed, $order->fresh()->status);
    }

    public function test_env_merchant_used_when_admin_setting_empty(): void
    {
        config(['zarinpal.merchant_id' => 'env-merchant-id-xxxxxxxxxxxxxxxx']);
        config(['zarinpal.sandbox' => false]);

        $this->assertSame('env-merchant-id-xxxxxxxxxxxxxxxx', StoreSettings::zarinpalMerchantId());
        $this->assertFalse(StoreSettings::zarinpalSandbox());
        $this->assertTrue(app(ZarinpalService::class)->isConfigured());
    }

    public function test_sandbox_without_merchant_still_redirects_to_zarinpal_gateway(): void
    {
        StoreSetting::query()->where('key', 'zarinpal_merchant_id')->delete();
        StoreSetting::set('zarinpal_sandbox', '1', 'payment', 'boolean');
        config([
            'zarinpal.merchant_id' => null,
            'zarinpal.sandbox_merchant_id' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
        ]);

        ['user' => $user, 'order' => $order] = $this->seedPendingOrder();

        Http::fake([
            'sandbox.zarinpal.com/*' => Http::response([
                'data' => [
                    'code' => 100,
                    'authority' => 'S00000000000000000000000000000000099',
                    'fee_type' => 'Merchant',
                    'fee' => 0,
                ],
                'errors' => [],
            ], 200),
        ]);

        $this->assertTrue(app(ZarinpalService::class)->isConfigured());
        $this->assertTrue(app(ZarinpalService::class)->isSandbox());

        $this->actingAs($user)
            ->get(route('checkout.payment', $order))
            ->assertRedirect('https://sandbox.zarinpal.com/pg/StartPay/S00000000000000000000000000000000099');

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->url() === 'https://sandbox.zarinpal.com/pg/v4/payment/request.json'
                && ($data['merchant_id'] ?? null) === 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';
        });

        $this->assertSame(PaymentStatus::Pending, $order->fresh()->payment_status);
        $this->assertNull($order->fresh()->payment_ref);
    }

    public function test_amount_in_rials_when_currency_is_rial(): void
    {
        StoreSetting::set('currency', 'ریال', 'appearance');

        $this->assertSame(150000, StoreSettings::zarinpalAmountInRials(150000));
    }
}
