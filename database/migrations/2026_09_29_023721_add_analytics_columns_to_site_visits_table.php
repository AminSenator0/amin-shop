<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->unsignedInteger('page_views')->default(1)->after('visitor_key');
            $table->string('os', 30)->nullable()->after('page_views');
            $table->string('browser', 30)->nullable()->after('os');
        });
    }

    public function down(): void
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->dropColumn(['page_views', 'os', 'browser']);
        });
    }
};