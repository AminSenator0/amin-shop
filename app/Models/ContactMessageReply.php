<?php

namespace App\Models;

use App\Enums\ReplyChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessageReply extends Model
{
    protected $fillable = [
        'contact_message_id',
        'user_id',
        'body',
        'is_from_admin',
        'channel',
        'sms_sent',
        'email_sent',
    ];

    protected function casts(): array
    {
        return [
            'is_from_admin' => 'boolean',
            'channel' => ReplyChannel::class,
            'sms_sent' => 'boolean',
            'email_sent' => 'boolean',
        ];
    }

    public function contactMessage(): BelongsTo
    {
        return $this->belongsTo(ContactMessage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
