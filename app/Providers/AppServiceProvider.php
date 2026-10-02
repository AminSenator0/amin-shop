<?php

namespace App\Providers;

use App\Enums\ReturnStatus;
use App\Listeners\LogAuthenticationEvents;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\Order;
use App\Observers\OrderObserver;
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
use Illuminate\Support\Facades\Log;
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
        Order::observe(OrderObserver::class);  // ← اضافه شد


        // ═══════════════════════════════════════════════
        // Event Listeners (Audit Logging)
        // ═══════════════════════════════════════════════

        // ثبت نشست فعال کاربر هنگام ورود (IP جدید = نشست جدید، مثل تلگرام)
        Event::listen(Login::class, function (Login $event) {
            if (app()->runningInConsole()) {
                return;
            }

            try {
                app(\App\Services\AccountSessionService::class)
                    ->record(request(), $event->user);
            } catch (\Throwable $e) {
                Log::error('AccountSession record failed: '.$e->getMessage());
            }
        });

        // Event::listen(Login::class, [LogAuthenticationEvents::class, 'handleLogin']);
        // Event::listen(Failed::class, [LogAuthenticationEvents::class, 'handleFailed']);
        // Event::listen(Logout::class, [LogAuthenticationEvents::class, 'handleLogout']);

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
                    // ← جدید: بج‌های سایدبار
                    'pendingC2cCount' => \App\Models\C2CPayment::whereIn('status', ['pending', 'receipt_uploaded'])->count(),
                    'pendingWalletDepositsCount' => \App\Models\WalletDeposit::pending()->count(),
                    'pendingWalletWithdrawalsCount' => \App\Models\WalletWithdrawal::pending()->count(),
                    'mySessionsCount' => Auth::user()
                        ? \App\Models\AccountSession::where('user_id', Auth::user()->id)->count()
                        : 0,
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
        // لاگین: ۵ تلاش در دقیقه
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(function () {
                    return back()->with(
                        'error',
                        'تعداد تلاش‌های ناموفق زیاد بود. لطفاً ۱ دقیقه دیگر تلاش کنید.'
                    );
                });
        });


        // ثبت‌نام: ۳ تلاش در دقیقه
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(3)
                ->by($request->ip())
                ->response(function () {
                    return back()->with(
                        'error',
                        'تعداد ثبت‌نام بیش از حد مجاز. لطفاً ۱ دقیقه صبر کنید.'
                    );
                });
        });


        // کد تخفیف: ۱۰ تلاش در دقیقه
        RateLimiter::for('coupon', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->ip())
                ->response(function () {
                    return back()->with(
                        'error',
                        'تعداد تلاش برای کد تخفیف زیاد بود. لطفاً ۱ دقیقه صبر کنید.'
                    );
                });
        });


        RateLimiter::for('seller-poll', function (Request $request) {
            // فقط ۱ درخواست در هر ۳ ثانیه از هر IP/دیوایس
            return Limit::perMinute(20)->by($request->ip());
        });

        // فرم تماس/پیام: ۵ پیام در ساعت
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perHour(5)
                ->by($request->ip())
                ->response(function () {
                    return back()->with(
                        'error',
                        'شما در یک ساعت فقط ۵ پیام می‌توانید ارسال کنید.'
                    );
                });
        });


        // پرداخت/درگاه: ۵ تلاش در دقیقه
        RateLimiter::for('payment', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(function () {
                    return back()->with(
                        'error',
                        'تعداد تلاش برای پرداخت زیاد بود. لطفاً ۱ دقیقه صبر کنید.'
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
            // دیتابیس در زمان migrate یا نصب اولیه در دسترس نیست
        }
    }
}