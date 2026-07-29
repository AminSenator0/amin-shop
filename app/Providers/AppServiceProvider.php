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
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FileUploadService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Order::class, OrderPolicy::class);

        Paginator::defaultView('pagination.tailwind');
        Paginator::defaultSimpleView('pagination.simple-tailwind');

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
