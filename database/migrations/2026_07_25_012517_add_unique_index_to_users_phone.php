<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // پاک‌سازی شماره‌های تکراری قبل از ایندکس یکتا (نگه داشتن کوچک‌ترین id)
        $duplicates = DB::table('users')
            ->select('phone')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->groupBy('phone')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('phone');

        foreach ($duplicates as $phone) {
            $keepId = DB::table('users')->where('phone', $phone)->orderBy('id')->value('id');
            DB::table('users')
                ->where('phone', $phone)
                ->where('id', '!=', $keepId)
                ->update(['phone' => null]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
        });
    }
};
