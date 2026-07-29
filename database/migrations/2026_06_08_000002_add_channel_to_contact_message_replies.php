<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_message_replies', function (Blueprint $table) {
            $table->string('channel')->default('panel')->after('is_from_admin');
            $table->boolean('email_sent')->default(false)->after('sms_sent');
        });
    }

    public function down(): void
    {
        Schema::table('contact_message_replies', function (Blueprint $table) {
            $table->dropColumn(['channel', 'email_sent']);
        });
    }
};
