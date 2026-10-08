<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('contact_message_replies', 'reply_to')) {
            Schema::table('contact_message_replies', function (Blueprint $table) {
                $table->json('reply_to')->nullable()->after('channel'); // snapshot نقل‌قول: sender + body
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('contact_message_replies', 'reply_to')) {
            Schema::table('contact_message_replies', function (Blueprint $table) {
                $table->dropColumn('reply_to');
            });
        }
    }
};