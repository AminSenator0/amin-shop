<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Mail\AuthOtpMail;
use App\Models\AuthOtp;
use App\Models\StoreSetting;
use App\Models\User;
use App\Support\StoreSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class OtpAuthStrictTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['auth.otp_test_code' => '123456']);
        StoreSetting::set('auth_otp_enabled', '1', 'auth', 'boolean');
        StoreSetting::set('auth_otp_channel', 'email', 'auth');
        StoreSetting::set('sms_driver', 'log', 'sms');
        StoreSetting::set('mail_mailer', 'log', 'mail');
        RateLimiter::clear('otp-send:login:email:buyer@shop.test');
        RateLimiter::clear('otp-send:register:email:new@shop.test');
        RateLimiter::clear('otp-send:login:mobile:09121234567');
        RateLimiter::clear('otp-send:register:mobile:09129876543');
    }

    /** @return array<string, mixed> */
    private function settingsPayload(array $extra = []): array
    {
        return array_merge([
            'store_name' => 'فروشگاه تست',
            'currency' => 'تومان',
            'color_preset' => StoreSettings::defaultColorPresetId(),
            'return_days' => 7,
            'active_tab' => 'auth',
            'auth_otp_enabled' => '1',
            'auth_otp_channel' => 'mobile',
        ], $extra);
    }

    public function test_admin_auth_tab_shows_otp_channel_options(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(route('admin.settings.edit', ['tab' => 'auth']))
            ->assertOk()
            ->assertSee('ورود و ثبت‌نام')
            ->assertSee('موبایل (پیامک)')
            ->assertSee('ایمیل')
            ->assertSee('فعال‌سازی ورود و ثبت‌نام با OTP');
    }

    public function test_admin_can_save_otp_channel_and_toggle(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->settingsPayload([
                'auth_otp_enabled' => '1',
                'auth_otp_channel' => 'email',
            ]))
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'auth']));

        $this->assertTrue(StoreSettings::authOtpEnabled());
        $this->assertSame('email', StoreSettings::authOtpChannel());

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->settingsPayload([
                'auth_otp_enabled' => '0',
                'auth_otp_channel' => 'mobile',
            ]))
            ->assertRedirect();

        $this->assertFalse(StoreSettings::authOtpEnabled());
        $this->assertSame('mobile', StoreSettings::authOtpChannel());
    }

    public function test_customer_cannot_change_otp_settings(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->put(route('admin.settings.update'), $this->settingsPayload([
                'auth_otp_channel' => 'email',
            ]))
            ->assertRedirect(route('user.dashboard'));
    }

    public function test_login_and_register_pages_show_otp_when_enabled(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('کد یکبارمصرف')
            ->assertSee('رمز عبور');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('کد یکبارمصرف');
    }

    public function test_otp_tab_hidden_when_disabled(): void
    {
        StoreSetting::set('auth_otp_enabled', '0', 'auth', 'boolean');

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('کد یکبارمصرف');

        $this->post(route('login.otp.send'), ['email' => 'a@b.com'])
            ->assertSessionHasErrors('otp');
    }

    public function test_email_login_otp_send_and_verify(): void
    {
        Mail::fake();
        $user = User::factory()->create([
            'email' => 'buyer@shop.test',
            'role' => UserRole::Customer,
        ]);

        $this->from(route('login'))
            ->post(route('login.otp.send'), ['email' => 'buyer@shop.test'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('otp_sent')
            ->assertSessionHas('otp_debug_code', '123456');

        Mail::assertSent(AuthOtpMail::class, fn (AuthOtpMail $mail) => $mail->hasTo('buyer@shop.test') && $mail->code === '123456');

        $this->post(route('login.otp.verify'), [
            'email' => 'buyer@shop.test',
            'code' => '123456',
        ])->assertRedirect(route('user.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull(AuthOtp::query()->whereNotNull('consumed_at')->first());
    }

    public function test_login_otp_does_not_reveal_missing_user(): void
    {
        Mail::fake();
        // خارج از حالت تست، نباید وجود حساب لو برود
        StoreSetting::set('mail_mailer', 'smtp', 'mail');
        StoreSetting::set('mail_host', 'smtp.example.com', 'mail');
        StoreSetting::set('mail_port', '587', 'mail');
        StoreSetting::set('mail_username', 'u', 'mail');
        StoreSetting::set('mail_password', 'p', 'mail');
        StoreSetting::set('mail_from_address', 'a@b.com', 'mail');

        $this->from(route('login'))
            ->post(route('login.otp.send'), ['email' => 'nobody@shop.test'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('otp_sent')
            ->assertSessionMissing('otp_debug_code')
            ->assertSessionDoesntHaveErrors('otp');

        Mail::assertNothingSent();
        $this->assertSame(0, AuthOtp::query()->count());
    }

    public function test_login_otp_test_mode_tells_when_user_missing(): void
    {
        StoreSetting::set('auth_otp_channel', 'mobile', 'auth');
        StoreSetting::set('sms_driver', 'log', 'sms');

        $this->from(route('login'))
            ->post(route('login.otp.send'), ['phone' => '09391234526'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('otp')
            ->assertSessionMissing('otp_debug_code');
    }

    public function test_admin_cannot_login_via_customer_otp(): void
    {
        Mail::fake();
        User::factory()->create([
            'email' => 'admin-otp@shop.test',
            'role' => UserRole::Admin,
        ]);

        $this->post(route('login.otp.send'), ['email' => 'admin-otp@shop.test'])
            ->assertRedirect();

        Mail::assertNothingSent();
    }

    public function test_wrong_otp_code_is_rejected(): void
    {
        Mail::fake();
        User::factory()->create(['email' => 'buyer@shop.test']);

        $this->post(route('login.otp.send'), ['email' => 'buyer@shop.test'])->assertRedirect();

        $this->from(route('login'))
            ->post(route('login.otp.verify'), [
                'email' => 'buyer@shop.test',
                'code' => '000000',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('code');

        $this->assertGuest();
        $this->assertSame(1, AuthOtp::query()->value('attempts'));
    }

    public function test_expired_otp_is_rejected(): void
    {
        Mail::fake();
        User::factory()->create(['email' => 'buyer@shop.test']);

        $this->post(route('login.otp.send'), ['email' => 'buyer@shop.test'])->assertRedirect();

        AuthOtp::query()->update(['expires_at' => now()->subMinute()]);

        $this->post(route('login.otp.verify'), [
            'email' => 'buyer@shop.test',
            'code' => '123456',
        ])->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    public function test_otp_send_is_throttled(): void
    {
        Mail::fake();
        User::factory()->create(['email' => 'buyer@shop.test']);

        $this->post(route('login.otp.send'), ['email' => 'buyer@shop.test'])->assertRedirect();
        $this->post(route('login.otp.send'), ['email' => 'buyer@shop.test'])->assertSessionHasErrors('otp');

        Mail::assertSent(AuthOtpMail::class, 1);
    }

    public function test_register_with_email_otp(): void
    {
        Mail::fake();
        StoreSetting::set('auth_otp_channel', 'email', 'auth');

        $this->from(route('register'))
            ->post(route('register.otp.send'), [
                'name' => 'کاربر جدید',
                'email' => 'new@shop.test',
                'phone' => '09121112233',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHas('otp_sent');

        Mail::assertSent(AuthOtpMail::class);

        $this->post(route('register.otp.verify'), [
            'email' => 'new@shop.test',
            'code' => '123456',
        ])->assertRedirect(route('user.dashboard'));

        $this->assertAuthenticated();
        $user = User::query()->where('email', 'new@shop.test')->first();
        $this->assertNotNull($user);
        $this->assertSame('کاربر جدید', $user->name);
        $this->assertSame('09121112233', $user->phone);
        $this->assertTrue(Hash::check('not-the-password', $user->password) === false);
    }

    public function test_register_with_mobile_otp_uses_sms_log_driver(): void
    {
        StoreSetting::set('auth_otp_channel', 'mobile', 'auth');
        StoreSetting::set('sms_driver', 'log', 'sms');
        RateLimiter::clear('otp-send:register:mobile:09129876543');
        \App\Services\SmsService::clearRecentLogMessages();

        $this->from(route('register'))
            ->post(route('register.otp.send'), [
                'name' => 'موبایلی',
                'phone' => '09129876543',
                'email' => 'mobile-user@shop.test',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHas('otp_debug_code', '123456');

        $this->assertDatabaseHas('auth_otps', [
            'channel' => 'mobile',
            'destination' => '09129876543',
            'purpose' => 'register',
        ]);

        $recent = \App\Services\SmsService::recentLogMessages();
        $this->assertNotEmpty($recent);
        $this->assertSame('123456', $recent[0]['code'] ?? null);
        $this->assertSame('09129876543', $recent[0]['phone'] ?? null);

        $this->post(route('register.otp.verify'), [
            'phone' => '09129876543',
            'code' => '123456',
        ])->assertRedirect(route('user.dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'mobile-user@shop.test',
            'phone' => '09129876543',
            'name' => 'موبایلی',
        ]);
    }

    public function test_debug_code_appears_on_login_page_in_test_mode(): void
    {
        StoreSetting::set('auth_otp_channel', 'mobile', 'auth');
        StoreSetting::set('sms_driver', 'log', 'sms');
        RateLimiter::clear('otp-send:login:mobile:09121000000');

        User::factory()->create([
            'phone' => '09121000000',
            'role' => UserRole::Customer,
        ]);

        $this->from(route('login'))
            ->post(route('login.otp.send'), ['phone' => '09121000000'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('otp_debug_code', '123456');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('حالت تست — کد تایید شما:', false)
            ->assertSee('123456')
            ->assertSee('otpSent: true', false);
    }

    public function test_debug_code_is_hidden_when_sms_is_not_log_driver(): void
    {
        StoreSetting::set('auth_otp_channel', 'mobile', 'auth');
        StoreSetting::set('sms_driver', 'kavenegar', 'sms');
        StoreSetting::set('sms_kavenegar_api_key', 'test-key', 'sms');
        StoreSetting::set('sms_kavenegar_sender', '1000', 'sms');
        RateLimiter::clear('otp-send:login:mobile:09121234567');

        User::factory()->create([
            'phone' => '09121234567',
            'role' => UserRole::Customer,
        ]);

        // بدون mock واقعی، ارسال پیامک شکست می‌خورد — فقط مطمئن می‌شویم کد دیباگ لو نمی‌رود
        $this->from(route('login'))
            ->post(route('login.otp.send'), ['phone' => '09121234567'])
            ->assertSessionMissing('otp_debug_code');
    }

    public function test_stale_debug_session_is_not_shown_outside_test_mode(): void
    {
        StoreSetting::set('auth_otp_channel', 'mobile', 'auth');
        StoreSetting::set('sms_driver', 'kavenegar', 'sms');

        $this->withSession([
            'otp_sent' => true,
            'otp_purpose' => 'login',
            'otp_destination' => '09121234567',
            'otp_destination_masked' => '0912***67',
            'otp_debug_code' => '654321',
        ])
            ->get(route('login'))
            ->assertOk()
            ->assertDontSee('654321')
            ->assertDontSee('حالت تست — کد تایید شما:');

        $this->assertNull(\App\Services\OtpService::viewDebugCode('mobile'));
    }

    public function test_leaving_sms_test_mode_clears_cached_codes(): void
    {
        StoreSetting::set('sms_driver', 'log', 'sms');
        \App\Services\SmsService::clearRecentLogMessages();

        app(\App\Services\SmsService::class)->sendOtp('09121234567', '112233', 'فروشگاه');
        $this->assertSame('112233', \App\Services\SmsService::recentLogMessages()[0]['code'] ?? null);

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin)
            ->put(route('admin.settings.update'), $this->settingsPayload([
                'active_tab' => 'sms',
                'sms_driver' => 'kavenegar',
                'sms_mode' => 'simple',
                'sms_kavenegar_api_key' => 'live-key',
                'sms_kavenegar_sender' => '1000',
            ]))
            ->assertRedirect();

        $this->assertSame('kavenegar', StoreSettings::smsDriver());
        $this->assertSame([], \App\Services\SmsService::recentLogMessages());
    }

    public function test_register_rejects_duplicate_email_before_send(): void
    {
        User::factory()->create(['email' => 'taken@shop.test']);

        $this->from(route('register'))
            ->post(route('register.otp.send'), [
                'name' => 'تکراری',
                'email' => 'taken@shop.test',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('email');
    }

    public function test_login_otp_ui_respects_mobile_channel(): void
    {
        StoreSetting::set('auth_otp_channel', 'mobile', 'auth');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('شماره موبایل')
            ->assertSee(route('login.otp.send'), false);
    }

    public function test_password_login_still_works_alongside_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'pass@shop.test',
            'password' => Hash::make('password'),
        ]);

        $this->post(route('login'), [
            'email' => 'pass@shop.test',
            'password' => 'password',
        ])->assertRedirect(route('user.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_code_is_stored_hashed_not_plaintext(): void
    {
        Mail::fake();
        User::factory()->create(['email' => 'buyer@shop.test']);

        $this->post(route('login.otp.send'), ['email' => 'buyer@shop.test'])->assertRedirect();

        $otp = AuthOtp::query()->first();
        $this->assertNotSame('123456', $otp->code_hash);
        $this->assertTrue(Hash::check('123456', $otp->code_hash));
    }

    public function test_max_attempts_consumes_otp(): void
    {
        Mail::fake();
        User::factory()->create(['email' => 'buyer@shop.test']);
        $this->post(route('login.otp.send'), ['email' => 'buyer@shop.test'])->assertRedirect();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.otp.verify'), [
                'email' => 'buyer@shop.test',
                'code' => '000000',
            ]);
        }

        $this->post(route('login.otp.verify'), [
            'email' => 'buyer@shop.test',
            'code' => '123456',
        ])->assertSessionHasErrors('code');

        $this->assertGuest();
        $this->assertNotNull(AuthOtp::query()->value('consumed_at'));
    }
}
