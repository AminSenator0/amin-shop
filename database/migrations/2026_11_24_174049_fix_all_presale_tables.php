<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── 1. presale_campaigns ───
        Schema::table('presale_campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('presale_campaigns', 'title')) {
                $table->string('title');
            }
            if (!Schema::hasColumn('presale_campaigns', 'starts_at')) {
                $table->timestamp('starts_at')->nullable();
            }
            if (!Schema::hasColumn('presale_campaigns', 'ends_at')) {
                $table->timestamp('ends_at')->nullable();
            }
            if (!Schema::hasColumn('presale_campaigns', 'status')) {
                $table->string('status', 20)->default('active');
            }
        });

        // ─── 2. presale_products ───
        Schema::table('presale_products', function (Blueprint $table) {
            if (!Schema::hasColumn('presale_products', 'campaign_id')) {
                $table->unsignedBigInteger('campaign_id');
            }
            if (!Schema::hasColumn('presale_products', 'product_id')) {
                $table->unsignedBigInteger('product_id');
            }
            if (!Schema::hasColumn('presale_products', 'presale_price')) {
                $table->unsignedBigInteger('presale_price');
            }
            if (!Schema::hasColumn('presale_products', 'final_price')) {
                $table->unsignedBigInteger('final_price')->nullable();
            }
            if (!Schema::hasColumn('presale_products', 'penalty_amount')) {
                $table->unsignedBigInteger('penalty_amount')->default(0);
            }
            if (!Schema::hasColumn('presale_products', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (!Schema::hasColumn('presale_products', 'created_at')) {
                $table->timestamps();
            }
        });

        // ─── 3. presale_orders ───
        Schema::table('presale_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('presale_orders', 'user_id')) {
                $table->unsignedBigInteger('user_id');
            }
            if (!Schema::hasColumn('presale_orders', 'campaign_id')) {
                $table->unsignedBigInteger('campaign_id');
            }
            if (!Schema::hasColumn('presale_orders', 'order_number')) {
                $table->string('order_number')->unique();
            }
            if (!Schema::hasColumn('presale_orders', 'status')) {
                $table->string('status', 30)->default('awaiting_final_price');
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
            if (!Schema::hasColumn('presale_orders', 'created_at')) {
                $table->timestamps();
            }
        });

        // ─── 4. presale_order_items ───
        Schema::table('presale_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('presale_order_items', 'presale_order_id')) {
                $table->unsignedBigInteger('presale_order_id');
            }
            if (!Schema::hasColumn('presale_order_items', 'presale_product_id')) {
                $table->unsignedBigInteger('presale_product_id')->nullable();
            }
            if (!Schema::hasColumn('presale_order_items', 'product_name')) {
                $table->string('product_name');
            }
            if (!Schema::hasColumn('presale_order_items', 'product_image')) {
                $table->string('product_image')->nullable();
            }
            if (!Schema::hasColumn('presale_order_items', 'presale_price')) {
                $table->unsignedBigInteger('presale_price');
            }
            if (!Schema::hasColumn('presale_order_items', 'final_price')) {
                $table->unsignedBigInteger('final_price')->nullable();
            }
            if (!Schema::hasColumn('presale_order_items', 'penalty')) {
                $table->unsignedBigInteger('penalty')->default(0);
            }
            if (!Schema::hasColumn('presale_order_items', 'quantity')) {
                $table->unsignedInteger('quantity')->default(1);
            }
            if (!Schema::hasColumn('presale_order_items', 'created_at')) {
                $table->timestamps();
            }
        });

        // ─── 5. presale_payments ───
        Schema::table('presale_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('presale_payments', 'presale_order_id')) {
                $table->unsignedBigInteger('presale_order_id');
            }
            if (!Schema::hasColumn('presale_payments', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable();
            }
            if (!Schema::hasColumn('presale_payments', 'amount')) {
                $table->unsignedBigInteger('amount');
            }
            if (!Schema::hasColumn('presale_payments', 'method')) {
                $table->string('method', 20);
            }
            if (!Schema::hasColumn('presale_payments', 'type')) {
                $table->string('type', 20)->default('installment');
            }
            if (!Schema::hasColumn('presale_payments', 'authority')) {
                $table->string('authority')->nullable()->unique();
            }
            if (!Schema::hasColumn('presale_payments', 'ref_id')) {
                $table->string('ref_id')->nullable();
            }
            if (!Schema::hasColumn('presale_payments', 'exact_rial')) {
                $table->unsignedBigInteger('exact_rial')->nullable();
            }
            if (!Schema::hasColumn('presale_payments', 'status')) {
                $table->string('status', 20)->default('pending');
            }
            if (!Schema::hasColumn('presale_payments', 'paid_at')) {
                $table->timestamp('paid_at')->nullable();
            }
            if (!Schema::hasColumn('presale_payments', 'verified_at')) {
                $table->timestamp('verified_at')->nullable();
            }
            if (!Schema::hasColumn('presale_payments', 'screenshot_path')) {
                $table->string('screenshot_path')->nullable();
            }
            if (!Schema::hasColumn('presale_payments', 'created_at')) {
                $table->timestamps();
            }
        });

        // ─── 6. presale_events ───
        Schema::table('presale_events', function (Blueprint $table) {
            if (!Schema::hasColumn('presale_events', 'presale_order_id')) {
                $table->unsignedBigInteger('presale_order_id');
            }
            if (!Schema::hasColumn('presale_events', 'actor_id')) {
                $table->unsignedBigInteger('actor_id')->nullable();
            }
            if (!Schema::hasColumn('presale_events', 'actor_type')) {
                $table->string('actor_type')->nullable();
            }
            if (!Schema::hasColumn('presale_events', 'action')) {
                $table->string('action');
            }
            if (!Schema::hasColumn('presale_events', 'metadata')) {
                $table->json('metadata')->nullable();
            }
            if (!Schema::hasColumn('presale_events', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        // irreversible safely
    }
};