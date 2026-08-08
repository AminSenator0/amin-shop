<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // اضافه کردن ستون payment_method به جدول orders (اگه وجود نداشت)
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->default('online')->after('payment_status');
            }
        });

        // جدول پرداخت‌های کارت به کارت (اگه وجود نداشت)
        if (!Schema::hasTable('c2c_payments')) {
            Schema::create('c2c_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->onDelete('cascade');
                $table->unsignedBigInteger('exact_rial');
                $table->timestamp('expires_at');
                $table->enum('status', ['pending', 'verified', 'receipt_uploaded', 'expired'])->default('pending');
                $table->string('receipt_path')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();

                $table->index('exact_rial');
            });
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });

        if (Schema::hasTable('c2c_payments')) {
            Schema::dropIfExists('c2c_payments');
        }
    }
};