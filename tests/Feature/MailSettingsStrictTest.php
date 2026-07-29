<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderStatusMail;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\OrderService;
use App\Support\StoreSettings;
use Database\Seeders\StoreSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * تست سخت‌گیرانهٔ تنظیمات ایمیل:
 * دسترسی، اعتبارسنجی، امنیت رمز، پایداری هنگام ذخیرهٔ تب‌های دیگر،
 * اولویت پنل بر .env، نگاشت encryption، applyMailConfig، و ارسال واقعی سفارش.
 */
class MailSettingsStrictTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function basePayload(array $extra = []): array
    {
        return array_merge([
            'store_name' => 'فروشگاه تست',
            'currency' => 'تومان',
            'color_preset' => StoreSettings::defaultColorPresetId(),
            'return_days' => 7,
            'active_tab' => 'mail',
        ], $extra);
    }

    private function configureSmtp(array $overrides = []): void
    {
        $defaults = [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.strict.test',
            'mail_port' => '587',
            'mail_username' => 'smtp-user',
            'mail_password' => 'smtp-secret',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'from@strict.test',
            'mail_from_name' => 'فروشگاه سخت‌گیر',
        ];

        foreach (array_merge($defaults, $overrides) as $key => $value) {
            StoreSetting::set($key, (string) $value, 'mail');
        }

        StoreSettings::applyMailConfig();
    }

    private function makeOrder(User $user): Order
    {
        $category = Category::create(['name' => 'تست', 'slug' => 'mail-strict-cat', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'محصول ایمیل',
            'slug' => 'mail-strict-product',
            'price' => 150000,
            'sku' => 'MAIL-S1',
            'stock' => 5,
            'is_active' => true,
        ]);
        $shipping = ShippingMethod::create(['name' => 'پست', 'cost' => 0, 'is_active' => true]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-MAIL-STRICT',
            'status' => OrderStatus::Paid,
            'payment_status' => PaymentStatus::Paid,
            'shipping_method_id' => $shipping->id,
            'subtotal' => 150000,
            'shipping_cost' => 0,
            'discount_amount' => 0,
            'total' => 150000,
            'tracking_code' => 'TRK-MAIL-1',
            'shipping_address' => [
                'full_name' => 'خریدار',
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
            'price' => 150000,
            'quantity' => 1,
            'total' => 150000,
        ]);

        return $order;
    }

    // ─── دسترسی ───────────────────────────────────────────────

    public function test_guest_cannot_open_mail_settings(): void
    {
        $this->get(route('admin.settings.edit', ['tab' => 'mail']))
            ->assertRedirect(route('admin.login'));
    }

    public function test_guest_cannot_update_mail_settings(): void
    {
        $this->put(route('admin.settings.update'), $this->basePayload([
            'mail_mailer' => 'smtp',
            'mail_host' => 'evil.example.com',
        ]))->assertRedirect(route('admin.login'));

        $this->assertNull(StoreSetting::query()->where('key', 'mail_host')->value('value'));
    }

    public function test_customer_cannot_open_or_update_mail_settings(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get(route('admin.settings.edit', ['tab' => 'mail']))
            ->assertRedirect(route('user.dashboard'));

        $this->actingAs($customer)
            ->put(route('admin.settings.update'), $this->basePayload([
                'mail_mailer' => 'smtp',
                'mail_host' => 'evil.example.com',
                'mail_password' => 'stolen',
            ]))
            ->assertRedirect(route('user.dashboard'));

        $this->assertNull(StoreSetting::query()->where('key', 'mail_host')->value('value'));
        $this->assertNull(StoreSetting::query()->where('key', 'mail_password')->value('value'));
    }

    // ─── UI ───────────────────────────────────────────────────

    public function test_mail_tab_ui_is_complete_and_helpful(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.settings.edit', ['tab' => 'mail']))
            ->assertOk()
            ->assertSee('ایمیل')
            ->assertSee('SMTP')
            ->assertSee('فقط لاگ')
            ->assertSee('هاست SMTP')
            ->assertSee('پورت')
            ->assertSee('نام کاربری')
            ->assertSee('رمز عبور')
            ->assertSee('رمزنگاری اتصال')
            ->assertSee('TLS')
            ->assertSee('SSL')
            ->assertSee('آدرس فرستنده')
            ->assertSee('نام فرستنده')
            ->assertSee('App Password');
    }

    // ─── اعتبارسنجی ───────────────────────────────────────────

    public function test_rejects_invalid_mail_mailer(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->from(route('admin.settings.edit', ['tab' => 'mail']))
            ->put(route('admin.settings.update'), $this->basePayload([
                'mail_mailer' => 'ses',
            ]))
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'mail']))
            ->assertSessionHasErrors('mail_mailer');

        $this->assertNull(StoreSetting::query()->where('key', 'mail_mailer')->value('value'));
    }

    public function test_rejects_invalid_encryption(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->from(route('admin.settings.edit', ['tab' => 'mail']))
            ->put(route('admin.settings.update'), $this->basePayload([
                'mail_mailer' => 'smtp',
                'mail_encryption' => 'starttls',
            ]))
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'mail']))
            ->assertSessionHasErrors('mail_encryption');
    }

    public function test_rejects_invalid_from_address(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        StoreSetting::set('mail_from_address', 'keep@valid.test', 'mail');

        $this->actingAs($admin)
            ->from(route('admin.settings.edit', ['tab' => 'mail']))
            ->put(route('admin.settings.update'), $this->basePayload([
                'mail_mailer' => 'smtp',
                'mail_from_address' => 'not-an-email',
            ]))
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'mail']))
            ->assertSessionHasErrors('mail_from_address');

        $this->assertSame('keep@valid.test', StoreSettings::mailFromAddress());
    }

    public function test_rejects_port_below_one(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->from(route('admin.settings.edit', ['tab' => 'mail']))
            ->put(route('admin.settings.update'), $this->basePayload([
                'mail_mailer' => 'smtp',
                'mail_port' => 0,
            ]))
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'mail']))
            ->assertSessionHasErrors('mail_port');
    }

    public function test_rejects_port_above_65535(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->from(route('admin.settings.edit', ['tab' => 'mail']))
            ->put(route('admin.settings.update'), $this->basePayload([
                'mail_mailer' => 'smtp',
                'mail_port' => 65536,
            ]))
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'mail']))
            ->assertSessionHasErrors('mail_port');
    }

    public function test_persian_digits_in_port_are_normalized(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->basePayload([
                'mail_mailer' => 'smtp',
                'mail_host' => 'smtp.example.com',
                'mail_port' => '۵۸۷',
                'mail_from_address' => 'a@b.com',
            ]))
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'mail']))
            ->assertSessionDoesntHaveErrors();

        $this->assertSame(587, StoreSettings::mailPort());
        $this->assertSame('587', StoreSetting::query()->where('key', 'mail_port')->value('value'));
    }

    // ─── ذخیره و نرمال‌سازی ───────────────────────────────────

    public function test_admin_can_save_full_smtp_stack_and_config_applies_immediately(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->basePayload([
                'mail_mailer' => 'smtp',
                'mail_host' => '  mail.provider.ir  ',
                'mail_port' => 465,
                'mail_username' => '  shop@provider.ir  ',
                'mail_password' => 'AppPass-987',
                'mail_encryption' => 'ssl',
                'mail_from_address' => '  noreply@provider.ir  ',
                'mail_from_name' => '  فروشگاه رسمی  ',
            ]))
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'mail']))
            ->assertSessionHas('success');

        $this->assertSame('smtp', StoreSettings::mailMailer());
        $this->assertSame('mail.provider.ir', StoreSettings::mailHost());
        $this->assertSame(465, StoreSettings::mailPort());
        $this->assertSame('shop@provider.ir', StoreSettings::mailUsername());
        $this->assertSame('AppPass-987', StoreSettings::mailPassword());
        $this->assertSame('ssl', StoreSettings::mailEncryption());
        $this->assertSame('smtps', StoreSettings::mailScheme());
        $this->assertSame('noreply@provider.ir', StoreSettings::mailFromAddress());
        $this->assertSame('فروشگاه رسمی', StoreSettings::mailFromName());
        $this->assertTrue(StoreSettings::mailIsConfigured());

        // پس از ذخیره، applyMailConfig در کنترلر صدا زده شده
        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('mail.provider.ir', config('mail.mailers.smtp.host'));
        $this->assertSame(465, config('mail.mailers.smtp.port'));
        $this->assertSame('shop@provider.ir', config('mail.mailers.smtp.username'));
        $this->assertSame('AppPass-987', config('mail.mailers.smtp.password'));
        $this->assertSame('smtps', config('mail.mailers.smtp.scheme'));
        $this->assertSame('noreply@provider.ir', config('mail.from.address'));
        $this->assertSame('فروشگاه رسمی', config('mail.from.name'));
    }

    public function test_switching_to_log_disables_configured_flag_but_keeps_host(): void
    {
        $this->configureSmtp();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->basePayload([
                'mail_mailer' => 'log',
                'mail_host' => 'smtp.strict.test',
                'mail_port' => 587,
                'mail_from_address' => 'from@strict.test',
            ]))
            ->assertRedirect();

        $this->assertSame('log', StoreSettings::mailMailer());
        $this->assertSame('smtp.strict.test', StoreSettings::mailHost());
        $this->assertFalse(StoreSettings::mailIsConfigured());
        $this->assertSame('log', config('mail.default'));
    }

    public function test_encryption_scheme_matrix(): void
    {
        StoreSetting::set('mail_encryption', 'tls', 'mail');
        $this->assertNull(StoreSettings::mailScheme());

        StoreSetting::set('mail_encryption', 'ssl', 'mail');
        $this->assertSame('smtps', StoreSettings::mailScheme());

        StoreSetting::set('mail_encryption', 'none', 'mail');
        $this->assertSame('smtp', StoreSettings::mailScheme());

        StoreSetting::set('mail_encryption', 'garbage', 'mail');
        $this->assertSame('tls', StoreSettings::mailEncryption());
        $this->assertNull(StoreSettings::mailScheme());
    }

    public function test_invalid_stored_mailer_falls_back_to_log(): void
    {
        StoreSetting::set('mail_mailer', 'mailgun', 'mail');

        $this->assertSame('log', StoreSettings::mailMailer());
        $this->assertFalse(StoreSettings::mailIsConfigured());
    }

    public function test_zero_or_negative_stored_port_falls_back_to_587(): void
    {
        StoreSetting::set('mail_port', '0', 'mail');
        $this->assertSame(587, StoreSettings::mailPort());

        StoreSetting::set('mail_port', '-10', 'mail');
        $this->assertSame(587, StoreSettings::mailPort());
    }

    // ─── امنیت رمز ────────────────────────────────────────────

    public function test_empty_password_does_not_wipe_existing_password(): void
    {
        StoreSetting::set('mail_password', 'keep-secret', 'mail');
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->basePayload([
                'mail_mailer' => 'smtp',
                'mail_host' => 'smtp.example.com',
                'mail_password' => '',
                'mail_from_address' => 'a@b.com',
            ]))
            ->assertRedirect();

        $this->assertSame('keep-secret', StoreSettings::mailPassword());
    }

    public function test_clear_password_checkbox_wipes_password_and_breaks_auth_config(): void
    {
        $this->configureSmtp();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->basePayload([
                'mail_mailer' => 'smtp',
                'mail_host' => 'smtp.strict.test',
                'mail_port' => 587,
                'mail_username' => 'smtp-user',
                'mail_password' => '',
                'mail_clear_password' => '1',
                'mail_encryption' => 'tls',
                'mail_from_address' => 'from@strict.test',
            ]))
            ->assertRedirect();

        $this->assertSame('', StoreSetting::query()->where('key', 'mail_password')->value('value'));
        $this->assertSame('', StoreSettings::mailPassword());
        $this->assertNull(config('mail.mailers.smtp.password'));
        $this->assertTrue(StoreSettings::mailIsConfigured());
    }

    public function test_cleared_panel_password_does_not_revive_from_mutated_runtime_config(): void
    {
        config([
            'mail.env_defaults.password' => '',
            'mail.mailers.smtp.password' => 'stale-runtime-should-not-win',
        ]);
        StoreSetting::set('mail_mailer', 'smtp', 'mail');
        StoreSetting::set('mail_host', 'smtp.example.com', 'mail');
        StoreSetting::set('mail_from_address', 'a@b.com', 'mail');
        StoreSetting::set('mail_password', '', 'mail');

        $this->assertSame('', StoreSettings::mailPassword());

        StoreSettings::applyMailConfig();
        $this->assertNull(config('mail.mailers.smtp.password'));
    }

    public function test_password_never_leaks_in_settings_html_or_for_admin_array(): void
    {
        StoreSetting::set('mail_password', 'UltraSecret-Mail-Pass-9911', 'mail');
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $html = $this->actingAs($admin)
            ->get(route('admin.settings.edit', ['tab' => 'mail']))
            ->assertOk()
            ->assertDontSee('UltraSecret-Mail-Pass-9911')
            ->assertSee('Ultr')
            ->getContent();

        $this->assertStringNotContainsString('value="UltraSecret', $html);
        $this->assertStringNotContainsString("UltraSecret-Mail-Pass-9911", $html);

        $forAdmin = StoreSettings::forAdmin();
        $this->assertSame('', $forAdmin['mail_password']);
        $this->assertTrue($forAdmin['mail_password_set']);
        $this->assertStringNotContainsString('UltraSecret', $forAdmin['mail_password_masked']);
        $this->assertStringStartsWith('Ultr', $forAdmin['mail_password_masked']);
    }

    public function test_short_password_is_fully_masked(): void
    {
        StoreSetting::set('mail_password', 'short', 'mail');

        $this->assertSame('*****', StoreSettings::mailPasswordMasked());
    }

    // ─── پایداری تب‌های دیگر ──────────────────────────────────

    public function test_saving_identity_tab_does_not_wipe_mail_credentials(): void
    {
        $this->configureSmtp([
            'mail_password' => 'persist-pass',
            'mail_username' => 'persist-user',
            'mail_from_name' => 'نام پایدار',
        ]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'store_name' => 'فروشگاه جدید',
                'currency' => 'تومان',
                'color_preset' => StoreSettings::defaultColorPresetId(),
                'return_days' => 7,
                'active_tab' => 'identity',
            ])
            ->assertRedirect();

        $this->assertSame('smtp', StoreSettings::mailMailer());
        $this->assertSame('smtp.strict.test', StoreSettings::mailHost());
        $this->assertSame('persist-user', StoreSettings::mailUsername());
        $this->assertSame('persist-pass', StoreSettings::mailPassword());
        $this->assertSame('from@strict.test', StoreSettings::mailFromAddress());
        $this->assertSame('نام پایدار', StoreSettings::mailFromName());
        $this->assertTrue(StoreSettings::mailIsConfigured());
    }

    public function test_saving_sms_tab_does_not_wipe_mail_settings(): void
    {
        $this->configureSmtp();
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->basePayload([
                'active_tab' => 'sms',
                'sms_driver' => 'log',
                'sms_mode' => 'simple',
            ]))
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'sms']));

        $this->assertSame('smtp', StoreSettings::mailMailer());
        $this->assertSame('smtp-secret', StoreSettings::mailPassword());
    }

    // ─── اولویت پنل / .env ────────────────────────────────────

    public function test_env_mail_values_used_when_admin_fields_empty(): void
    {
        config([
            'mail.env_defaults.mailer' => 'smtp',
            'mail.env_defaults.host' => 'env-smtp.example.com',
            'mail.env_defaults.port' => 2525,
            'mail.env_defaults.username' => 'env-user',
            'mail.env_defaults.password' => 'env-pass',
            'mail.env_defaults.scheme' => 'smtps',
            'mail.env_defaults.from_address' => 'env-from@example.com',
            'mail.env_defaults.from_name' => 'Env Store',
        ]);

        $this->assertSame('smtp', StoreSettings::mailMailer());
        $this->assertSame('env-smtp.example.com', StoreSettings::mailHost());
        $this->assertSame(2525, StoreSettings::mailPort());
        $this->assertSame('env-user', StoreSettings::mailUsername());
        $this->assertSame('env-pass', StoreSettings::mailPassword());
        $this->assertSame('ssl', StoreSettings::mailEncryption());
        $this->assertSame('env-from@example.com', StoreSettings::mailFromAddress());
        $this->assertSame('Env Store', StoreSettings::mailFromName());
        $this->assertTrue(StoreSettings::mailIsConfigured());
    }

    public function test_panel_values_override_env(): void
    {
        config([
            'mail.env_defaults.mailer' => 'smtp',
            'mail.env_defaults.host' => 'env-host',
            'mail.env_defaults.password' => 'env-pass',
            'mail.env_defaults.from_address' => 'env@x.com',
        ]);

        StoreSetting::set('mail_mailer', 'smtp', 'mail');
        StoreSetting::set('mail_host', 'panel-host', 'mail');
        StoreSetting::set('mail_password', 'panel-pass', 'mail');
        StoreSetting::set('mail_from_address', 'panel@x.com', 'mail');

        $this->assertSame('panel-host', StoreSettings::mailHost());
        $this->assertSame('panel-pass', StoreSettings::mailPassword());
        $this->assertSame('panel@x.com', StoreSettings::mailFromAddress());
    }

    public function test_empty_store_host_falls_back_to_env_not_blank(): void
    {
        config(['mail.env_defaults.host' => 'fallback-host.example']);
        StoreSetting::set('mail_host', '', 'mail');

        $this->assertSame('fallback-host.example', StoreSettings::mailHost());
    }

    public function test_non_smtp_env_mailer_normalizes_to_log(): void
    {
        config([
            'mail.env_defaults.mailer' => 'array',
            'mail.default' => 'array',
        ]);

        $this->assertSame('log', StoreSettings::mailMailer());
    }

    // ─── mailIsConfigured ─────────────────────────────────────

    public function test_configured_requires_smtp_host_and_from(): void
    {
        config([
            'mail.env_defaults.host' => '',
            'mail.env_defaults.from_address' => '',
            'mail.mailers.smtp.host' => '',
            'mail.from.address' => '',
        ]);

        StoreSetting::set('mail_mailer', 'log', 'mail');
        StoreSetting::set('mail_host', 'h', 'mail');
        StoreSetting::set('mail_from_address', 'a@b.com', 'mail');
        $this->assertFalse(StoreSettings::mailIsConfigured());

        StoreSetting::set('mail_mailer', 'smtp', 'mail');
        StoreSetting::set('mail_host', '', 'mail');
        $this->assertFalse(StoreSettings::mailIsConfigured());

        StoreSetting::set('mail_host', 'smtp.x.com', 'mail');
        StoreSetting::set('mail_from_address', '', 'mail');
        $this->assertFalse(StoreSettings::mailIsConfigured());

        StoreSetting::set('mail_from_address', 'ok@x.com', 'mail');
        $this->assertTrue(StoreSettings::mailIsConfigured());
    }

    // ─── applyMailConfig ──────────────────────────────────────

    public function test_apply_mail_config_sets_null_credentials_when_empty(): void
    {
        config([
            'mail.env_defaults.username' => '',
            'mail.env_defaults.password' => '',
        ]);
        StoreSetting::set('mail_mailer', 'smtp', 'mail');
        StoreSetting::set('mail_host', 'h.example', 'mail');
        StoreSetting::set('mail_port', '587', 'mail');
        StoreSetting::set('mail_username', '', 'mail');
        StoreSetting::set('mail_password', '', 'mail');
        StoreSetting::set('mail_encryption', 'tls', 'mail');
        StoreSetting::set('mail_from_address', 'a@b.com', 'mail');

        StoreSettings::applyMailConfig();

        $this->assertNull(config('mail.mailers.smtp.username'));
        $this->assertNull(config('mail.mailers.smtp.password'));
        $this->assertNull(config('mail.mailers.smtp.scheme'));
    }

    public function test_apply_mail_config_uses_store_name_when_from_name_empty(): void
    {
        config([
            'mail.env_defaults.from_name' => '',
            'mail.from.name' => '',
        ]);
        StoreSetting::set('store_name', 'نام از فروشگاه', 'appearance');
        StoreSetting::set('mail_mailer', 'log', 'mail');
        StoreSetting::set('mail_from_name', '', 'mail');

        StoreSettings::applyMailConfig();

        $this->assertSame('نام از فروشگاه', config('mail.from.name'));
    }

    // ─── Seeder ───────────────────────────────────────────────

    public function test_seeder_adds_mail_keys_without_wiping_existing_password(): void
    {
        StoreSetting::set('mail_password', 'already-set', 'mail');
        StoreSetting::set('mail_host', 'keep-host', 'mail');

        $this->seed(StoreSettingsSeeder::class);

        $this->assertSame('already-set', StoreSettings::mailPassword());
        $this->assertSame('keep-host', StoreSettings::mailHost());
        $this->assertTrue(StoreSetting::query()->where('key', 'mail_mailer')->exists());
        $this->assertTrue(StoreSetting::query()->where('key', 'mail_encryption')->exists());
        $this->assertTrue(StoreSetting::query()->where('key', 'mail_from_address')->exists());
    }

    // ─── ارسال واقعی سفارش ────────────────────────────────────

    public function test_order_confirmation_mail_uses_applied_from_and_recipient(): void
    {
        Mail::fake();
        $this->configureSmtp([
            'mail_from_address' => 'orders@shop.test',
            'mail_from_name' => 'سفارش‌ها',
        ]);

        $user = User::factory()->create(['email' => 'buyer@shop.test']);
        $order = $this->makeOrder($user);

        app(OrderService::class)->sendConfirmationEmail($order);

        Mail::assertSent(OrderConfirmationMail::class, function (OrderConfirmationMail $mail) use ($order) {
            return $mail->hasTo('buyer@shop.test')
                && $mail->order->is($order)
                && str_contains($mail->envelope()->subject, $order->order_number);
        });

        $this->assertSame('orders@shop.test', config('mail.from.address'));
        $this->assertSame('سفارش‌ها', config('mail.from.name'));
    }

    public function test_order_status_mail_is_sent_when_requested(): void
    {
        Mail::fake();
        $this->configureSmtp();

        $user = User::factory()->create(['email' => 'status@shop.test']);
        $order = $this->makeOrder($user);

        // notifyStatusChange فقط وضعیت SMS را برمی‌گرداند؛ ایمیل جداگانه ارسال می‌شود
        app(OrderService::class)->notifyStatusChange($order, OrderStatus::Processing, false, true);

        Mail::assertSent(OrderStatusMail::class, fn (OrderStatusMail $mail) => $mail->hasTo('status@shop.test'));
    }

    public function test_confirmation_mail_skipped_when_customer_email_blank(): void
    {
        Mail::fake();
        $this->configureSmtp();

        $user = User::factory()->create(['email' => 'blank-check@shop.test']);
        // ایمیل خالی در DB (بدون NULL) — resolveEmail نباید ارسال کند
        \Illuminate\Support\Facades\DB::table('users')->where('id', $user->id)->update(['email' => '']);
        $order = $this->makeOrder($user->fresh());

        app(OrderService::class)->sendConfirmationEmail($order);

        Mail::assertNothingSent();
    }

    public function test_for_admin_exposes_configured_flag_consistently_with_helpers(): void
    {
        $adminView = StoreSettings::forAdmin();
        $this->assertFalse($adminView['mail_configured']);
        $this->assertSame(StoreSettings::mailIsConfigured(), $adminView['mail_configured']);

        $this->configureSmtp();
        $adminView = StoreSettings::forAdmin();
        $this->assertTrue($adminView['mail_configured']);
        $this->assertSame('smtp.strict.test', $adminView['mail_host']);
        $this->assertSame('', $adminView['mail_password']);
    }
}
