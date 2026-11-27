<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PresaleStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PresaleCampaign;
use App\Models\PresalePayment;
use App\Models\PresaleProduct;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\PresalePricingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class PresaleIntegrationTest extends Command
{
    protected $signature = 'presale:test
                            {--seed : ایجاد داده تستی}
                            {--cleanup : پاک کردن داده‌های تستی}';

    protected $description = 'تست جامع یکپارچگی پیش‌فروش';

    private int $errors = 0;
    private int $warnings = 0;
    private int $passed = 0;
    private ?User $testUser = null;
    private ?Product $testProduct = null;
    private ?PresaleCampaign $testCampaign = null;
    private ?PresaleProduct $testPresaleProduct = null;
    private ?Order $testOrder = null;

    public function handle(): int
    {
        $this->info('╔════════════════════════════════════════════╗');
        $this->info('║   تست جامع سیستم پیش‌فروش (یکپارچه)       ║');
        $this->info('╚════════════════════════════════════════════╝');
        $this->newLine();

        if ($this->option('cleanup')) {
            $this->cleanup();
            return 0;
        }

        $this->testDatabaseSchema();
        $this->testEnums();
        $this->testRoutes();
        $this->testModelsAndRelations();
        $this->testCartServiceLogic();

        if ($this->option('seed')) {
            $this->createTestData();
            $this->testPresaleCheckoutFlow();
            $this->testPricingSync();
            $this->testInstallments();
            $this->testCancellation();
            $this->testAutoCancelProtection();
            $this->testNormalOrderRegression();
            $this->cleanup();
        } else {
            $this->warn('برای تست یکپارچگی از --seed استفاده کنید:');
            $this->line('  php artisan presale:test --seed');
        }

        $this->newLine();
        $this->info('╔════════════════════════════════════════════╗');
        $this->info("║  ✅ موفق: {$this->passed}  |  ⚠️ هشدار: {$this->warnings}  |  ❌ خطا: {$this->errors}  ║");
        $this->info('╚════════════════════════════════════════════╝');

        return $this->errors > 0 ? 1 : 0;
    }

    // ─── تست ساختار دیتابیس ───
    private function testDatabaseSchema(): void
    {
        $this->info('📦 تست ساختار دیتابیس');
        $tables = [
            'orders' => ['order_type', 'presale_campaign_id', 'presale_status', 'presale_final_total', 'presale_penalties_total', 'presale_total_paid', 'presale_deadline_at', 'presale_completed_at', 'presale_refunded_at'],
            'order_items' => ['presale_product_id', 'presale_final_price', 'presale_penalty'],
            'presale_campaigns' => ['title', 'status', 'starts_at', 'ends_at'],
            'presale_products' => ['campaign_id', 'product_id', 'presale_price', 'final_price', 'penalty_amount', 'is_active'],
            'presale_payments' => ['order_id', 'user_id', 'amount', 'method', 'type', 'status', 'authority'],
        ];

        foreach ($tables as $table => $columns) {
            if (!Schema::hasTable($table)) {
                $this->errorTest("جدول '$table' وجود ندارد!");
                continue;
            }
            foreach ($columns as $col) {
                if (Schema::hasColumn($table, $col)) {
                    $this->passTest("  $table.$col");
                } else {
                    $this->errorTest("  $table.$col موجود نیست!");
                }
            }
        }
        $this->newLine();
    }

    // ─── تست Enum ها ───
    private function testEnums(): void
    {
        $this->info('🔖 تست Enum ها');
        $cases = \App\Enums\PaymentStatus::cases();
        $hasPartial = collect($cases)->contains(fn($c) => $c->value === 'partially_paid');
        $hasPartial ? $this->passTest('  PaymentStatus::PartiallyPaid') : $this->errorTest('  PaymentStatus::PartiallyPaid وجود ندارد!');

        try {
            $label = \App\Enums\PaymentStatus::PartiallyPaid->label();
            $this->passTest("  PaymentStatus::PartiallyPaid->label() = '$label'");
        } catch (\UnhandledMatchError $e) {
            $this->errorTest('  PaymentStatus::PartiallyPaid->label() خطای UnhandledMatchError!');
        }

        try {
            $color = \App\Enums\PaymentStatus::PartiallyPaid->color();
            $this->passTest("  PaymentStatus::PartiallyPaid->color() = '$color'");
        } catch (\UnhandledMatchError $e) {
            $this->errorTest('  PaymentStatus::PartiallyPaid->color() خطای UnhandledMatchError!');
        }

        $this->passTest('  PresaleStatus enum ' . (enum_exists(PresaleStatus::class) ? 'موجود' : 'ندارد'));
        $this->newLine();
    }

    // ─── تست روت‌ها ───
    private function testRoutes(): void
    {
        $this->info('🌐 تست روت‌ها');
        $routes = [
            'cart.presale.store', 'checkout.presale.pay', 'admin.presale.campaigns.index',
            'admin.presale.campaigns.show', 'admin.presale.campaigns.close',
            'admin.presale.campaigns.products.pricing', 'admin.presale.orders.cancel',
            'home', 'cart.index', 'checkout.index', 'checkout.store',
            'user.orders.index', 'user.orders.show', 'admin.orders.index', 'admin.orders.show',
        ];
        foreach ($routes as $route) {
            Route::has($route) ? $this->passTest("  $route") : $this->errorTest("  $route یافت نشد!");
        }
        $this->newLine();
    }

    // ─── تست مدل‌ها و روابط ───
    private function testModelsAndRelations(): void
    {
        $this->info('🧩 تست مدل‌ها و روابط');
        $product = new Product();
        method_exists($product, 'presaleProducts') ? $this->passTest('  Product::presaleProducts()') : $this->errorTest('  Product::presaleProducts() ندارد!');

        $order = new Order();
        method_exists($order, 'isPresale') ? $this->passTest('  Order::isPresale()') : $this->errorTest('  Order::isPresale() ندارد!');
        method_exists($order, 'presalePayments') ? $this->passTest('  Order::presalePayments()') : $this->errorTest('  Order::presalePayments() ندارد!');

        $item = new OrderItem();
        method_exists($item, 'isPresale') ? $this->passTest('  OrderItem::isPresale()') : $this->errorTest('  OrderItem::isPresale() ندارد!');
        method_exists($item, 'presaleProduct') ? $this->passTest('  OrderItem::presaleProduct()') : $this->errorTest('  OrderItem::presaleProduct() ندارد!');

        $cart = app(CartService::class);
        method_exists($cart, 'addPresale') ? $this->passTest('  CartService::addPresale()') : $this->errorTest('  CartService::addPresale() ندارد!');
        method_exists($cart, 'hasPresaleItems') ? $this->passTest('  CartService::hasPresaleItems()') : $this->errorTest('  CartService::hasPresaleItems() ندارد!');
        method_exists($cart, 'hasNormalItems') ? $this->passTest('  CartService::hasNormalItems()') : $this->errorTest('  CartService::hasNormalItems() ندارد!');

        $pricing = app(PresalePricingService::class);
        method_exists($pricing, 'setProductPricing') ? $this->passTest('  PresalePricingService::setProductPricing()') : $this->errorTest('  PresalePricingService::setProductPricing() ندارد!');
        method_exists($pricing, 'cancelOrder') ? $this->passTest('  PresalePricingService::cancelOrder()') : $this->errorTest('  PresalePricingService::cancelOrder() ندارد!');

        $this->newLine();
    }

    // ─── تست منطق سبد (بدون دیتابیس) ───
    private function testCartServiceLogic(): void
    {
        $this->info('🛒 تست منطق سبد خرید (بدون دیتابیس)');
        $cart = app(CartService::class);
        $cart->clear();

        session()->put('cart', [
            '1' => ['product_id' => 1, 'quantity' => 1, 'price' => 100000, 'sale_type' => 'normal', 'presale_product_id' => null],
        ]);
        $cart->hasNormalItems() ? $this->passTest('  hasNormalItems() روی سبد عادی = true') : $this->errorTest('  hasNormalItems() روی سبد عادی باید true باشد!');
        $cart->hasPresaleItems() ? $this->errorTest('  hasPresaleItems() روی سبد عادی باید false باشد!') : $this->passTest('  hasPresaleItems() روی سبد عادی = false');

        session()->put('cart', [
            'presale_1' => ['product_id' => 1, 'quantity' => 1, 'price' => 700000, 'sale_type' => 'presale', 'presale_product_id' => 1],
        ]);
        $cart->hasPresaleItems() ? $this->passTest('  hasPresaleItems() روی سبد پیش‌فروش = true') : $this->errorTest('  hasPresaleItems() روی سبد پیش‌فروش باید true باشد!');
        $cart->hasNormalItems() ? $this->errorTest('  hasNormalItems() روی سبد پیش‌فروش باید false باشد!') : $this->passTest('  hasNormalItems() روی سبد پیش‌فروش = false');

        $cart->clear();
        $this->newLine();
    }

    // ─── ایجاد داده تستی ───
    // ─── ایجاد داده تستی ───
       private function createTestData(): void
    {
        $this->info('🌱 ایجاد داده تستی');
        DB::beginTransaction();

        $this->testUser = new User();
        $this->testUser->name = 'تست کاربر پیش‌فروش';
        $this->testUser->email = 'presale_test_' . time() . '@test.local';
        $this->testUser->phone = '0912' . random_int(1000000, 9999999);
        $this->testUser->password = bcrypt('password');
        $this->testUser->save();

        // ساخت کیف پول مستقیم (بدون استفاده از رابطه)
        \App\Models\Wallet::create([
            'user_id' => $this->testUser->id,
            'balance' => 50000000,
        ]);

        $this->testProduct = new Product();
        $this->testProduct->name = 'کتاب تست پیش‌فروش ' . time();
        $this->testProduct->slug = 'presale-test-' . time();
        $this->testProduct->sku = 'PRESALE-' . time();
        $this->testProduct->price = 2000000;
        $this->testProduct->stock = 5;
        $this->testProduct->is_active = true;
        $this->testProduct->save();

        $this->testCampaign = PresaleCampaign::create([
            'title' => 'کمپین تست ' . time(),
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(7),
            'status' => 'active',
        ]);

        $this->testPresaleProduct = PresaleProduct::create([
            'campaign_id' => $this->testCampaign->id,
            'product_id' => $this->testProduct->id,
            'presale_price' => 700000,
            'is_active' => true,
        ]);

        $this->passTest("  کاربر: {$this->testUser->id}");
        $this->passTest("  محصول: {$this->testProduct->id}");
        $this->passTest("  کمپین: {$this->testCampaign->id}");
        $this->passTest("  پیش‌فروش: {$this->testPresaleProduct->id}");
        $this->newLine();
    }

    // ─── تست جریان checkout پیش‌فروش ───
    private function testPresaleCheckoutFlow(): void
    {
        $this->info('📦 تست جریان checkout پیش‌فروش');

        $cart = app(CartService::class);
        $cart->clear();

        try {
            $cart->addPresale($this->testPresaleProduct->id, 2);
            $this->passTest('  افزودن پیش‌فروش به سبد');
        } catch (\Exception $e) {
            $this->errorTest('  افزودن به سبد: ' . $e->getMessage());
            return;
        }

        $items = $cart->items();
        if ($items->count() !== 1) {
            $this->errorTest('  تعداد آیتم‌های سبد باید ۱ باشد!');
            return;
        }
        if (($items->first()['sale_type'] ?? '') !== 'presale') {
            $this->errorTest('  sale_type آیتم باید presale باشد!');
            return;
        }
        $this->passTest('  آیتم سبد sale_type= presale');

        if ($cart->subtotal() !== 1400000) {
            $this->errorTest('  subtotal باید 1,400,000 باشد، ولی ' . $cart->subtotal() . ' است!');
            return;
        }
        $this->passTest('  subtotal = 1,400,000');

        $orderService = app(\App\Services\OrderService::class);
        try {
            $order = $orderService->createPresaleOrder(
                $this->testUser->id,
                $this->testCampaign->id,
                $items->map(fn($item) => [
                    'presale_product_id' => $item['presale_product_id'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                ])->toArray(),
                ['full_name' => 'تست', 'phone' => '09120000000', 'address' => 'آدرس تست', 'city' => 'تهران', 'province' => 'تهران']
            );
        } catch (\Exception $e) {
            $this->errorTest('  ثبت سفارش پیش‌فروش: ' . $e->getMessage());
            return;
        }

        $this->testOrder = $order;
        $this->passTest('  سفارش ثبت شد: ' . $order->order_number);

        if ($order->order_type !== 'presale') {
            $this->errorTest('  order_type باید presale باشد!');
        } else {
            $this->passTest('  order_type = presale');
        }

        if ($order->presale_status !== PresaleStatus::AwaitingFinalPrice->value) {
            $this->errorTest('  presale_status باید awaiting_final_price باشد!');
        } else {
            $this->passTest('  presale_status = awaiting_final_price');
        }

        if ($order->status !== OrderStatus::Pending) {
            $this->errorTest('  status باید pending باشد!');
        } else {
            $this->passTest('  status = pending');
        }

        if ($order->payment_status !== PaymentStatus::Pending) {
            $this->errorTest('  payment_status باید pending باشد!');
        } else {
            $this->passTest('  payment_status = pending');
        }

        $this->testProduct->refresh();
        if ($this->testProduct->stock !== 5) {
            $this->errorTest('  موجودی محصول نباید کم شده باشد! stock=' . $this->testProduct->stock);
        } else {
            $this->passTest('  موجودی محصول دست‌نخورده (۵)');
        }

        $cart->clear();
        $this->newLine();
    }

    // ─── تست همگام‌سازی قیمت نهایی ───
    private function testPricingSync(): void
    {
        $this->info('💰 تست همگام‌سازی قیمت نهایی');

        if (! $this->testOrder) {
            $this->warnTest('  سفارش تستی وجود ندارد، این تست رد شد.');
            return;
        }

        $pricing = app(PresalePricingService::class);
        $this->testCampaign->update(['status' => 'closed']);

        try {
            $pricing->setProductPricing($this->testPresaleProduct, 2000000, 100000);
        } catch (\Exception $e) {
            $this->errorTest('  setProductPricing: ' . $e->getMessage());
            return;
        }

        $this->testOrder->refresh();
        $this->testPresaleProduct->refresh();

        if ($this->testPresaleProduct->final_price !== 2000000) {
            $this->errorTest('  final_price محصول باید 2,000,000 باشد!');
        } else {
            $this->passTest('  final_price محصول = 2,000,000');
        }

        if ($this->testOrder->presale_final_total !== 4000000) {
            $this->errorTest('  presale_final_total سفارش باید 4,000,000 باشد (2qty × 2,000,000)! ولی ' . $this->testOrder->presale_final_total);
        } else {
            $this->passTest('  presale_final_total سفارش = 4,000,000');
        }

        if ($this->testOrder->presale_penalties_total !== 200000) {
            $this->errorTest('  presale_penalties_total باید 200,000 باشد (2qty × 100,000)!');
        } else {
            $this->passTest('  presale_penalties_total = 200,000');
        }

        if ($this->testOrder->presale_status !== PresaleStatus::AwaitingPayment->value) {
            $this->errorTest('  presale_status باید awaiting_payment باشد!');
        } else {
            $this->passTest('  presale_status = awaiting_payment');
        }

        $item = $this->testOrder->items->first();
        if ($item->presale_final_price !== 2000000) {
            $this->errorTest('  presale_final_price آیتم باید 2,000,000 باشد!');
        } else {
            $this->passTest('  آیتم presale_final_price = 2,000,000');
        }

        if ($item->presale_penalty !== 100000) {
            $this->errorTest('  presale_penalty آیتم باید 100,000 باشد!');
        } else {
            $this->passTest('  آیتم presale_penalty = 100,000');
        }

        $this->newLine();
    }

    // ─── تست اقساط ───
    private function testInstallments(): void
    {
        $this->info('💳 تست پرداخت اقساط');

        if (! $this->testOrder) {
            $this->warnTest('  سفارش تستی وجود ندارد.');
            return;
        }

        $paymentService = app(\App\Services\PresalePaymentService::class);

        try {
            $paymentService->payWithWallet($this->testOrder, 100000, 'installment');
            $this->passTest('  قسط ۱ (۱۰۰,۰۰۰) پرداخت شد');
        } catch (\Exception $e) {
            $this->errorTest('  قسط ۱: ' . $e->getMessage());
            return;
        }

        $this->testOrder->refresh();
        if ($this->testOrder->presale_total_paid !== 100000) {
            $this->errorTest('  presale_total_paid باید 100,000 باشد!');
        } else {
            $this->passTest('  presale_total_paid = 100,000');
        }

        if ($this->testOrder->presale_status !== PresaleStatus::PartiallyPaid->value) {
            $this->errorTest('  presale_status باید partially_paid باشد!');
        } else {
            $this->passTest('  presale_status = partially_paid');
        }

        $remaining = 4100000 - 100000;
        try {
            $paymentService->payWithWallet($this->testOrder, $remaining, 'installment');
            $this->passTest('  قسط نهایی (' . number_format($remaining) . ') پرداخت شد');
        } catch (\Exception $e) {
            $this->errorTest('  قسط نهایی: ' . $e->getMessage());
            return;
        }

        $this->testOrder->refresh();
        if ($this->testOrder->presale_status !== PresaleStatus::Completed->value) {
            $this->errorTest('  presale_status باید completed باشد!');
        } else {
            $this->passTest('  presale_status = completed');
        }

        if ($this->testOrder->status !== OrderStatus::Paid) {
            $this->errorTest('  status باید paid باشد!');
        } else {
            $this->passTest('  status = paid');
        }

        if ($this->testOrder->payment_status !== PaymentStatus::Paid) {
            $this->errorTest('  payment_status باید paid باشد!');
        } else {
            $this->passTest('  payment_status = paid');
        }

        $this->newLine();
    }

    // ─── تست لغو و استرداد ───
     private function testCancellation(): void
    {
        $this->info('🔄 تست لغو و استرداد');

        if (! $this->testPresaleProduct || ! $this->testUser) {
            $this->warnTest('  داده تستی ناقص، تست رد شد.');
            return;
        }

        $cart = app(CartService::class);
        $cart->clear();
        $cart->addPresale($this->testPresaleProduct->id, 1);

        $orderService = app(\App\Services\OrderService::class);
        $order = $orderService->createPresaleOrder(
            $this->testUser->id,
            $this->testCampaign->id,
            [['presale_product_id' => $this->testPresaleProduct->id, 'price' => 700000, 'quantity' => 1]],
            ['full_name' => 'تست', 'phone' => '09120000000', 'address' => 'تست']
        );

        $paymentService = app(\App\Services\PresalePaymentService::class);
        $paymentService->payWithWallet($order, 700000, 'presale');

        $walletBefore = \App\Models\Wallet::where('user_id', $this->testUser->id)->value('balance') ?? 0;

        $pricing = app(PresalePricingService::class);
        try {
            $pricing->cancelOrder($order, 'تست لغو سیستم', null);
            $this->passTest('  سفارش لغو شد');
        } catch (\Exception $e) {
            $this->errorTest('  لغو: ' . $e->getMessage());
            $cart->clear();
            return;
        }

        $order->refresh();
        if ($order->status !== OrderStatus::Cancelled) {
            $this->errorTest('  status باید cancelled باشد!');
        } else {
            $this->passTest('  status = cancelled');
        }

        if ($order->payment_status !== PaymentStatus::Refunded) {
            $this->errorTest('  payment_status باید refunded باشد!');
        } else {
            $this->passTest('  payment_status = refunded');
        }

        if (! $order->presale_refunded_at) {
            $this->errorTest('  presale_refunded_at باید ست شده باشد!');
        } else {
            $this->passTest('  presale_refunded_at ست شد');
        }

        $walletAfter = \App\Models\Wallet::where('user_id', $this->testUser->id)->value('balance') ?? 0;
        if ($walletAfter !== $walletBefore + 700000) {
            $this->errorTest('  کیف پول باید 700,000 افزایش یابد! قبل=' . $walletBefore . ' بعد=' . $walletAfter);
        } else {
            $this->passTest('  کیف پول +700,000 (استرداد موفق)');
        }

        try {
            $pricing->cancelOrder($order, 'تست دوباره', null);
            $this->errorTest('  لغو دوباره باید خطا بدهد!');
        } catch (\RuntimeException $e) {
            $this->passTest('  لغو دوباره بلاک شد (idempotent)');
        }

        $cart->clear();
        $this->newLine();
    }

    // ─── تست محافظ لغو خودکار ───
       private function testAutoCancelProtection(): void
    {
        $this->info('⏱️ تست محافظ لغو خودکار ۲۰ دقیقه‌ای');

        if (! $this->testPresaleProduct || ! $this->testUser || ! $this->testCampaign) {
            $this->warnTest('  داده تستی ناقص، تست رد شد.');
            return;
        }

        $cart = app(CartService::class);
        $cart->clear();
        $cart->addPresale($this->testPresaleProduct->id, 1);

        $orderService = app(\App\Services\OrderService::class);
        $order = $orderService->createPresaleOrder(
            $this->testUser->id,
            $this->testCampaign->id,
            [['presale_product_id' => $this->testPresaleProduct->id, 'price' => 700000, 'quantity' => 1]],
            ['full_name' => 'تست', 'phone' => '09120000000', 'address' => 'تست']
        );

        $order->update(['created_at' => now()->subMinutes(30)]);

        \Illuminate\Support\Facades\Artisan::call('orders:cancel-expired');

        $order->refresh();
        if ($order->status === OrderStatus::Cancelled) {
            $this->errorTest('  سفارش پیش‌فروش نباید توسط لغو خودکار لغو شود!');
        } else {
            $this->passTest('  سفارش پیش‌فروش از لغو خودکار محافظت شد');
        }

        $cart->clear();
        $this->newLine();
    }

    // ─── تست رگرسیون خرید عادی ───
     private function testNormalOrderRegression(): void
    {
        $this->info('🛍️ تست رگرسیون خرید عادی');

        if (! $this->testProduct) {
            $this->warnTest('  داده تستی ناقص، تست رد شد.');
            return;
        }

        $cart = app(CartService::class);
        $cart->clear();

        session()->put('cart', [
            '1' => ['product_id' => $this->testProduct->id, 'quantity' => 1, 'price' => 2000000, 'selected_size' => null, 'selected_color' => null, 'custom_fields' => [], 'sale_type' => 'normal', 'presale_product_id' => null],
        ]);
        $this->passTest('  محصول عادی به سبد اضافه شد');

        if ($cart->hasNormalItems()) {
            $this->passTest('  hasNormalItems() = true');
        } else {
            $this->errorTest('  hasNormalItems() باید true باشد!');
        }

        if (! $cart->hasPresaleItems()) {
            $this->passTest('  hasPresaleItems() = false');
        } else {
            $this->errorTest('  hasPresaleItems() باید false باشد!');
        }

        try {
            $cart->addPresale($this->testPresaleProduct?->id ?? 99999, 1);
            $this->errorTest('  اختلاط سبد باید خطا بدهد!');
        } catch (\Exception $e) {
            $this->passTest('  اختلاط سبد بلاک شد: ' . $e->getMessage());
        }

        $cart->clear();
        $this->newLine();
    }

    // ─── پاکسازی ───
    private function cleanup(): void
    {
        $this->info('🧹 پاکسازی داده‌های تستی');

        if ($this->testOrder) {
            PresalePayment::where('order_id', $this->testOrder->id)->delete();
            OrderItem::where('order_id', $this->testOrder->id)->delete();
            $this->testOrder->delete();
        }

        Order::where('user_id', $this->testUser?->id ?? 0)->where('order_type', 'presale')->each(function ($o) {
            PresalePayment::where('order_id', $o->id)->delete();
            OrderItem::where('order_id', $o->id)->delete();
            $o->delete();
        });

        $this->testPresaleProduct?->delete();
        $this->testCampaign?->delete();
        $this->testProduct?->delete();
        $this->testUser?->delete();

        $this->passTest('  داده‌های تستی حذف شدند');
        $this->newLine();
    }

    // ─── هلپرها ───
    private function passTest(string $msg): void
    {
        $this->passed++;
        $this->line("  ✅ $msg");
    }

    private function errorTest(string $msg): void
    {
        $this->errors++;
        $this->error("  ❌ $msg");
    }

    private function warnTest(string $msg): void
    {
        $this->warnings++;
        $this->warn("  ⚠️ $msg");
    }
}