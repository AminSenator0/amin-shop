<?php

namespace App\Providers;

use App\Enums\ReturnStatus;
use App\Listeners\LogAuthenticationEvents;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Observers\CouponObserver;
use App\Observers\ProductObserver;
use App\Observers\UserObserver;
use App\Policies\OrderPolicy;
use App\Services\CartService;
use App\Services\ContactMessageService;
use App\Services\FileUploadService;
use App\Services\OrderService;
use App\Services\WishlistService;
use App\Support\StoreSettings;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FileUploadService::class);
    }

    public function boot(): void
    {
        // ═══════════════════════════════════════════════
        // Policies
        // ═══════════════════════════════════════════════

        Gate::policy(Order::class, OrderPolicy::class);


        // ═══════════════════════════════════════════════
        // Observers
        // ═══════════════════════════════════════════════

        Product::observe(ProductObserver::class);
        User::observe(UserObserver::class);
        Coupon::observe(CouponObserver::class);


        // ═══════════════════════════════════════════════
        // Event Listeners (Audit Logging)
        // ═══════════════════════════════════════════════

        Event::listen(Login::class, [LogAuthenticationEvents::class, 'handleLogin']);
        Event::listen(Failed::class, [LogAuthenticationEvents::class, 'handleFailed']);
        Event::listen(Logout::class, [LogAuthenticationEvents::class, 'handleLogout']);

        // Uncomment if you have custom Order/Payment events:
        // Event::listen(\App\Events\OrderCreated::class, [\App\Listeners\LogOrderEvents::class, 'handleOrderCreated']);
        // Event::listen(\App\Events\OrderPaid::class, [\App\Listeners\LogOrderEvents::class, 'handleOrderPaid']);
        // Event::listen(\App\Events\PaymentStarted::class, [\App\Listeners\LogPaymentEvents::class, 'handlePaymentStarted']);
        // Event::listen(\App\Events\PaymentSuccess::class, [\App\Listeners\LogPaymentEvents::class, 'handlePaymentSuccess']);
        // Event::listen(\App\Events\PaymentFailed::class, [\App\Listeners\LogPaymentEvents::class, 'handlePaymentFailed']);
        // Event::listen(\App\Events\PaymentCallback::class, [\App\Listeners\LogPaymentEvents::class, 'handlePaymentCallback']);


        // ═══════════════════════════════════════════════
        // Pagination
        // ═══════════════════════════════════════════════

        Paginator::defaultView('pagination.tailwind');
        Paginator::defaultSimpleView('pagination.simple-tailwind');


        // ═══════════════════════════════════════════════
        // Rate Limiting
        // ═══════════════════════════════════════════════

        $this->configureRateLimiting();


        // ═══════════════════════════════════════════════
        // Admin View Composer
        // ═══════════════════════════════════════════════

        View::composer(
            ['layouts.admin', 'components.admin.sidebar'],
            function ($view) {
                $view->with([
                    'unreadMessagesCount' => ContactMessage::unread()->count(),
                    'unreadOrdersCount' => app(OrderService::class)->unreadCount(),
                    'pendingReturnsCount' => OrderReturn::where(
                        'status',
                        ReturnStatus::Pending
                    )->count(),
                    'pendingReviewsCount' => Review::where(
                        'is_approved',
                        false
                    )->count(),
                ]);
            }
        );


        // ═══════════════════════════════════════════════
        // User View Composer
        // ═══════════════════════════════════════════════

        View::composer(
            ['layouts.user', 'components.user.sidebar'],
            function ($view) {
                $user = Auth::user();

                $view->with([
                    'unreadUserMessagesCount' => $user
                        ? app(ContactMessageService::class)->unreadCountForUser($user)
                        : 0,

                    'wishlistCount' => $user
                        ? app(WishlistService::class)->count()
                        : 0,

                    'cartCount' => app(CartService::class)->count(),

                    'openReturnsCount' => $user
                        ? $user->openReturnsCount()
                        : 0,
                ]);
            }
        );


        // ═══════════════════════════════════════════════
        // Store Settings
        // ═══════════════════════════════════════════════

        View::composer('*', function ($view) {
            $name = $view->getName();

            if (
                preg_match('/^(admin\.|components\.admin\.)/', $name)
                || $name === 'layouts.admin'
            ) {
                return;
            }

            if (
                preg_match(
                    '/^(shop|layouts\.(shop|guest|user)|user\.|auth\.|profile\.|invoices\.|components\.(shop|user)\.)/',
                    $name
                )
            ) {
                $view->with(
                    'store',
                    StoreSettings::forShop()
                );
            }
        });


        // ═══════════════════════════════════════════════
        // Dynamic Mail Configuration
        // ═══════════════════════════════════════════════

        $this->applyStoreMailConfig();
    }


    /**
     * Configure application rate limiting.
     */
    private function configureRateLimiting(): void
    {
        // \u0644\u0627\u06af\u06cc\u0646: \u06f5 \u062a\u0644\u0627\u0634 \u062f\u0631 \u062f\u0642\u06cc\u0642\u0647
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(function () {
                    return back()->with(
                        'error',
                        '\u062a\u0639\u062f\u0627\u062f \u062a\u0644\u0627\u0634\u200c\u0647\u0627\u06cc \u0646\u0627\u0645\u0648\u0641\u0642 \u0632\u06cc\u0627\u062f \u0628\u0648\u062f. \u0644\u0637\u0641\u0627\u064b \u06f1 \u062f\u0642\u06cc\u0642\u0647 \u062f\u06cc\u06af\u0631 \u062a\u0644\u0627\u0634 \u06a9\u0646\u06cc\u062f.'
                    );
                });
        });


        // \u062b\u0628\u062a\u200c\u0646\u0627\u0645: \u06f3 \u062a\u0644\u0627\u0634 \u062f\u0631 \u062f\u0642\u06cc\u0642\u0647
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(3)
                ->by($request->ip())
                ->response(function () {
                    return back()->with(
                        'error',
                        '\u062a\u0639\u062f\u0627\u062f \u062b\u0628\u062a\u200c\u0646\u0627\u0645 \u0628\u06cc\u0634 \u0627\u0632 \u062d\u062f \u0645\u062c\u0627\u0632. \u0644\u0637\u0641\u0627\u064b \u06f1 \u062f\u0642\u06cc\u0642\u0647 \u0635\u0628\u0631 \u06a9\u0646\u06cc\u062f.'
                    );
                });
        });


        // \u06a9\u062f \u062a\u062e\u0641\u06cc\u0641: \u06f1\u06f0 \u062a\u0644\u0627\u0634 \u062f\u0631 \u062f\u0642\u06cc\u0642\u0647
        RateLimiter::for('coupon', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->ip())
                ->response(function () {
                    return back()->with(
                        'error',
                        '\u062a\u0639\u062f\u0627\u062f \u062a\u0644\u0627\u0634 \u0628\u0631\u0627\u06cc \u06a9\u062f \u062a\u062e\u0641\u06cc\u0641 \u0632\u06cc\u0627\u062f \u0628\u0648\u062f. \u0644\u0637\u0641\u0627\u064b \u06f1 \u062f\u0642\u06cc\u0642\u0647 \u0635\u0628\u0631 \u06a9\u0646\u06cc\u062f.'
                    );
                });
        });


        // \u0641\u0631\u0645 \u062a\u0645\u0627\u0633/\u067e\u06cc\u0627\u0645: \u06f5 \u067e\u06cc\u0627\u0645 \u062f\u0631 \u0633\u0627\u0639\u062a
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perHour(5)
                ->by($request->ip())
                ->response(function () {
                    return back()->with(
                        'error',
                        '\u0634\u0645\u0627 \u062f\u0631 \u06cc\u06a9 \u0633\u0627\u0639\u062a \u0641\u0642\u0637 \u06f5 \u067e\u06cc\u0627\u0645 \u0645\u06cc\u200c\u062a\u0648\u0627\u0646\u06cc\u062f \u0627\u0631\u0633\u0627\u0644 \u06a9\u0646\u06cc\u062f.'
                    );
                });
        });


        // \u067e\u0631\u062f\u0627\u062e\u062a/\u062f\u0631\u06af\u0627\u0647: \u06f5 \u062a\u0644\u0627\u0634 \u062f\u0631 \u062f\u0642\u06cc\u0642\u0647
        RateLimiter::for('payment', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(function () {
                    return back()->with(
                        'error',
                        '\u062a\u0639\u062f\u0627\u062f \u062a\u0644\u0627\u0634 \u0628\u0631\u0627\u06cc \u067e\u0631\u062f\u0627\u062e\u062a \u0632\u06cc\u0627\u062f \u0628\u0648\u062f. \u0644\u0637\u0641\u0627\u064b \u06f1 \u062f\u0642\u06cc\u0642\u0647 \u0635\u0628\u0631 \u06a9\u0646\u06cc\u062f.'
                    );
                });
        });
    }


    /**
     * Apply dynamic store mail configuration.
     */
    private function applyStoreMailConfig(): void
    {
        try {
            if (! Schema::hasTable('store_settings')) {
                return;
            }

            StoreSettings::applyMailConfig();
        } catch (\Throwable) {
            // \u062f\u06cc\u062a\u0627\u0628\u06cc\u0633 \u062f\u0631 \u0632\u0645\u0627\u0646 migrate \u06cc\u0627 \u0646\u0635\u0628 \u0627\u0648\u0644\u06cc\u0647 \u062f\u0631 \u062f\u0633\u062a\u0631\u0633 \u0646\u06cc\u0633\u062a
        }
    }
}