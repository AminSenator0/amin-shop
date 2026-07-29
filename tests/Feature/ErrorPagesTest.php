<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_page_uses_custom_minimal_view(): void
    {
        $this->get('/__missing-error-page-route__')
            ->assertNotFound()
            ->assertSee('404', false)
            ->assertSee('صفحه پیدا نشد', false)
            ->assertSee('صفحه اصلی', false);
    }

    public function test_403_page_uses_custom_minimal_view(): void
    {
        Route::get('/__test-abort-403', fn () => abort(403));

        $this->get('/__test-abort-403')
            ->assertForbidden()
            ->assertSee('403', false)
            ->assertSee('دسترسی مجاز نیست', false);
    }

    public function test_401_page_uses_custom_minimal_view(): void
    {
        Route::get('/__test-abort-401', fn () => abort(401));

        $this->get('/__test-abort-401')
            ->assertUnauthorized()
            ->assertSee('401', false)
            ->assertSee('ورود لازم است', false)
            ->assertSee('ورود', false);
    }

    public function test_429_page_uses_custom_minimal_view(): void
    {
        Route::get('/__test-abort-429', fn () => abort(429));

        $this->get('/__test-abort-429')
            ->assertStatus(429)
            ->assertSee('429', false)
            ->assertSee('درخواست‌های زیاد', false);
    }

    public function test_500_page_uses_custom_minimal_view(): void
    {
        Route::get('/__test-abort-500', fn () => abort(500));

        $this->get('/__test-abort-500')
            ->assertStatus(500)
            ->assertSee('500', false)
            ->assertSee('خطای سرور', false);
    }

    public function test_503_page_uses_custom_minimal_view(): void
    {
        Route::get('/__test-abort-503', fn () => abort(503));

        $this->get('/__test-abort-503')
            ->assertStatus(503)
            ->assertSee('503', false)
            ->assertSee('سرویس موقتاً در دسترس نیست', false);
    }
}
