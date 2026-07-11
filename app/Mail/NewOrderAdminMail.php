<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewOrderAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly User $adminUser,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New order '.$this->order->order_number.' — E-Souq Plus',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-order-admin',
            with: [
                'adminOrdersUrl' => route('admin.orders', absolute: true),
                'paymentLabel' => $this->paymentLabel(),
            ],
        );
    }

    public function paymentLabel(): string
    {
        return match ($this->order->payment_method) {
            Order::PAYMENT_METHOD_STRIPE => 'Card (Stripe)',
            Order::PAYMENT_METHOD_COD => 'Cash on Delivery',
            default => ucfirst(str_replace('_', ' ', $this->order->payment_method)),
        };
    }
}
