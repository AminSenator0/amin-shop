<?php

use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FinancialController as AdminFinancialController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ShippingMethodController;
use App\Http\Controllers\Admin\SliderController as AdminSliderController;
use App\Http\Controllers\Admin\StoreSettingsController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Models\Category;
use App\Models\Product;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\ReturnController as AdminReturnController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\ContactController;
use App\Http\Controllers\Shop\CouponController;
use App\Http\Controllers\Shop\ReviewController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BlockedIpController;
use App\Http\Controllers\Admin\SecurityAlertController;
use App\Http\Controllers\Admin\BlogPostController as AdminBlogPostController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\HomepageController as AdminHomepageController;
use App\Http\Controllers\Admin\NewsletterSubscriberController;
use App\Http\Controllers\Shop\BlogController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\NewsletterController;
use App\Http\Controllers\Shop\SearchController;
use App\Http\Controllers\Shop\OrderTrackingController;
use App\Http\Controllers\Shop\PageController;
use App\Http\Controllers\Shop\PaymentController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\Shop\WishlistController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\User\AddressController;
use App\Http\Controllers\User\CartController as UserCartController;
use App\Http\Controllers\User\CouponController as UserCouponController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\MessageController as UserMessageController;
use App\Http\Controllers\User\OrderController as UserOrderController;
use App\Http\Controllers\User\ReturnController as UserReturnController;
use App\Http\Controllers\User\ReviewController as UserReviewController;
use App\Http\Controllers\User\WishlistController as UserWishlistController;

// ===== کنترلرهای کارت به کارت =====
use App\Http\Controllers\Payment\C2CPaymentController;                           // برای آپلود رسید و پرداخت دستی
use App\Http\Controllers\Shop\C2CPaymentController as ShopC2CPaymentController; // برای درخواست بررسی و وضعیت
use App\Http\Controllers\Admin\C2CPaymentController as AdminC2CPaymentController; // برای مدیریت در ادمین

use App\Support\StoreSettings;
use Illuminate\Support\Facades\Route;

Route::get('/favicon.ico', function () {
    $url = StoreSettings::faviconUrl();

    return redirect($url, 302, ['Cache-Control' => 'public, max-age=86400']);
});

// ============================
// فروشگاه (عمومی)
// ============================
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search/suggest', [SearchController::class, 'suggest'])->name('search.suggest');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{lineKey}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{lineKey}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/faq', [PageController::class, 'faq'])->name('pages.faq');
Route::get('/about', [PageController::class, 'about'])->name('pages.about');
Route::get('/rules', [PageController::class, 'rules'])->name('pages.rules');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:contact')
    ->name('contact.store');
Route::get('/track-order', [OrderTrackingController::class, 'show'])->name('orders.track');
Route::post('/newsletter', [NewsletterController::class, 'subscribe'])->middleware('throttle:5,1')->name('newsletter.subscribe');
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
Route::get('/sitemap.xml', function () {
    $categories = Category::where('is_active', true)->get();
    $products = Product::where('is_active', true)->get();

    return response()
        ->view('sitemap', compact('categories', 'products'))
        ->header('Content-Type', 'application/xml');
})->name('sitemap');

// ============================
// فروشگاه (کاربران لاگین‌شده)
// ============================
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::post('/checkout/coupon', [CouponController::class, 'apply'])->middleware('throttle:10,1')->name('checkout.coupon.apply');
    Route::delete('/checkout/coupon', [CouponController::class, 'destroy'])->name('checkout.coupon.destroy');
    Route::get('/checkout/payment/{order}', [CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::post('/checkout/payment/{order}', [CheckoutController::class, 'processPayment'])
        ->middleware('throttle:payment')
        ->name('checkout.payment.process');
    Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('products.reviews.store');

    // ========== کارت به کارت (فقط این ۵ خط) ==========
    Route::get('/c2c/{order}', [\App\Http\Controllers\Payment\C2CPaymentController::class, 'show'])->name('c2c.show');
    Route::post('/c2c/check/{order}', [\App\Http\Controllers\Payment\C2CPaymentController::class, 'checkStatus'])->name('c2c.check');
    Route::post('/c2c/receipt/{order}', [\App\Http\Controllers\Payment\C2CPaymentController::class, 'uploadReceipt'])->name('c2c.receipt');
    Route::post('/c2c/request-check/{c2cPayment}', [\App\Http\Controllers\Shop\C2CPaymentController::class, 'requestCheck'])->name('c2c.request-check');
    Route::get('/c2c/check-status/{check}', [\App\Http\Controllers\Shop\C2CPaymentController::class, 'checkStatus'])->name('c2c.check-status');

    // ===== پنل کاربری =====
    Route::prefix('account')->name('user.')->group(function () {
        Route::get('/', [UserDashboardController::class, 'index'])->name('dashboard');
        Route::get('/orders', [UserOrderController::class, 'index'])->name('orders.index');
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::get('/orders/{order}', [UserOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/cancel', [UserOrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/orders/{order}/reorder', [UserOrderController::class, 'reorder'])->name('orders.reorder');
        Route::post('/orders/{order}/returns', [UserReturnController::class, 'store'])->name('orders.returns.store');
        Route::get('/orders/{order}/invoice', [InvoiceController::class, 'show'])->name('orders.invoice');
        Route::get('/wishlist', [UserWishlistController::class, 'index'])->name('wishlist.index');
        Route::get('/reviews', [UserReviewController::class, 'index'])->name('reviews.index');
        Route::get('/returns', [UserReturnController::class, 'index'])->name('returns.index');
        Route::resource('addresses', AddressController::class)->except(['show']);
        Route::get('/coupons', [UserCouponController::class, 'index'])->name('coupons.index');
        Route::post('/coupons', [UserCouponController::class, 'store'])->middleware('throttle:10,1')->name('coupons.store');
        Route::post('/coupons/{coupon}/apply', [UserCouponController::class, 'apply'])
            ->middleware('throttle:coupon')
            ->name('coupons.apply');
        Route::delete('/coupons/{coupon}', [UserCouponController::class, 'destroy'])->name('coupons.destroy');
        Route::get('/messages', [UserMessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/new', [UserMessageController::class, 'create'])->name('messages.create');
        Route::post('/messages', [UserMessageController::class, 'store'])
            ->middleware('throttle:contact')
            ->name('messages.store');
        Route::get('/messages/{message}', [UserMessageController::class, 'show'])->name('messages.show');
        Route::post('/messages/{message}/reply', [UserMessageController::class, 'reply'])->name('messages.reply');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ============================
// ورود به پنل مدیریت
// ============================
Route::prefix('admin')->name('admin.')->middleware('guest')->group(function () {
    Route::get('login', [AdminLoginController::class, 'create'])->name('login');
    Route::post('login', [AdminLoginController::class, 'store']);
});

// ============================
// پنل مدیریت (ادمین)
// ============================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/export', [AdminDashboardController::class, 'export'])->name('dashboard.export');
    Route::get('financial', [AdminFinancialController::class, 'index'])->name('financial.index');
    Route::get('financial/export', [AdminFinancialController::class, 'export'])->name('financial.export');
    Route::get('products/form-upload/{key}', [AdminProductController::class, 'formUpload'])->name('products.form-upload');
    Route::resource('products', AdminProductController::class)->except(['show']);
    Route::delete('products/{product}/images/{image}', [AdminProductController::class, 'destroyImage'])->name('products.images.destroy');
    Route::resource('categories', AdminCategoryController::class)->except(['show']);
    Route::get('orders/export', [AdminOrderController::class, 'export'])->name('orders.export');
    Route::patch('orders/mark-all-read', [AdminOrderController::class, 'markAllRead'])->name('orders.mark-all-read');
    Route::get('orders/create', [AdminOrderController::class, 'create'])->name('orders.create');
    Route::post('orders', [AdminOrderController::class, 'store'])->name('orders.store');
    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/invoice', [InvoiceController::class, 'show'])->name('orders.invoice');
    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::patch('orders/{order}/tracking', [AdminOrderController::class, 'updateTracking'])->name('orders.tracking');
    Route::patch('orders/{order}/payment-status', [AdminOrderController::class, 'updatePaymentStatus'])->name('orders.payment-status');
    Route::post('orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');
    Route::patch('orders/{order}/internal-notes', [AdminOrderController::class, 'updateInternalNotes'])->name('orders.internal-notes');

    // ===== مدیریت پرداخت‌های کارت به کارت (ادمین) =====
    Route::get('c2c-payments', [AdminC2CPaymentController::class, 'index'])->name('c2c.index');
    Route::post('c2c-payments/{payment}/verify', [AdminC2CPaymentController::class, 'verify'])->name('c2c.verify');
    Route::post('c2c-payments/{payment}/reject', [AdminC2CPaymentController::class, 'reject'])->name('c2c.reject');

    Route::resource('brands', AdminBrandController::class)->except(['show']);
    Route::get('returns', [AdminReturnController::class, 'index'])->name('returns.index');
    Route::post('orders/{order}/returns', [AdminReturnController::class, 'store'])->name('returns.store');
    Route::patch('returns/{orderReturn}/status', [AdminReturnController::class, 'updateStatus'])->name('returns.status');
    Route::get('messages', [ContactMessageController::class, 'index'])->name('messages.index');
    Route::patch('messages/mark-all-read', [ContactMessageController::class, 'markAllRead'])->name('messages.mark-all-read');
    Route::get('messages/{message}', [ContactMessageController::class, 'show'])->name('messages.show');
    Route::post('messages/{message}/reply', [ContactMessageController::class, 'reply'])->name('messages.reply');
    Route::patch('messages/{message}/unread', [ContactMessageController::class, 'markUnread'])->name('messages.unread');
    Route::delete('messages/{message}', [ContactMessageController::class, 'destroy'])->name('messages.destroy');
    Route::get('users/export', [AdminUserController::class, 'export'])->name('users.export');
    Route::get('users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::patch('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::resource('shipping', ShippingMethodController::class)->except(['show']);
    Route::resource('coupons', AdminCouponController::class)->except(['show']);
    Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::patch('reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
    Route::patch('reviews/{review}/reject', [AdminReviewController::class, 'reject'])->name('reviews.reject');
    Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::resource('sliders', AdminSliderController::class)->except(['show']);
    Route::get('banners/form-upload/{key}', [AdminBannerController::class, 'formUpload'])->name('banners.form-upload');
    Route::resource('banners', AdminBannerController::class)->except(['show']);
    Route::get('homepage', [AdminHomepageController::class, 'index'])->name('homepage.index');
    Route::put('homepage', [AdminHomepageController::class, 'update'])->name('homepage.update');
    Route::resource('faqs', AdminFaqController::class)->except(['show']);
    Route::resource('blog-posts', AdminBlogPostController::class)->except(['show']);
    Route::get('newsletter/export', [NewsletterSubscriberController::class, 'export'])->name('newsletter.export');
    Route::get('newsletter', [NewsletterSubscriberController::class, 'index'])->name('newsletter.index');
    Route::delete('newsletter/{newsletterSubscriber}', [NewsletterSubscriberController::class, 'destroy'])->name('newsletter.destroy');
    Route::get('settings', [StoreSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [StoreSettingsController::class, 'update'])->name('settings.update');

    Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
        Route::get('/', [AuditLogController::class, 'dashboard'])->name('dashboard');
        Route::get('/logs', [AuditLogController::class, 'index'])->name('index');
        Route::get('/export', [AuditLogController::class, 'export'])->name('export');
        Route::get('/ip/{ip}', [AuditLogController::class, 'ipDetail'])->name('ip-detail');
        Route::get('/user/{user}/activity', [AuditLogController::class, 'userActivity'])->name('user-activity');
        Route::get('/admin/{admin}/activity', [AuditLogController::class, 'adminActivity'])->name('admin-activity');
        Route::get('{auditLog}/show', [AuditLogController::class, 'show'])->name('show');

        Route::get('/alerts', [SecurityAlertController::class, 'index'])->name('alerts');
        Route::patch('/alerts/{alert}/resolve', [SecurityAlertController::class, 'resolve'])->name('alerts.resolve');

        Route::get('/blocked-ips', [BlockedIpController::class, 'index'])->name('blocked-ips');
        Route::post('/blocked-ips', [BlockedIpController::class, 'store'])->name('blocked-ips.store');
        Route::delete('/blocked-ips/{blockedIp}', [BlockedIpController::class, 'destroy'])->name('blocked-ips.destroy');
    });
});

require __DIR__.'/auth.php';
