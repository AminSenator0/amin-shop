<?php

namespace App\Mail;

use App\Models\ContactMessage;
use App\Support\StoreSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactMessage $contactMessage,
        public string $replyBody,
    ) {}

    public function envelope(): Envelope
    {
        $storeName = StoreSettings::get('store_name', config('app.name'));

        return new Envelope(
            subject: "پاسخ {$storeName} — {$this->contactMessage->subject}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-reply',
            with: [
                'theme' => StoreSettings::themeCssVariables(),
            ],
        );
    }
}
