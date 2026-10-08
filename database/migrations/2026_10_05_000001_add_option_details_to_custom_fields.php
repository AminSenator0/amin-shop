<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // نوع نمایش فیلد — فقط برای فیلدهای دارای گزینه معنا دارد
        if (! Schema::hasColumn('product_custom_fields', 'display_type')) {
            Schema::table('product_custom_fields', function (Blueprint $table) {
                $table->string('display_type')->default('buttons')->after('type'); // buttons | dropdown | swatches
            });
        }

        // جدول جزئیات هر گزینه — ردیف واقعی برای lock امن موجودی در checkout
        if (! Schema::hasTable('product_custom_field_options')) {
            Schema::create('product_custom_field_options', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_custom_field_id')->constrained('product_custom_fields')->cascadeOnDelete();
                $table->string('value');                 // مثلاً «ساده»
                $table->integer('stock')->nullable();    // null = از موجودی اصلی محصول کم شود
                $table->unsignedBigInteger('price')->nullable(); // null = قیمت پایه محصول
                $table->string('image')->nullable();     // null = بدون تعویض تصویر
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['product_custom_field_id', 'value'], 'pcfo_field_value_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_custom_field_options');

        if (Schema::hasColumn('product_custom_fields', 'display_type')) {
            Schema::table('product_custom_fields', function (Blueprint $table) {
                $table->dropColumn('display_type');
            });
        }
    }
};