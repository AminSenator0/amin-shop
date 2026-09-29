<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seller_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_name');
            $table->string('fcm_token')->nullable();
            $table->string('api_key', 64)->unique()->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('seller_devices');
    }
};