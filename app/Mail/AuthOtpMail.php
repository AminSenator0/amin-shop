<?php

namespace App\Mail;

use App\Support\StoreSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuthOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public string $purpose,
    ) {}

    public function envelope(): Envelope
    {
        $storeName = StoreSettings::get('store_name', config('app.name'));
        $action = $this->purpose === 'register' ? 'ثبت‌نام' : 'ورود';

        return new Envelope(
            subject: "کد {$action} — {$storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auth-otp',
            with: [
                'theme' => StoreSettings::themeCssVariables(),
                'storeName' => StoreSettings::get('store_name', config('app.name')),
            ],
        );
    }
}
