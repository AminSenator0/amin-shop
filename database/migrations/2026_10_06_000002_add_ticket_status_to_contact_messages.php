<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('contact_messages', 'is_closed')) {
            Schema::table('contact_messages', function (Blueprint $table) {
                $table->boolean('is_closed')->default(false)->after('is_read');
                $table->timestamp('closed_at')->nullable()->after('is_closed');
                $table->foreignId('closed_by')->nullable()->after('closed_at')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('contact_messages', 'is_closed')) {
            Schema::table('contact_messages', function (Blueprint $table) {
                $table->dropConstrainedForeignId('closed_by');
                $table->dropColumn(['is_closed', 'closed_at']);
            });
        }
    }
};