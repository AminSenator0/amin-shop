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
use App\Services\OrderService;
use App\Services\SmsService;
use App\Support\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SmsSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(User $user): Order
    {
        $category = Category::create(['name' => 'تست', 'slug' => 'sms-cat', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'محصول',
            'slug' => 'sms-product',
            'price' => 100000,
            'sku' => 'SMS-1',
            'stock' => 5,
            'is_active' => true,
        ]);
        $shipping = ShippingMethod::create(['name' => 'پست', 'cost' => 0, 'is_active' => true]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-SMS-TEST1',
            'status' => OrderStatus::Paid,
            'payment_status' => PaymentStatus::Paid,
            'shipping_method_id' => $shipping->id,
            'subtotal' => 100000,
            'shipping_cost' => 0,
            'discount_amount' => 0,
            'total' => 100000,
            'tracking_code' => 'TRK123',
            'shipping_address' => [
                'full_name' => 'کاربر',
                'phone' => '09121234567',
                'province' => 'تهران',
                'city' => 'تهران',
                'address' => 'آدرس',
                'postal_code' => '1234567890',
            ],
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'price' => 100000,
            'quantity' => 1,
            'total' => 100000,
        ]);

        return $order;
    }

    public function test_admin_sms_settings_page_is_clear_and_helpful(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.settings.edit', ['tab' => 'sms']))
            ->assertOk()
            ->assertSee('ملی‌پیامک')
            ->assertSee('کاوه‌نگار')
            ->assertSee('متن نمونه برای ثبت در ملی‌پیامک')
            ->assertSee('متن نمونه برای ثبت در کاوه‌نگار')
            ->assertSee('{0}')
            ->assertSee('%token10');
    }

    public function test_admin_can_save_kavenegar_simple_settings(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $preset = StoreSettings::defaultColorPresetId();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'store_name' => 'فروشگاه تست',
                'currency' => 'تومان',
                'color_preset' => $preset,
                'return_days' => 7,
                'sms_driver' => 'kavenegar',
                'sms_mode' => 'simple',
                'sms_kavenegar_api_key' => 'test-api-key-123456',
                'sms_kavenegar_sender' => '10004346',
                'active_tab' => 'sms',
            ])
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'sms']));

        $this->assertSame('kavenegar', StoreSettings::smsDriver());
        $this->assertSame('simple', StoreSettings::smsMode());
        $this->assertSame('test-api-key-123456', StoreSettings::smsKavenegarApiKey());
        $this->assertSame('10004346', StoreSettings::smsKavenegarSender());
        $this->assertTrue(StoreSettings::smsIsConfigured());
    }

    public function test_admin_can_save_lookup_templates(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $preset = StoreSettings::defaultColorPresetId();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'store_name' => 'فروشگاه تست',
                'currency' => 'تومان',
                'color_preset' => $preset,
                'return_days' => 7,
                'sms_driver' => 'kavenegar',
                'sms_mode' => 'lookup',
                'sms_kavenegar_api_key' => 'lookup-key-999',
                'sms_template_order_processing' => 'orderprocessing',
                'sms_template_order_shipped' => 'ordershipped',
                'sms_template_order_delivered' => 'orderdelivered',
                'sms_template_message_reply_notice' => 'messagereply',
                'sms_template_message_reply_body' => 'messagereplybody',
                'active_tab' => 'sms',
            ])
            ->assertRedirect();

        $this->assertSame('lookup', StoreSettings::smsMode());
        $this->assertSame('ordershipped', StoreSettings::smsTemplate('order_shipped'));
    }

    public function test_empty_api_key_does_not_wipe_existing_key(): void
    {
        StoreSetting::set('sms_kavenegar_api_key', 'keep-me-key', 'sms');
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $preset = StoreSettings::defaultColorPresetId();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'store_name' => 'فروشگاه تست',
                'currency' => 'تومان',
                'color_preset' => $preset,
                'return_days' => 7,
                'sms_driver' => 'kavenegar',
                'sms_mode' => 'simple',
                'sms_kavenegar_api_key' => '',
                'sms_kavenegar_sender' => '1000',
                'active_tab' => 'sms',
            ])
            ->assertRedirect();

        $this->assertSame('keep-me-key', StoreSettings::smsKavenegarApiKey());
    }

    public function test_customer_cannot_change_sms_settings(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->put(route('admin.settings.update'), [
                'store_name' => 'هک',
                'currency' => 'تومان',
                'color_preset' => 'teal',
                'return_days' => 7,
                'sms_driver' => 'kavenegar',
                'sms_kavenegar_api_key' => 'stolen-key',
            ])
            ->assertRedirect(route('user.dashboard'));

        $this->assertNull(StoreSetting::query()->where('key', 'sms_kavenegar_api_key')->value('value'));
    }

    public function test_log_driver_writes_to_log(): void
    {
        StoreSetting::set('sms_driver', 'log', 'sms');
        Log::spy();

        $ok = app(SmsService::class)->send('09121234567', 'سلام تست');

        $this->assertTrue($ok);
        Log::shouldHaveReceived('info')->withArgs(function ($message, $context) {
            return $message === 'SMS sent'
                && $context['phone'] === '09121234567'
                && $context['message'] === 'سلام تست';
        });
    }

    public function test_simple_mode_calls_official_send_endpoint(): void
    {
        StoreSetting::set('sms_driver', 'kavenegar', 'sms');
        StoreSetting::set('sms_mode', 'simple', 'sms');
        StoreSetting::set('sms_kavenegar_api_key', 'KEY123', 'sms');
        StoreSetting::set('sms_kavenegar_sender', '10004346', 'sms');

        Http::fake([
            'api.kavenegar.com/*' => Http::response([
                'return' => ['status' => 200, 'message' => 'تایید شد'],
                'entries' => [['messageid' => 1]],
            ], 200),
        ]);

        $this->assertTrue(app(SmsService::class)->send('09121234567', 'پیام تست'));

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/v1/KEY123/sms/send.json')
                && $request['receptor'] === '09121234567'
                && $request['sender'] === '10004346'
                && $request['message'] === 'پیام تست';
        });
    }

    public function test_lookup_mode_calls_verify_lookup_with_tokens(): void
    {
        StoreSetting::set('sms_driver', 'kavenegar', 'sms');
        StoreSetting::set('sms_mode', 'lookup', 'sms');
        StoreSetting::set('sms_kavenegar_api_key', 'KEY999', 'sms');
        StoreSetting::set('sms_template_order_shipped', 'ordershipped', 'sms');
        StoreSetting::set('store_name', 'فروشگاه من', 'appearance');

        Http::fake([
            'api.kavenegar.com/*' => Http::response([
                'return' => ['status' => 200, 'message' => 'تایید شد'],
                'entries' => [['messageid' => 2]],
            ], 200),
        ]);

        $user = User::factory()->create();
        $order = $this->makeOrder($user);

        $ok = app(OrderService::class)->notifyStatusChange($order, OrderStatus::Shipped, true, false);

        $this->assertTrue($ok);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/v1/KEY999/verify/lookup.json')
                && $request['template'] === 'ordershipped'
                && $request['token'] === 'ORD-SMS-TEST1'
                && $request['token2'] === 'TRK123'
                && $request['receptor'] === '09121234567';
        });
    }

    public function test_lookup_falls_back_to_simple_when_template_missing(): void
    {
        StoreSetting::set('sms_driver', 'kavenegar', 'sms');
        StoreSetting::set('sms_mode', 'lookup', 'sms');
        StoreSetting::set('sms_kavenegar_api_key', 'KEY888', 'sms');
        StoreSetting::set('sms_kavenegar_sender', '10004346', 'sms');
        StoreSetting::set('sms_template_order_processing', '', 'sms');

        Http::fake([
            'api.kavenegar.com/*' => Http::response([
                'return' => ['status' => 200, 'message' => 'تایید شد'],
                'entries' => [],
            ], 200),
        ]);

        $user = User::factory()->create();
        $order = $this->makeOrder($user);

        $this->assertTrue(app(OrderService::class)->notifyStatusChange($order, OrderStatus::Processing, true, false));

        Http::assertSent(fn ($request) => str_contains($request->url(), 'sms/send.json'));
    }

    public function test_env_api_key_used_when_admin_empty(): void
    {
        config(['services.sms.driver' => 'kavenegar']);
        config(['services.sms.kavenegar.api_key' => 'env-sms-key']);
        config(['services.sms.kavenegar.sender' => '10001111']);

        $this->assertSame('kavenegar', StoreSettings::smsDriver());
        $this->assertSame('env-sms-key', StoreSettings::smsKavenegarApiKey());
        $this->assertSame('10001111', StoreSettings::smsKavenegarSender());
    }

    public function test_admin_can_save_melipayamak_settings(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $preset = StoreSettings::defaultColorPresetId();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'store_name' => 'فروشگاه تست',
                'currency' => 'تومان',
                'color_preset' => $preset,
                'return_days' => 7,
                'sms_driver' => 'melipayamak',
                'sms_mode' => 'lookup',
                'sms_meli_username' => 'meliuser',
                'sms_meli_password' => 'melipass-secret',
                'sms_meli_from' => '50004001',
                'sms_meli_body_order_shipped' => '99887',
                'active_tab' => 'sms',
            ])
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'sms']));

        $this->assertSame('melipayamak', StoreSettings::smsDriver());
        $this->assertSame('meliuser', StoreSettings::smsMeliUsername());
        $this->assertSame('melipass-secret', StoreSettings::smsMeliPassword());
        $this->assertSame('50004001', StoreSettings::smsMeliFrom());
        $this->assertSame(99887, StoreSettings::smsMeliBodyId('order_shipped'));
        $this->assertTrue(StoreSettings::smsIsConfigured());
    }

    public function test_melipayamak_simple_calls_official_send_endpoint(): void
    {
        StoreSetting::set('sms_driver', 'melipayamak', 'sms');
        StoreSetting::set('sms_mode', 'simple', 'sms');
        StoreSetting::set('sms_meli_username', 'user1', 'sms');
        StoreSetting::set('sms_meli_password', 'pass1', 'sms');
        StoreSetting::set('sms_meli_from', '50004001', 'sms');

        Http::fake([
            'rest.payamak-panel.com/*' => Http::response([
                'Value' => '1234567890123456789',
                'RetStatus' => 1,
                'StrRetStatus' => 'Ok',
            ], 200),
        ]);

        $this->assertTrue(app(SmsService::class)->send('09121234567', 'پیام تست ملی'));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://rest.payamak-panel.com/api/SendSMS/SendSMS'
                && $request['username'] === 'user1'
                && $request['password'] === 'pass1'
                && $request['to'] === '09121234567'
                && $request['from'] === '50004001'
                && $request['text'] === 'پیام تست ملی'
                && ($request['isFlash'] === 'false' || $request['isFlash'] === false);
        });
    }

    public function test_melipayamak_pattern_calls_base_service_number(): void
    {
        StoreSetting::set('sms_driver', 'melipayamak', 'sms');
        StoreSetting::set('sms_mode', 'lookup', 'sms');
        StoreSetting::set('sms_meli_username', 'user1', 'sms');
        StoreSetting::set('sms_meli_password', 'pass1', 'sms');
        StoreSetting::set('sms_meli_body_order_shipped', '7788', 'sms');
        StoreSetting::set('store_name', 'فروشگاه من', 'appearance');

        Http::fake([
            'rest.payamak-panel.com/*' => Http::response([
                'Value' => '9876543210987654321',
                'RetStatus' => 1,
                'StrRetStatus' => 'Ok',
            ], 200),
        ]);

        $user = User::factory()->create();
        $order = $this->makeOrder($user);

        $ok = app(OrderService::class)->notifyStatusChange($order, OrderStatus::Shipped, true, false);

        $this->assertTrue($ok);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber'
                && $request['bodyId'] == 7788
                && $request['to'] === '09121234567'
                && $request['text'] === 'فروشگاه من;ORD-SMS-TEST1;TRK123';
        });
    }

    public function test_admin_can_save_melipayamak_console_api_key(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'store_name' => 'فروشگاه تست',
                'currency' => 'تومان',
                'color_preset' => StoreSettings::defaultColorPresetId(),
                'return_days' => 7,
                'sms_driver' => 'melipayamak',
                'sms_meli_auth' => 'api_key',
                'sms_mode' => 'lookup',
                'sms_meli_api_key' => 'console-token-secret',
                'sms_meli_from' => '50004001',
                'sms_meli_body_auth_otp' => '12345',
                'active_tab' => 'sms',
            ])
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'sms']));

        $this->assertSame('api_key', StoreSettings::smsMeliAuth());
        $this->assertSame('console-token-secret', StoreSettings::smsMeliApiKey());
        $this->assertTrue(StoreSettings::smsIsConfigured());
    }

    public function test_melipayamak_console_simple_calls_send_simple_endpoint(): void
    {
        StoreSetting::set('sms_driver', 'melipayamak', 'sms');
        StoreSetting::set('sms_meli_auth', 'api_key', 'sms');
        StoreSetting::set('sms_mode', 'simple', 'sms');
        StoreSetting::set('sms_meli_api_key', 'tok123', 'sms');
        StoreSetting::set('sms_meli_from', '50004001', 'sms');

        Http::fake([
            'console.melipayamak.com/*' => Http::response([
                'recId' => '3741437414',
                'status' => '',
            ], 200),
        ]);

        $this->assertTrue(app(SmsService::class)->send('09121234567', 'پیام کنسول'));

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->url() === 'https://console.melipayamak.com/api/send/simple/tok123'
                && ($data['to'] ?? null) === '09121234567'
                && ($data['from'] ?? null) === '50004001'
                && ($data['text'] ?? null) === 'پیام کنسول';
        });
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'rest.payamak-panel.com'));
    }

    public function test_melipayamak_console_pattern_calls_send_shared_endpoint(): void
    {
        StoreSetting::set('sms_driver', 'melipayamak', 'sms');
        StoreSetting::set('sms_meli_auth', 'api_key', 'sms');
        StoreSetting::set('sms_mode', 'lookup', 'sms');
        StoreSetting::set('sms_meli_api_key', 'tok123', 'sms');
        StoreSetting::set('sms_meli_body_order_shipped', '7788', 'sms');
        StoreSetting::set('store_name', 'فروشگاه من', 'appearance');

        Http::fake([
            'console.melipayamak.com/*' => Http::response([
                'recId' => '3741437414',
                'status' => '',
            ], 200),
        ]);

        $user = User::factory()->create();
        $order = $this->makeOrder($user);

        $ok = app(OrderService::class)->notifyStatusChange($order, OrderStatus::Shipped, true, false);

        $this->assertTrue($ok);
        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->url() === 'https://console.melipayamak.com/api/send/shared/tok123'
                && ($data['bodyId'] ?? null) == 7788
                && ($data['to'] ?? null) === '09121234567'
                && ($data['args'] ?? null) === ['فروشگاه من', 'ORD-SMS-TEST1', 'TRK123'];
        });
    }

    public function test_meli_password_not_leaked_on_settings_html(): void
    {
        StoreSetting::set('sms_driver', 'melipayamak', 'sms');
        StoreSetting::set('sms_meli_password', 'super-secret-meli-pass', 'sms');
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.settings.edit', ['tab' => 'sms']))
            ->assertOk()
            ->assertDontSee('super-secret-meli-pass');
    }

    public function test_api_key_not_leaked_on_settings_html(): void
    {
        StoreSetting::set('sms_kavenegar_api_key', 'super-secret-api-key-xyz', 'sms');
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.settings.edit', ['tab' => 'sms']))
            ->assertOk()
            ->assertDontSee('super-secret-api-key-xyz')
            ->assertSee('supe');
    }
}
