<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_type')) {
                $table->string('order_type', 20)->default('normal');
            }
            if (!Schema::hasColumn('orders', 'presale_campaign_id')) {
                $table->foreignId('presale_campaign_id')->nullable()->constrained('presale_campaigns')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'presale_status')) {
                $table->string('presale_status', 30)->nullable();
            }
            if (!Schema::hasColumn('orders', 'presale_final_total')) {
                $table->unsignedBigInteger('presale_final_total')->nullable();
            }
            if (!Schema::hasColumn('orders', 'presale_penalties_total')) {
                $table->unsignedBigInteger('presale_penalties_total')->default(0);
            }
            if (!Schema::hasColumn('orders', 'presale_total_paid')) {
                $table->unsignedBigInteger('presale_total_paid')->default(0);
            }
            if (!Schema::hasColumn('orders', 'presale_deadline_at')) {
                $table->timestamp('presale_deadline_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'presale_completed_at')) {
                $table->timestamp('presale_completed_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'presale_refunded_at')) {
                $table->timestamp('presale_refunded_at')->nullable();
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'presale_product_id')) {
                $table->foreignId('presale_product_id')->nullable()->constrained('presale_products')->nullOnDelete();
            }
            if (!Schema::hasColumn('order_items', 'presale_final_price')) {
                $table->unsignedBigInteger('presale_final_price')->nullable();
            }
            if (!Schema::hasColumn('order_items', 'presale_penalty')) {
                $table->unsignedBigInteger('presale_penalty')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_type', 'presale_campaign_id', 'presale_status',
                'presale_final_total', 'presale_penalties_total', 'presale_total_paid',
                'presale_deadline_at', 'presale_completed_at', 'presale_refunded_at',
            ]);
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['presale_product_id', 'presale_final_price', 'presale_penalty']);
        });
    }
};