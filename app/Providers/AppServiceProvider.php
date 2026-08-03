<?php

namespace App\Providers;

use App\Enums\ReturnStatus;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Review;
use App\Policies\OrderPolicy;
use App\Services\CartService;
use App\Services\ContactMessageService;
use App\Services\FileUploadService;
use App\Services\OrderService;
use App\Services\WishlistService;
use App\Support\StoreSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        Gate::policy(Order::class, OrderPolicy::class);

        Paginator::defaultView('pagination.tailwind');
        Paginator::defaultSimpleView('pagination.simple-tailwind');

        // ═══ Rate Limiting (محدودیت تلاش) ═══
        $this->configureRateLimiting();

        View::composer(['layouts.admin', 'components.admin.sidebar'], function ($view) {
            $view->with([
                'unreadMessagesCount' => ContactMessage::unread()->count(),
                'unreadOrdersCount' => app(OrderService::class)->unreadCount(),
                'pendingReturnsCount' => OrderReturn::where('status', ReturnStatus::Pending)->count(),
                'pendingReviewsCount' => Review::where('is_approved', false)->count(),
            ]);
        });

        View::composer(['layouts.user', 'components.user.sidebar'], function ($view) {
            $user = Auth::user();
            $view->with([
                'unreadUserMessagesCount' => $user ? app(ContactMessageService::class)->unreadCountForUser($user) : 0,
                'wishlistCount' => $user ? app(WishlistService::class)->count() : 0,
                'cartCount' => app(CartService::class)->count(),
                'openReturnsCount' => $user ? $user->openReturnsCount() : 0,
            ]);
        });

        View::composer('*', function ($view) {
            $name = $view->getName();

            if (preg_match('/^(admin\.|components\.admin\.)/', $name) || $name === 'layouts.admin') {
                return;
            }

            if (preg_match('/^(shop|layouts\.(shop|guest|user)|user\.|auth\.|profile\.|invoices\.|components\.(shop|user)\.)/', $name)) {
                $view->with('store', StoreSettings::forShop());
            }
        });

        $this->applyStoreMailConfig();
    }

    private function configureRateLimiting(): void
    {
        // لاگین: ۵ تلاش در دقیقه
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function () {
                return back()->with('error', 'تعداد تلاش‌های ناموفق زیاد بود. لطفاً ۱ دقیقه دیگر تلاش کنید.');
            });
        });

        // ثبت‌نام: ۳ تلاش در دقیقه
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip())->response(function () {
                return back()->with('error', 'تعداد ثبت‌نام بیش از حد مجاز. لطفاً ۱ دقیقه صبر کنید.');
            });
        });

        // کد تخفیف: ۱۰ تلاش در دقیقه
        RateLimiter::for('coupon', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip())->response(function () {
                return back()->with('error', 'تعداد تلاش برای کد تخفیف زیاد بود. لطفاً ۱ دقیقه صبر کنید.');
            });
        });

        // فرم تماس/پیام: ۳ پیام در ساعت
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perHour(5)->by($request->ip())->response(function () {
                return back()->with('error', 'شما در یک ساعت فقط ۳ پیام می‌توانید ارسال کنید.');
            });
        });

        // پرداخت/درگاه: ۵ تلاش در دقیقه
        RateLimiter::for('payment', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function () {
                return back()->with('error', 'تعداد تلاش برای پرداخت زیاد بود. لطفاً ۱ دقیقه صبر کنید.');
            });
        });
    }

    private function applyStoreMailConfig(): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('store_settings')) {
                return;
            }

            StoreSettings::applyMailConfig();
        } catch (\Throwable) {
            // دیتابیس در زمان migrate یا نصب اولیه در دسترس نیست
        }
    }
}