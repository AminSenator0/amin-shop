<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique();
            $table->string('reason', 255);
            $table->foreignId('blocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('blocked_until')->nullable(); // null = permanent
            $table->unsignedInteger('failed_attempts')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index('ip_address');
            $table->index('blocked_until');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_ips');
    }
};