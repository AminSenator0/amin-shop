<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presale_products', function (Blueprint $table) {
            $table->json('custom_fields')->nullable()->after('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('presale_products', function (Blueprint $table) {
            $table->dropColumn('custom_fields');
        });
    }
};