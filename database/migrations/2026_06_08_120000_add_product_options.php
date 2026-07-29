<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('sizes')->nullable()->after('weight');
            $table->json('colors')->nullable()->after('sizes');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->json('options')->nullable()->after('product_sku');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sizes', 'colors']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('options');
        });
    }
};
