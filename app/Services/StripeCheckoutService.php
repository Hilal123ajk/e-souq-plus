<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeCheckoutService
{
    public function __construct()
    {
        Stripe::setApiKey((string) config('services.stripe.secret'));
    }

    /**
     * @throws ApiErrorException
     */
    public function createCheckoutSession(Order $order): Session
    {
        $currency = strtolower((string) config('services.stripe.currency', 'aed'));

        $lineItems = $order->items->map(function ($item) use ($currency): array {
            return [
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount' => $this->toStripeAmount((float) $item->unit_price),
                    'product_data' => [
                        'name' => $item->product_name,
                        'metadata' => [
                            'product_id' => (string) $item->product_id,
                            'sku' => (string) ($item->product_sku ?? ''),
                        ],
                    ],
                ],
                'quantity' => (int) $item->quantity,
            ];
        })->values()->all();

        if ((float) $order->delivery_fee > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount' => $this->toStripeAmount((float) $order->delivery_fee),
                    'product_data' => [
                        'name' => 'Delivery fee',
                    ],
                ],
                'quantity' => 1,
            ];
        }

        return Session::create([
            'mode' => 'payment',
            'customer_email' => $order->email,
            'client_reference_id' => (string) $order->id,
            'line_items' => $lineItems,
            'payment_intent_data' => [
                'metadata' => [
                    'order_id' => (string) $order->id,
                    'order_number' => $order->order_number,
                ],
            ],
            'success_url' => route('store.checkout.stripe.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('store.checkout.stripe.cancel', ['order' => $order->id]),
            'metadata' => [
                'order_id' => (string) $order->id,
                'order_number' => $order->order_number,
            ],
        ]);
    }

    /**
     * @throws ApiErrorException
     */
    public function retrieveSession(string $sessionId): Session
    {
        return Session::retrieve($sessionId, [
            'expand' => ['payment_intent.latest_charge'],
        ]);
    }

    public function isSessionPaid(Session $session): bool
    {
        return $session->payment_status === 'paid';
    }

    public function sessionMatchesOrder(Session $session, Order $order): bool
    {
        $metadataOrderId = $session->metadata['order_id'] ?? null;

        return (string) $metadataOrderId === (string) $order->id
            && $session->id === $order->stripe_checkout_session_id;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function constructWebhookEvent(string $payload, ?string $signature): \Stripe\Event
    {
        $secret = (string) config('services.stripe.webhook_secret');

        if ($secret === '' || $signature === null) {
            throw new \RuntimeException('Stripe webhook is not configured.');
        }

        return Webhook::constructEvent($payload, $signature, $secret);
    }

    public function logWebhookFailure(string $message, array $context = []): void
    {
        Log::warning($message, $context);
    }

    private function toStripeAmount(float $amount): int
    {
        return (int) round($amount * 100);
    }
}
