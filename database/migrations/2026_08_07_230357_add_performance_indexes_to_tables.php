<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('is_featured');
            $table->index(['is_active', 'is_featured']);
            $table->index(['category_id', 'is_active']);
            $table->index(['brand_id', 'is_active']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->index(['product_id', 'is_approved']);
            $table->index(['is_approved', 'created_at']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('product_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('sort_order');
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->index(['is_published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['is_active', 'is_featured']);
            $table->dropIndex(['category_id', 'is_active']);
            $table->dropIndex(['brand_id', 'is_active']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'is_approved']);
            $table->dropIndex(['is_approved', 'created_at']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['product_id']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['sort_order']);
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropIndex(['is_published', 'published_at']);
        });
    }
};