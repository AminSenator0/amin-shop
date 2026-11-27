<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presale_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // سفارش‌دارها حذف نمی‌شوند (محافظت در برابر از دست رفتن داده)
            $table->foreignId('campaign_id')->constrained('presale_campaigns')->restrictOnDelete();
            $table->string('order_number')->unique();
            $table->string('status')->default('awaiting_final_price');

            // مبالغ — همه تومان، همه فقط سمت سرور محاسبه می‌شوند
            $table->unsignedBigInteger('subtotal');                    // مجموع پیش‌پرداخت آیتم‌ها
            $table->unsignedBigInteger('final_total')->nullable();     // مجموع قیمت نهایی آیتم‌ها
            $table->unsignedBigInteger('penalties_total')->default(0); // مجموع جریمه‌ها
            $table->unsignedBigInteger('total_paid')->default(0);      // مجموع کل واریزی (شامل پیش‌پرداخت)

            $table->timestamp('deadline_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presale_orders');
    }
};