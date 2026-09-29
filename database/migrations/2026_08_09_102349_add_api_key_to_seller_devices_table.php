
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('seller_devices', 'api_key')) {
            Schema::table('seller_devices', function (Blueprint $table) {
                $table->string('api_key', 64)
                    ->nullable()
                    ->unique()
                    ->after('device_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('seller_devices', 'api_key')) {
            Schema::table('seller_devices', function (Blueprint $table) {
                $table->dropColumn('api_key');
            });
        }
    }
};

