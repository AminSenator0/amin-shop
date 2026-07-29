<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->boolean('has_unread_reply_for_user')->default(false)->after('is_read');
            $table->timestamp('last_replied_at')->nullable()->after('has_unread_reply_for_user');
        });

        Schema::create('contact_message_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->boolean('is_from_admin')->default(false);
            $table->boolean('sms_sent')->default(false);
            $table->timestamps();
        });

        DB::table('contact_messages')
            ->whereNull('user_id')
            ->orderBy('id')
            ->each(function ($message) {
                $userId = DB::table('users')
                    ->where('email', $message->email)
                    ->value('id');

                if ($userId) {
                    DB::table('contact_messages')
                        ->where('id', $message->id)
                        ->update(['user_id' => $userId]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_message_replies');

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['has_unread_reply_for_user', 'last_replied_at']);
        });
    }
};
