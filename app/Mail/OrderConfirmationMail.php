<?php

namespace App\Mail;

use App\Models\Order;
use App\Support\StoreSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        $storeName = StoreSettings::get('store_name', config('app.name'));

        return new Envelope(
            subject: "تأیید سفارش {$this->order->order_number} — {$storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-confirmation',
            with: [
                'theme' => StoreSettings::themeCssVariables(),
            ],
        );
    }
}
