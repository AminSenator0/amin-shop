<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presale_order_id')->constrained('presale_orders')->cascadeOnDelete();
            $table->unsignedBigInteger('amount'); // تومان
            $table->string('method');             // zarinpal|c2c
            $table->string('type')->default('installment'); // presale=پیش‌پرداخت اول | installment=قسط
            $table->string('status')->default('pending');   // pending|verified|failed

            // زرین‌پال
            $table->string('authority')->nullable()->unique();
            $table->string('ref_id')->nullable();

            // کارت‌به‌کارت (مبلغ دقیق ریالی + انقضا)
            $table->unsignedBigInteger('exact_rial')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->string('fail_reason')->nullable();
            $table->timestamps();

            $table->index(['presale_order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presale_payments');
    }
};