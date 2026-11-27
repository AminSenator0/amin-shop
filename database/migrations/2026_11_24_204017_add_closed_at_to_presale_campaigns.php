<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presale_campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('presale_campaigns', 'closed_at')) {
                $table->timestamp('closed_at')->nullable();
            }
            if (!Schema::hasColumn('presale_campaigns', 'is_extended')) {
                $table->boolean('is_extended')->default(false);
            }
            if (!Schema::hasColumn('presale_campaigns', 'extended_at')) {
                $table->timestamp('extended_at')->nullable();
            }
        });
    }

    public function down(): void {}
};