<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Order $order,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order confirmed — '.$this->order->order_number.' | E-Souq Plus',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-confirmation',
            with: [
                'paymentLabel' => $this->paymentLabel(),
            ],
        );
    }

    public function paymentLabel(): string
    {
        return match ($this->order->payment_method) {
            Order::PAYMENT_METHOD_STRIPE => 'Card (paid online)',
            Order::PAYMENT_METHOD_COD => 'Cash on Delivery',
            default => ucfirst(str_replace('_', ' ', $this->order->payment_method)),
        };
    }
}
