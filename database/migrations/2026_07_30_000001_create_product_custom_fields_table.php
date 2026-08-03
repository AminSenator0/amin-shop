<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('label'); // عنوان فیلد (مثلاً: "مدت زمان اشتراک")
            $table->string('type')->default('text'); // text | select | email | password
            $table->json('options')->nullable(); // برای select: ["1 ماهه", "3 ماهه", "6 ماهه"]
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_custom_fields');
    }
};