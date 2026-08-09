<?php
// database/migrations/xxxx_create_c2c_checks_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('c2c_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('c2c_payment_id')->constrained('c2c_payments')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, checking, found, not_found
            $table->decimal('amount', 14, 0); // مبلغ به ریال
            $table->string('tracking_code'); // شناسه واریز
            $table->timestamp('check_from')->nullable(); // از این زمان SMS رو چک کن
            $table->text('result')->nullable(); // JSON نتیجه
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('c2c_checks');
    }
};