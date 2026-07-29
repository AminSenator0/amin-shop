<?php

namespace App\Services;

use App\Enums\ReplyChannel;
use App\Mail\ContactMessageReplyMail;
use App\Models\ContactMessage;
use App\Models\ContactMessageReply;
use App\Support\StoreSettings;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ContactMessageService
{
    public function __construct(private SmsService $sms) {}

    public function queryForUser(User $user): Builder
    {
        return ContactMessage::query()
            ->where(function (Builder $query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere(function (Builder $query) use ($user) {
                        $query->whereNull('user_id')->where('email', $user->email);
                    });
            });
    }

    public function userCanAccess(ContactMessage $message, User $user): bool
    {
        if ($message->user_id === $user->id) {
            return true;
        }

        return $message->user_id === null && $message->email === $user->email;
    }

    public function linkToUser(ContactMessage $message, User $user): void
    {
        if ($message->user_id === null && $this->userCanAccess($message, $user)) {
            $message->update(['user_id' => $user->id]);
        }
    }

    public function adminReply(ContactMessage $message, User $admin, string $body, ReplyChannel $channel = ReplyChannel::Panel): ContactMessageReply
    {
        $reply = DB::transaction(function () use ($message, $admin, $body, $channel) {
            $reply = $message->replies()->create([
                'user_id' => $admin->id,
                'body' => $body,
                'is_from_admin' => true,
                'channel' => $channel->value,
            ]);

            $message->update([
                'last_replied_at' => now(),
                'has_unread_reply_for_user' => true,
            ]);

            return $reply;
        });

        if ($channel->sendsSmsNotification()) {
            $this->notifyUserBySms($message, $reply);
        }

        if ($channel->sendsSmsBody()) {
            $this->sendReplyBodyBySms($message, $reply, $body);
        }

        if ($channel->sendsEmail()) {
            $this->sendReplyByEmail($message, $reply, $body);
        }

        return $reply;
    }

    public function userReply(ContactMessage $message, User $user, string $body): ContactMessageReply
    {
        return DB::transaction(function () use ($message, $user, $body) {
            $this->linkToUser($message, $user);

            $reply = $message->replies()->create([
                'user_id' => $user->id,
                'body' => $body,
                'is_from_admin' => false,
                'channel' => ReplyChannel::Panel->value,
            ]);

            $message->markAsUnread();

            return $reply;
        });
    }

    public function markRepliesReadForUser(ContactMessage $message): void
    {
        $message->update(['has_unread_reply_for_user' => false]);
    }

    public function unreadCountForUser(User $user): int
    {
        return $this->queryForUser($user)->where('has_unread_reply_for_user', true)->count();
    }

    private function notifyUserBySms(ContactMessage $message, ContactMessageReply $reply): void
    {
        $phone = $this->resolvePhone($message);

        if (! $phone) {
            return;
        }

        $storeName = StoreSettings::get('store_name', config('app.name'));
        $sent = $this->sms->notifyMessageReply($phone, $storeName, $message->id);
        $reply->update(['sms_sent' => $sent]);
    }

    private function sendReplyBodyBySms(ContactMessage $message, ContactMessageReply $reply, string $body): void
    {
        $phone = $this->resolvePhone($message);

        if (! $phone) {
            return;
        }

        $storeName = StoreSettings::get('store_name', config('app.name'));
        $sent = $this->sms->notifyMessageReplyBody($phone, $storeName, $message->id, $body);
        $reply->update(['sms_sent' => $sent]);
    }

    private function sendReplyByEmail(ContactMessage $message, ContactMessageReply $reply, string $body): void
    {
        if (! $message->email) {
            return;
        }

        try {
            Mail::to($message->email)->send(new ContactMessageReplyMail($message, $body));
            $reply->update(['email_sent' => true]);
        } catch (\Throwable) {
            $reply->update(['email_sent' => false]);
        }
    }

    private function resolvePhone(ContactMessage $message): ?string
    {
        $phone = $message->phone ?? $message->user?->phone;

        return $phone ? normalize_mobile($phone) : null;
    }
}
