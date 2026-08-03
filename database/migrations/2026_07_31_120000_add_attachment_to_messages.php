<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAttachmentToMessages extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->string('attachment')->nullable()->after('message');
        });

        Schema::table('contact_message_replies', function (Blueprint $table) {
            $table->string('attachment')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });

        Schema::table('contact_message_replies', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });
    }
}