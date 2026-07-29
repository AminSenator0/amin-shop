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

/**
 * تست سخت‌گیرانهٔ ملی‌پیامک: قرارداد API، امنیت رمز، اعتبارسنجی، شکست/موفقیت، نرمال‌سازی.
 */
class SmsMelipayamakStrictTest extends TestCase
{
    use RefreshDatabase;

    private function basePayload(array $extra = []): array
    {
        return array_merge([
            'store_name' => 'فروشگاه تست',
            'currency' => 'تومان',
            'color_preset' => StoreSettings::defaultColorPresetId(),
            'return_days' => 7,
            'active_tab' => 'sms',
        ], $extra);
    }

    private function configureMeli(array $overrides = []): void
    {
        $defaults = [
            'sms_driver' => 'melipayamak',
            'sms_mode' => 'simple',
            'sms_meli_username' => 'user1',
            'sms_meli_password' => 'pass1',
            'sms_meli_from' => '50004001',
        ];

        foreach (array_merge($defaults, $overrides) as $key => $value) {
            StoreSetting::set($key, (string) $value, 'sms');
        }
    }

    private function makeOrder(User $user, array $addressOverrides = []): Order
    {
        $category = Category::create(['name' => 'تست', 'slug' => 'sms-strict-cat', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'محصول',
            'slug' => 'sms-strict-product',
            'price' => 100000,
            'sku' => 'SMS-S1',
            'stock' => 5,
            'is_active' => true,
        ]);
        $shipping = ShippingMethod::create(['name' => 'پست', 'cost' => 0, 'is_active' => true]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-STRICT-1',
            'status' => OrderStatus::Paid,
            'payment_status' => PaymentStatus::Paid,
            'shipping_method_id' => $shipping->id,
            'subtotal' => 100000,
            'shipping_cost' => 0,
            'discount_amount' => 0,
            'total' => 100000,
            'tracking_code' => 'TRK;INJECT',
            'shipping_address' => array_merge([
                'full_name' => 'کاربر',
                'phone' => '09121234567',
                'province' => 'تهران',
                'city' => 'تهران',
                'address' => 'آدرس',
                'postal_code' => '1234567890',
            ], $addressOverrides),
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

    public function test_guest_cannot_open_sms_settings(): void
    {
        $this->get(route('admin.settings.edit', ['tab' => 'sms']))
            ->assertRedirect(route('admin.login'));
    }

    public function test_rejects_invalid_sms_driver(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->from(route('admin.settings.edit', ['tab' => 'sms']))
            ->put(route('admin.settings.update'), $this->basePayload([
                'sms_driver' => 'twilio',
                'sms_mode' => 'simple',
            ]))
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'sms']))
            ->assertSessionHasErrors('sms_driver');
    }

    public function test_rejects_non_numeric_meli_body_id(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->from(route('admin.settings.edit', ['tab' => 'sms']))
            ->put(route('admin.settings.update'), $this->basePayload([
                'sms_driver' => 'melipayamak',
                'sms_mode' => 'lookup',
                'sms_meli_username' => 'u',
                'sms_meli_password' => 'p',
                'sms_meli_body_order_shipped' => '12ab',
            ]))
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'sms']))
            ->assertSessionHasErrors('sms_meli_body_order_shipped');
    }

    public function test_persian_digits_in_body_id_normalized_and_saved(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->basePayload([
                'sms_driver' => 'melipayamak',
                'sms_mode' => 'lookup',
                'sms_meli_username' => 'u',
                'sms_meli_password' => 'p',
                'sms_meli_body_order_shipped' => '۱۲۳۴۵',
            ]))
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'sms']))
            ->assertSessionDoesntHaveErrors();

        $this->assertSame(12345, StoreSettings::smsMeliBodyId('order_shipped'));
    }

    public function test_empty_password_does_not_wipe_existing_password(): void
    {
        StoreSetting::set('sms_meli_password', 'keep-secret', 'sms');
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->basePayload([
                'sms_driver' => 'melipayamak',
                'sms_mode' => 'simple',
                'sms_meli_username' => 'userkeep',
                'sms_meli_password' => '',
                'sms_meli_from' => '5000',
            ]))
            ->assertRedirect();

        $this->assertSame('keep-secret', StoreSettings::smsMeliPassword());
        $this->assertSame('userkeep', StoreSettings::smsMeliUsername());
    }

    public function test_clear_password_checkbox_wipes_password(): void
    {
        StoreSetting::set('sms_meli_password', 'wipe-me', 'sms');
        StoreSetting::set('sms_meli_username', 'user1', 'sms');
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->basePayload([
                'sms_driver' => 'melipayamak',
                'sms_mode' => 'simple',
                'sms_meli_username' => 'user1',
                'sms_meli_password' => '',
                'sms_clear_meli_password' => '1',
                'sms_meli_from' => '5000',
            ]))
            ->assertRedirect();

        $this->assertSame('', StoreSettings::smsMeliPassword());
        $this->assertFalse(StoreSettings::smsIsConfigured());
    }

    public function test_saving_appearance_tab_does_not_wipe_meli_credentials(): void
    {
        StoreSetting::set('sms_driver', 'melipayamak', 'sms');
        StoreSetting::set('sms_meli_username', 'persist-user', 'sms');
        StoreSetting::set('sms_meli_password', 'persist-pass', 'sms');
        StoreSetting::set('sms_meli_from', '50009999', 'sms');
        StoreSetting::set('sms_meli_body_order_shipped', '555', 'sms');
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'store_name' => 'فروشگاه تست',
                'currency' => 'تومان',
                'color_preset' => StoreSettings::defaultColorPresetId(),
                'return_days' => 7,
                'active_tab' => 'identity',
            ])
            ->assertRedirect();

        $this->assertSame('melipayamak', StoreSettings::smsDriver());
        $this->assertSame('persist-user', StoreSettings::smsMeliUsername());
        $this->assertSame('persist-pass', StoreSettings::smsMeliPassword());
        $this->assertSame('50009999', StoreSettings::smsMeliFrom());
        $this->assertSame(555, StoreSettings::smsMeliBodyId('order_shipped'));
    }

    public function test_env_meli_credentials_used_when_admin_empty(): void
    {
        config([
            'services.sms.driver' => 'melipayamak',
            'services.sms.melipayamak.username' => 'env-user',
            'services.sms.melipayamak.password' => 'env-pass',
            'services.sms.melipayamak.from' => '50001111',
        ]);

        $this->assertSame('melipayamak', StoreSettings::smsDriver());
        $this->assertSame('env-user', StoreSettings::smsMeliUsername());
        $this->assertSame('env-pass', StoreSettings::smsMeliPassword());
        $this->assertSame('50001111', StoreSettings::smsMeliFrom());
        $this->assertTrue(StoreSettings::smsIsConfigured());
    }

    public function test_send_rejects_empty_message_and_invalid_phone(): void
    {
        $this->configureMeli();
        Http::fake();

        $this->assertFalse(app(SmsService::class)->send('09121234567', ''));
        $this->assertFalse(app(SmsService::class)->send('123', 'پیام'));
        $this->assertFalse(app(SmsService::class)->send('', 'پیام'));

        Http::assertNothingSent();
    }

    public function test_send_normalizes_plus98_phone(): void
    {
        $this->configureMeli();
        Http::fake([
            'rest.payamak-panel.com/*' => Http::response([
                'Value' => '1234567890123456789',
                'RetStatus' => 1,
                'StrRetStatus' => 'Ok',
            ], 200),
        ]);

        $this->assertTrue(app(SmsService::class)->send('+989121234567', 'تست'));

        Http::assertSent(fn ($request) => $request['to'] === '09121234567');
    }

    public function test_simple_send_requires_from_line(): void
    {
        $this->configureMeli(['sms_meli_from' => '']);
        Http::fake();
        Log::spy();

        $this->assertFalse(app(SmsService::class)->send('09121234567', 'پیام'));
        Http::assertNothingSent();
        Log::shouldHaveReceived('error')->withArgs(fn ($msg) => str_contains($msg, 'sender line'));
    }

    public function test_unconfigured_provider_does_not_hit_network(): void
    {
        StoreSetting::set('sms_driver', 'melipayamak', 'sms');
        StoreSetting::set('sms_meli_username', '', 'sms');
        StoreSetting::set('sms_meli_password', '', 'sms');
        Http::fake();
        Log::spy();

        $user = User::factory()->create();
        $order = $this->makeOrder($user);

        $this->assertFalse(
            app(OrderService::class)->notifyStatusChange($order, OrderStatus::Processing, true, false)
        );
        Http::assertNothingSent();
        Log::shouldHaveReceived('error')->withArgs(fn ($msg) => str_contains($msg, 'not configured'));
    }

    public function test_simple_payload_matches_official_rest_contract(): void
    {
        $this->configureMeli();
        Http::fake([
            'rest.payamak-panel.com/*' => Http::response([
                'Value' => '1234567890123456789',
                'RetStatus' => 1,
                'StrRetStatus' => 'Ok',
            ], 200),
        ]);

        $this->assertTrue(app(SmsService::class)->send('09121234567', 'متن رسمی'));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://rest.payamak-panel.com/api/SendSMS/SendSMS'
                && $request->method() === 'POST'
                && str_contains((string) $request->header('Content-Type')[0], 'application/x-www-form-urlencoded')
                && $request['username'] === 'user1'
                && $request['password'] === 'pass1'
                && $request['to'] === '09121234567'
                && $request['from'] === '50004001'
                && $request['text'] === 'متن رسمی'
                && ($request['isFlash'] === 'false' || $request['isFlash'] === false);
        });
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'kavenegar'));
    }

    public function test_success_when_retstatus_ok(): void
    {
        $this->configureMeli();
        Http::fake([
            'rest.payamak-panel.com/*' => Http::response([
                'Value' => '0',
                'RetStatus' => 1,
                'StrRetStatus' => 'Ok',
            ], 200),
        ]);

        $this->assertTrue(app(SmsService::class)->send('09121234567', 'ok'));
    }

    public function test_success_when_large_recid_even_if_retstatus_missing(): void
    {
        $this->configureMeli();
        Http::fake([
            'rest.payamak-panel.com/*' => Http::response([
                'Value' => '9876543210987654321',
            ], 200),
        ]);

        $this->assertTrue(app(SmsService::class)->send('09121234567', 'ok'));
    }

    public function test_fails_on_small_error_value_codes(): void
    {
        $this->configureMeli();
        Http::fake([
            'rest.payamak-panel.com/*' => Http::response([
                'Value' => '11',
                'RetStatus' => 0,
                'StrRetStatus' => 'Error',
            ], 200),
        ]);
        Log::spy();

        $this->assertFalse(app(SmsService::class)->send('09121234567', 'fail'));
        Log::shouldHaveReceived('error')->withArgs(fn ($msg) => str_contains($msg, 'Melipayamak'));
    }

    public function test_fails_on_http_error(): void
    {
        $this->configureMeli();
        Http::fake([
            'rest.payamak-panel.com/*' => Http::response('Gateway Timeout', 504),
        ]);

        $this->assertFalse(app(SmsService::class)->send('09121234567', 'fail'));
    }

    public function test_lookup_without_body_id_falls_back_to_simple(): void
    {
        $this->configureMeli([
            'sms_mode' => 'lookup',
            'sms_meli_body_order_processing' => '0',
        ]);
        Http::fake([
            'rest.payamak-panel.com/*' => Http::response([
                'Value' => '1234567890123456789',
                'RetStatus' => 1,
            ], 200),
        ]);

        $user = User::factory()->create();
        $order = $this->makeOrder($user);

        $this->assertTrue(
            app(OrderService::class)->notifyStatusChange($order, OrderStatus::Processing, true, false)
        );

        Http::assertSent(fn ($r) => $r->url() === 'https://rest.payamak-panel.com/api/SendSMS/SendSMS');
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'BaseServiceNumber'));
    }

    public function test_lookup_without_body_id_and_without_from_fails_safely(): void
    {
        $this->configureMeli([
            'sms_mode' => 'lookup',
            'sms_meli_from' => '',
            'sms_meli_body_order_processing' => '',
        ]);
        Http::fake();

        $user = User::factory()->create();
        $order = $this->makeOrder($user);

        $this->assertFalse(
            app(OrderService::class)->notifyStatusChange($order, OrderStatus::Processing, true, false)
        );
        Http::assertNothingSent();
    }

    public function test_pattern_sanitizes_semicolon_in_variables(): void
    {
        $this->configureMeli([
            'sms_mode' => 'lookup',
            'sms_meli_body_order_shipped' => '7788',
        ]);
        StoreSetting::set('store_name', 'فروشگاه;'.' بد', 'appearance');

        Http::fake([
            'rest.payamak-panel.com/*' => Http::response([
                'Value' => '1234567890123456789',
                'RetStatus' => 1,
            ], 200),
        ]);

        $user = User::factory()->create();
        $order = $this->makeOrder($user);

        $this->assertTrue(
            app(OrderService::class)->notifyStatusChange($order, OrderStatus::Shipped, true, false)
        );

        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://rest.payamak-panel.com/api/SendSMS/BaseServiceNumber') {
                return false;
            }

            $parts = explode(';', (string) $request['text']);

            // تزریق ; نباید فیلد اضافه بسازد — دقیقاً ۳ متغیر
            return count($parts) === 3
                && (int) $request['bodyId'] === 7788
                && $request['to'] === '09121234567'
                && $parts[0] === 'فروشگاه، بد'
                && $parts[1] === 'ORD-STRICT-1'
                && $parts[2] === 'TRK،INJECT'
                && ! array_key_exists('from', $request->data());
        });
    }

    public function test_pattern_never_sends_to_kavenegar_when_meli_selected(): void
    {
        $this->configureMeli([
            'sms_mode' => 'lookup',
            'sms_meli_body_order_delivered' => '9001',
        ]);
        StoreSetting::set('sms_kavenegar_api_key', 'should-not-use', 'sms');
        StoreSetting::set('sms_template_order_delivered', 'orderdelivered', 'sms');
        StoreSetting::set('store_name', 'فروشگاه من', 'appearance');

        Http::fake([
            'rest.payamak-panel.com/*' => Http::response(['RetStatus' => 1, 'Value' => '1'], 200),
            'api.kavenegar.com/*' => Http::response(['return' => ['status' => 200]], 200),
        ]);

        $user = User::factory()->create();
        $order = $this->makeOrder($user);

        $this->assertTrue(
            app(OrderService::class)->notifyStatusChange($order, OrderStatus::Delivered, true, false)
        );

        Http::assertSent(fn ($r) => str_contains($r->url(), 'BaseServiceNumber'));
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'kavenegar'));
    }

    public function test_failure_logs_must_not_include_password(): void
    {
        $this->configureMeli(['sms_meli_password' => 'ultra-secret-pass-xyz']);
        Http::fake([
            'rest.payamak-panel.com/*' => Http::response([
                'Value' => '0',
                'RetStatus' => 0,
                'StrRetStatus' => 'Error',
            ], 200),
        ]);

        Log::spy();
        app(SmsService::class)->send('09121234567', 'x');

        Log::shouldHaveReceived('error')->withArgs(function ($message, $context = []) {
            $blob = $message.json_encode($context);

            return str_contains($message, 'Melipayamak')
                && ! str_contains($blob, 'ultra-secret-pass-xyz');
        });
    }

    public function test_password_masked_on_settings_page_never_full_value(): void
    {
        StoreSetting::set('sms_driver', 'melipayamak', 'sms');
        StoreSetting::set('sms_meli_password', 'AbCdEfGhIjKlMnOp', 'sms');
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $html = $this->actingAs($admin)
            ->get(route('admin.settings.edit', ['tab' => 'sms']))
            ->assertOk()
            ->assertDontSee('AbCdEfGhIjKlMnOp')
            ->assertDontSee('value="AbCd')
            ->getContent();

        $this->assertStringNotContainsString('AbCdEfGhIjKlMnOp', $html);
        $this->assertMatchesRegularExpression('/type="password"[^>]*name="sms_meli_password"/', $html);
    }

    public function test_console_api_key_mode_is_configured_without_username(): void
    {
        StoreSetting::set('sms_driver', 'melipayamak', 'sms');
        StoreSetting::set('sms_meli_auth', 'api_key', 'sms');
        StoreSetting::set('sms_meli_api_key', 'console-key-1', 'sms');
        StoreSetting::set('sms_meli_username', '', 'sms');
        StoreSetting::set('sms_meli_password', '', 'sms');

        $this->assertTrue(StoreSettings::smsIsConfigured());
        $this->assertTrue(app(SmsService::class)->isConfigured());
    }

    public function test_console_api_key_not_leaked_on_settings_html(): void
    {
        StoreSetting::set('sms_driver', 'melipayamak', 'sms');
        StoreSetting::set('sms_meli_auth', 'api_key', 'sms');
        StoreSetting::set('sms_meli_api_key', 'ConsoleSecretKeyXYZ999', 'sms');
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.settings.edit', ['tab' => 'sms']))
            ->assertOk()
            ->assertDontSee('ConsoleSecretKeyXYZ999')
            ->assertSee('کلید API کنسول');
    }

    public function test_empty_console_api_key_does_not_wipe_existing(): void
    {
        StoreSetting::set('sms_meli_api_key', 'keep-console-key', 'sms');
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->basePayload([
                'sms_driver' => 'melipayamak',
                'sms_meli_auth' => 'api_key',
                'sms_mode' => 'simple',
                'sms_meli_api_key' => '',
                'sms_meli_from' => '50004001',
            ]))
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'sms']));

        $this->assertSame('keep-console-key', StoreSettings::smsMeliApiKey());
    }

    public function test_customer_cannot_steal_meli_settings(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->put(route('admin.settings.update'), $this->basePayload([
                'sms_driver' => 'melipayamak',
                'sms_meli_username' => 'hacker',
                'sms_meli_password' => 'hacked',
            ]))
            ->assertRedirect(route('user.dashboard'));

        $this->assertNull(StoreSetting::query()->where('key', 'sms_meli_username')->value('value'));
        $this->assertNull(StoreSetting::query()->where('key', 'sms_meli_password')->value('value'));
    }
}
