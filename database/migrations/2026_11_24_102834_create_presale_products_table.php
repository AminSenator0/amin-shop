<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presale_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('presale_campaigns')->cascadeOnDelete();
            // nullable تا حذف محصول اصلی هیچ‌وقت مانع نشه (آیتم‌های سفارش snapshot دارند)
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->unsignedBigInteger('presale_price');              // پیش‌پرداخت (تومان)
            $table->unsignedBigInteger('final_price')->nullable();    // قیمت نهایی — فقط از پنل پیش‌فروش
            $table->unsignedBigInteger('penalty_amount')->default(0); // جریمه هر واحد (تومان)
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['campaign_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presale_products');
    }
};