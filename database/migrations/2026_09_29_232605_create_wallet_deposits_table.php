<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount'); // ریال
            $table->enum('gateway', ['zarinpal', 'c2c']);
            $table->enum('status', ['pending', 'paid', 'rejected', 'expired'])->default('pending');
            $table->string('authority')->nullable()->unique(); // زرین‌پال
            $table->string('receipt_path')->nullable();        // رسید کارت به کارت
            $table->string('tracking_code')->nullable();       // کد رهگیری کارت به کارت
            $table->string('rejection_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_deposits');
    }
};