<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('action', 50)->index();
            $table->string('category', 20)->index();
            $table->string('severity', 20)->index();
            $table->string('ip_address', 45)->nullable()->index(); // IPv6 support
            $table->string('user_agent', 500)->nullable();
            $table->string('device_fingerprint', 64)->nullable()->index();
            $table->string('url', 500)->nullable();
            $table->string('method', 10)->nullable();
            $table->json('payload')->nullable(); // sanitized
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('description')->nullable();
            $table->string('reference_type', 50)->nullable(); // App\Models\Order
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('session_id', 128)->nullable()->index();
            $table->timestamp('created_at')->index();

            // Composite indexes for common queries
            $table->index(['category', 'created_at']);
            $table->index(['severity', 'created_at']);
            $table->index(['ip_address', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};