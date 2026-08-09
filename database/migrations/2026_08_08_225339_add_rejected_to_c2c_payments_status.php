<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // برای MySQL باید کل ENUM رو دوباره تعریف کنی
        DB::statement("ALTER TABLE c2c_payments MODIFY status ENUM('pending', 'receipt_uploaded', 'verified', 'expired', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE c2c_payments MODIFY status ENUM('pending', 'receipt_uploaded', 'verified', 'expired') NOT NULL DEFAULT 'pending'");
    }
};