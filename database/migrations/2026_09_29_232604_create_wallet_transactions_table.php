<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['credit', 'debit']);
            // reference_type: zarinpal_deposit | c2c_deposit | order_payment | order_refund
            //                | withdrawal_hold | withdrawal_refund | admin_adjustment
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedBigInteger('amount');          // ریال
            $table->unsignedBigInteger('balance_after');   // موجودی بعد از تراکنش
            $table->string('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            // جلوگیری از تراکنش تکراری برای یک مرجع (سطح دیتابیس)
            $table->unique(['wallet_id', 'reference_type', 'reference_id', 'type'], 'wallet_tx_ref_unique');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};