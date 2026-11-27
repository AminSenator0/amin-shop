<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presale_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('presale_payments', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('presale_order_id')->constrained('orders')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('presale_payments', function (Blueprint $table) {
            $table->dropColumn('order_id');
        });
    }
};