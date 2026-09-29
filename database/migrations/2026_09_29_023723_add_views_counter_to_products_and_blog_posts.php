<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('views')->default(0)->after('is_featured');
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->unsignedBigInteger('views')->default(0)->after('is_published');
        });
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('views'));
        Schema::table('blog_posts', fn (Blueprint $t) => $t->dropColumn('views'));
    }
};