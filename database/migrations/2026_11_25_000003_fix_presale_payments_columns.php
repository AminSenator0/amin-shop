<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presale_payments', function (Blueprint $table) {
            // drop ستون قدیمی (Laravel خودش FK رو هم handle می‌کنه)
            if (Schema::hasColumn('presale_payments', 'presale_order_id')) {
                $table->dropColumn('presale_order_id');
            }

            // اضافه کردن order_id اگه نیست
            if (!Schema::hasColumn('presale_payments', 'order_id')) {
                $table->foreignId('order_id')->constrained('orders');
            }
        });
    }

    public function down(): void
    {
        //
    }
};