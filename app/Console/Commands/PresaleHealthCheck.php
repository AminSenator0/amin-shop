<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class PresaleHealthCheck extends Command
{
    protected $signature = 'presale:health-check';
    protected $description = 'بررسی سلامت یکپارچگی پیش‌فروش با فروشگاه';

    public function handle(): int
    {
        $this->info('🔍 شروع بررسی سلامت سیستم...');
        $this->newLine();

        $errors = 0;
        $warnings = 0;

        // ─── 1. دیتابیس ───
        $this->info('📦 1. بررسی ستون‌های دیتابیس');
        $requiredOrderCols = [
            'order_type', 'presale_campaign_id', 'presale_status',
            'presale_final_total', 'presale_penalties_total', 'presale_total_paid',
            'presale_deadline_at', 'presale_completed_at', 'presale_refunded_at',
        ];
        foreach ($requiredOrderCols as $col) {
            if (Schema::hasColumn('orders', $col)) {
                $this->line("   ✅ orders.$col");
            } else {
                $this->error("   ❌ orders.$col موجود نیست!");
                $errors++;
            }
        }

        $requiredItemCols = ['presale_product_id', 'presale_final_price', 'presale_penalty'];
        foreach ($requiredItemCols as $col) {
            if (Schema::hasColumn('order_items', $col)) {
                $this->line("   ✅ order_items.$col");
            } else {
                $this->error("   ❌ order_items.$col موجود نیست!");
                $errors++;
            }
        }

        if (Schema::hasColumn('presale_payments', 'order_id')) {
            $this->line("   ✅ presale_payments.order_id");
        } else {
            $this->warn("   ⚠️ presale_payments.order_id هنوز اضافه نشده");
            $warnings++;
        }
        $this->newLine();

        // ─── 2. روت‌ها ───
        $this->info('🌐 2. بررسی روت‌های کلیدی');
        $requiredRoutes = [
            'home',
            'products.index',
            'products.show',
            'cart.index',
            'cart.store',
            'cart.presale.store',
            'checkout.index',
            'checkout.store',
            'user.orders.index',
            'user.orders.show',
            'admin.orders.index',
            'admin.presale.campaigns.index',
            'admin.presale.campaigns.show',
        ];
        foreach ($requiredRoutes as $routeName) {
            if (Route::has($routeName)) {
                $this->line("   ✅ $routeName");
            } else {
                $this->error("   ❌ $routeName یافت نشد!");
                $errors++;
            }
        }
        $this->newLine();

        // ─── 3. enum ها ───
        $this->info('🔖 3. بررسی Enum ها');
        $enumFile = app_path('Enums/PaymentStatus.php');
        if (file_exists($enumFile)) {
            $content = file_get_contents($enumFile);
            if (str_contains($content, 'PartiallyPaid')) {
                $this->line("   ✅ PaymentStatus::PartiallyPaid");
            } else {
                $this->error("   ❌ PaymentStatus::PartiallyPaid یافت نشد!");
                $errors++;
            }
        } else {
            $this->error("   ❌ فایل PaymentStatus.php یافت نشد!");
            $errors++;
        }

        $enumFile2 = app_path('Enums/PresaleStatus.php');
        if (file_exists($enumFile2)) {
            $this->line("   ✅ PresaleStatus enum موجود");
        } else {
            $this->error("   ❌ PresaleStatus enum یافت نشد!");
            $errors++;
        }
        $this->newLine();

        // ─── 4. مدل‌ها ───
        $this->info('🧩 4. بررسی مدل‌ها و روابط');
        $models = [
            'App\Models\PresaleCampaign',
            'App\Models\PresaleProduct',
            'App\Models\PresalePayment',
            'App\Models\Order',
            'App\Models\OrderItem',
        ];
        foreach ($models as $model) {
            if (class_exists($model)) {
                $this->line("   ✅ $model");
            } else {
                $this->error("   ❌ $model یافت نشد!");
                $errors++;
            }
        }

        // چک رابطه presaleProducts روی Product
        $product = new \App\Models\Product();
        if (method_exists($product, 'presaleProducts')) {
            $this->line("   ✅ Product::presaleProducts()");
        } else {
            $this->error("   ❌ Product::presaleProducts() یافت نشد!");
            $errors++;
        }
        $this->newLine();

        // ─── 5. سرویس‌ها ───
        $this->info('⚙️ 5. بررسی سرویس‌ها');
        $services = [
            'App\Services\CartService',
            'App\Services\PresalePricingService',
            'App\Services\PresalePaymentService',
        ];
        foreach ($services as $service) {
            if (class_exists($service)) {
                $this->line("   ✅ $service");
            } else {
                $this->error("   ❌ $service یافت نشد!");
                $errors++;
            }
        }

        $cartService = app(\App\Services\CartService::class);
        if (method_exists($cartService, 'addPresale')) {
            $this->line("   ✅ CartService::addPresale()");
        } else {
            $this->error("   ❌ CartService::addPresale() یافت نشد!");
            $errors++;
        }
        if (method_exists($cartService, 'hasPresaleItems')) {
            $this->line("   ✅ CartService::hasPresaleItems()");
        } else {
            $this->error("   ❌ CartService::hasPresaleItems() یافت نشد!");
            $errors++;
        }
        if (method_exists($cartService, 'hasNormalItems')) {
            $this->line("   ✅ CartService::hasNormalItems()");
        } else {
            $this->error("   ❌ CartService::hasNormalItems() یافت نشد!");
            $errors++;
        }
        if (method_exists($cartService, 'quantityFor')) {
            $this->line("   ✅ CartService::quantityFor()");
        } else {
            $this->error("   ❌ CartService::quantityFor() یافت نشد!");
            $errors++;
        }
        $this->newLine();

        // ─── 6. جداول presale قدیمی (هشدار) ───
        $this->info('🗄️ 6. بررسی جداول قدیمی پیش‌فروش');
        $oldTables = ['presale_orders', 'presale_order_items', 'presale_events'];
        foreach ($oldTables as $table) {
            if (Schema::hasTable($table)) {
                $this->warn("   ⚠️ جدول '$table' هنوز وجود دارد (بعد از تکمیل پروژه حذف شود)");
                $warnings++;
            } else {
                $this->line("   ✅ جدول '$table' حذف شده");
            }
        }
        $this->newLine();

        // ─── 7. خلاصه ───
        $this->info('📋 نتیجه نهایی:');
        if ($errors === 0 && $warnings === 0) {
            $this->info('   🎉 همه چی سالمه! می‌تونی مرحله بعدی رو شروع کنی.');
        } elseif ($errors === 0) {
            $this->warn("   ⚠️ $warnings هشدار داری ولی خطا نداری. می‌تونی ادامه بدی.");
        } else {
            $this->error("   ❌ $errors خطا و $warnings هشدار. خطاها رو فیکس کن بعد ادامه بده.");
        }

        return $errors > 0 ? 1 : 0;
    }
}