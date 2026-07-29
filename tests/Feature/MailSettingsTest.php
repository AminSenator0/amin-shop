<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\StoreSetting;
use App\Models\User;
use App\Support\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailSettingsTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function basePayload(array $overrides = []): array
    {
        return array_merge([
            'store_name' => 'فروشگاه تست',
            'currency' => 'تومان',
            'color_preset' => StoreSettings::defaultColorPresetId(),
            'return_days' => 7,
            'active_tab' => 'mail',
        ], $overrides);
    }

    public function test_admin_mail_settings_page_shows_smtp_fields(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.settings.edit', ['tab' => 'mail']))
            ->assertOk()
            ->assertSee('ایمیل')
            ->assertSee('SMTP')
            ->assertSee('هاست SMTP')
            ->assertSee('آدرس فرستنده');
    }

    public function test_admin_can_save_smtp_settings(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->basePayload([
                'mail_mailer' => 'smtp',
                'mail_host' => 'smtp.example.com',
                'mail_port' => 587,
                'mail_username' => 'shop@example.com',
                'mail_password' => 'secret-pass-123',
                'mail_encryption' => 'tls',
                'mail_from_address' => 'noreply@example.com',
                'mail_from_name' => 'فروشگاه من',
            ]))
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'mail']));

        $this->assertSame('smtp', StoreSettings::mailMailer());
        $this->assertSame('smtp.example.com', StoreSettings::mailHost());
        $this->assertSame(587, StoreSettings::mailPort());
        $this->assertSame('shop@example.com', StoreSettings::mailUsername());
        $this->assertSame('secret-pass-123', StoreSettings::mailPassword());
        $this->assertSame('tls', StoreSettings::mailEncryption());
        $this->assertNull(StoreSettings::mailScheme());
        $this->assertSame('noreply@example.com', StoreSettings::mailFromAddress());
        $this->assertSame('فروشگاه من', StoreSettings::mailFromName());
        $this->assertTrue(StoreSettings::mailIsConfigured());

        StoreSettings::applyMailConfig();
        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.example.com', config('mail.mailers.smtp.host'));
        $this->assertSame(587, config('mail.mailers.smtp.port'));
        $this->assertSame('noreply@example.com', config('mail.from.address'));
        $this->assertSame('فروشگاه من', config('mail.from.name'));
    }

    public function test_ssl_encryption_maps_to_smtps_scheme(): void
    {
        StoreSetting::set('mail_mailer', 'smtp', 'mail');
        StoreSetting::set('mail_encryption', 'ssl', 'mail');
        StoreSetting::set('mail_host', 'smtp.ssl.test', 'mail');
        StoreSetting::set('mail_port', '465', 'mail');
        StoreSetting::set('mail_from_address', 'a@b.com', 'mail');

        $this->assertSame('smtps', StoreSettings::mailScheme());

        StoreSettings::applyMailConfig();
        $this->assertSame('smtps', config('mail.mailers.smtp.scheme'));
        $this->assertSame(465, config('mail.mailers.smtp.port'));
    }

    public function test_empty_password_does_not_wipe_existing_password(): void
    {
        StoreSetting::set('mail_password', 'keep-me-pass', 'mail');
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->basePayload([
                'mail_mailer' => 'smtp',
                'mail_host' => 'smtp.example.com',
                'mail_port' => 587,
                'mail_password' => '',
                'mail_from_address' => 'noreply@example.com',
            ]))
            ->assertRedirect();

        $this->assertSame('keep-me-pass', StoreSettings::mailPassword());
    }

    public function test_admin_can_clear_mail_password(): void
    {
        StoreSetting::set('mail_password', 'wipe-me', 'mail');
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->basePayload([
                'mail_mailer' => 'log',
                'mail_password' => '',
                'mail_clear_password' => '1',
            ]))
            ->assertRedirect();

        $this->assertSame('', StoreSettings::mailPassword());
    }

    public function test_customer_cannot_change_mail_settings(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->put(route('admin.settings.update'), $this->basePayload([
                'mail_mailer' => 'smtp',
                'mail_host' => 'evil.example.com',
            ]))
            ->assertRedirect(route('user.dashboard'));

        $this->assertNull(StoreSetting::query()->where('key', 'mail_host')->value('value'));
    }

    public function test_password_is_masked_on_admin_form(): void
    {
        StoreSetting::set('mail_password', 'super-secret-password', 'mail');
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.settings.edit', ['tab' => 'mail']))
            ->assertOk()
            ->assertSee('supe')
            ->assertDontSee('super-secret-password');
    }
}
