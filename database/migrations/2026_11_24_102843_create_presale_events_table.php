<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // تایم‌لاین حسابرسی هر سفارش پیش‌فروش (الگوی OrderActivity)
        Schema::create('presale_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presale_order_id')->nullable()->constrained('presale_orders')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->json('payload')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['presale_order_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presale_events');
    }
};