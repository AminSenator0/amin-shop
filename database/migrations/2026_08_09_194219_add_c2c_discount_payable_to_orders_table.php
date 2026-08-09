<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'c2c_discount')) {
                $table->decimal('c2c_discount', 14, 0)->default(0)->after('total');
            }
            if (!Schema::hasColumn('orders', 'payable')) {
                $table->decimal('payable', 14, 0)->default(0)->after('c2c_discount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['c2c_discount', 'payable']);
        });
    }
};