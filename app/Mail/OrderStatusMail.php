<?php

namespace App\Mail;

use App\Models\Order;
use App\Support\StoreSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $statusMessage,
    ) {}

    public function envelope(): Envelope
    {
        $storeName = StoreSettings::get('store_name', config('app.name'));

        return new Envelope(
            subject: "به‌روزرسانی سفارش {$this->order->order_number} — {$storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-status',
            with: [
                'theme' => StoreSettings::themeCssVariables(),
            ],
        );
    }
}
