<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('footer_licenses', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();   // مثل «نماد اعتماد الکترونیکی»
            $table->string('image');               // مسیر عکس در storage
            $table->string('link')->nullable();    // آدرس مقصد (تب جدید)
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('footer_licenses');
    }
};