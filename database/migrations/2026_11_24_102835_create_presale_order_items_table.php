<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presale_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presale_order_id')->constrained('presale_orders')->cascadeOnDelete();
            $table->foreignId('presale_product_id')->nullable()->constrained('presale_products')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();

            // snapshot — سفارش حتی با حذف محصول زنده می‌ماند
            $table->string('product_name');
            $table->string('product_sku')->nullable();
            $table->string('product_image')->nullable();

            // قیمت‌ها (تومان) — snapshot موقع سفارش؛ final/penalty موقع همگام‌سازی از پنل
            $table->unsignedBigInteger('presale_price');
            $table->unsignedBigInteger('final_price')->nullable();
            $table->unsignedBigInteger('penalty')->default(0);

            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->index('presale_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presale_order_items');
    }
};