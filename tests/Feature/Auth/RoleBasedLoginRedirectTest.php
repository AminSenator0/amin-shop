<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedLoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_user_login_redirects_to_user_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Customer,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('user.dashboard', absolute: false));
    }

    public function test_admin_user_login_redirects_to_admin_dashboard_even_after_visiting_account(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->get('/account');

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_customer_user_login_redirects_to_user_dashboard_even_after_visiting_admin(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Customer,
        ]);

        $this->get('/admin');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('user.dashboard', absolute: false));
    }

    public function test_admin_user_login_honors_intended_admin_route(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->get('/admin/products');

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/products');
    }

    public function test_customer_user_login_honors_intended_user_route(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Customer,
        ]);

        $this->get('/account/orders');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/account/orders');
    }
}
