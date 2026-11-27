<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presale_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('presale_orders', 'user_id')) {
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('presale_orders', 'campaign_id')) {
                $table->foreignId('campaign_id')->constrained('presale_campaigns')->restrictOnDelete();
            }
            if (!Schema::hasColumn('presale_orders', 'order_number')) {
                $table->string('order_number')->unique();
            }
            if (!Schema::hasColumn('presale_orders', 'status')) {
                $table->string('status')->default('awaiting_final_price');
            }
            if (!Schema::hasColumn('presale_orders', 'subtotal')) {
                $table->unsignedBigInteger('subtotal');
            }
            if (!Schema::hasColumn('presale_orders', 'final_total')) {
                $table->unsignedBigInteger('final_total')->nullable();
            }
            if (!Schema::hasColumn('presale_orders', 'penalties_total')) {
                $table->unsignedBigInteger('penalties_total')->default(0);
            }
            if (!Schema::hasColumn('presale_orders', 'total_paid')) {
                $table->unsignedBigInteger('total_paid')->default(0);
            }
            if (!Schema::hasColumn('presale_orders', 'deadline_at')) {
                $table->timestamp('deadline_at')->nullable();
            }
            if (!Schema::hasColumn('presale_orders', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
            if (!Schema::hasColumn('presale_orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable();
            }
            if (!Schema::hasColumn('presale_orders', 'cancel_reason')) {
                $table->text('cancel_reason')->nullable();
            }
            if (!Schema::hasColumn('presale_orders', 'shipping_address')) {
                $table->json('shipping_address')->nullable();
            }
        });
    }

    public function down(): void
    {
        // irreversible safely
    }
};