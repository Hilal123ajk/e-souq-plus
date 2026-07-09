<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\OrderPaymentEvent;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\PaymentIntent;

class OrderPaymentEventService
{
    public function recordCheckoutStarted(Order $order, Session $session): OrderPaymentEvent
    {
        return $this->createFromSession($order, OrderPaymentEvent::TYPE_CHECKOUT_STARTED, $session, [
            'source' => 'checkout',
        ]);
    }

    public function recordPaymentSucceeded(Order $order, Session $session, array $context = []): OrderPaymentEvent
    {
        if ($this->hasEvent($order, OrderPaymentEvent::TYPE_PAYMENT_SUCCEEDED, $session->id)) {
            return OrderPaymentEvent::query()
                ->where('order_id', $order->id)
                ->where('event_type', OrderPaymentEvent::TYPE_PAYMENT_SUCCEEDED)
                ->where('stripe_checkout_session_id', $session->id)
                ->firstOrFail();
        }

        return $this->createFromSession($order, OrderPaymentEvent::TYPE_PAYMENT_SUCCEEDED, $session, $context);
    }

    public function recordCheckoutCancelled(Order $order, ?Session $session = null): OrderPaymentEvent
    {
        $attributes = [
            'order_id' => $order->id,
            'event_type' => OrderPaymentEvent::TYPE_CHECKOUT_CANCELLED,
            'status' => 'cancelled',
            'payload' => ['source' => 'cancel_url'],
        ];

        if ($session !== null) {
            $attributes = array_merge($attributes, $this->extractSessionAttributes($session));
        } else {
            $attributes['stripe_checkout_session_id'] = $order->stripe_checkout_session_id;
        }

        return OrderPaymentEvent::query()->create($attributes);
    }

    public function recordPaymentVerificationFailed(
        Order $order,
        Session $session,
        string $failureMessage,
        ?string $failureCode = null,
    ): OrderPaymentEvent {
        $attributes = $this->extractSessionAttributes($session);

        return OrderPaymentEvent::query()->create([
            'order_id' => $order->id,
            'event_type' => OrderPaymentEvent::TYPE_PAYMENT_VERIFICATION_FAILED,
            ...$attributes,
            'failure_code' => $failureCode,
            'failure_message' => $failureMessage,
            'payload' => ['source' => 'success_callback'],
        ]);
    }

    public function recordPaymentFailed(Order $order, Session|PaymentIntent $stripeObject, array $context = []): OrderPaymentEvent
    {
        $attributes = $stripeObject instanceof Session
            ? $this->extractSessionAttributes($stripeObject)
            : $this->extractPaymentIntentAttributes($stripeObject);

        $failure = $this->extractFailureDetails($stripeObject);

        return OrderPaymentEvent::query()->create([
            'order_id' => $order->id,
            'event_type' => OrderPaymentEvent::TYPE_PAYMENT_FAILED,
            ...$attributes,
            'failure_code' => $failure['code'],
            'failure_message' => $failure['message'],
            'payload' => $context,
        ]);
    }

    public function recordWebhookEvent(Order $order, Event $event, Session|PaymentIntent|null $stripeObject = null): OrderPaymentEvent
    {
        if ($event->id !== null && $this->hasStripeEventId($event->id)) {
            return OrderPaymentEvent::query()
                ->where('stripe_event_id', $event->id)
                ->firstOrFail();
        }

        $attributes = [
            'order_id' => $order->id,
            'event_type' => OrderPaymentEvent::TYPE_WEBHOOK_RECEIVED,
            'stripe_event_id' => $event->id,
            'status' => $event->type,
            'payload' => [
                'stripe_event_type' => $event->type,
                'livemode' => $event->livemode,
            ],
        ];

        if ($stripeObject instanceof Session) {
            $attributes = array_merge($attributes, $this->extractSessionAttributes($stripeObject));
        } elseif ($stripeObject instanceof PaymentIntent) {
            $attributes = array_merge($attributes, $this->extractPaymentIntentAttributes($stripeObject));
        }

        return OrderPaymentEvent::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function createFromSession(
        Order $order,
        string $eventType,
        Session $session,
        array $context = [],
    ): OrderPaymentEvent {
        return OrderPaymentEvent::query()->create([
            'order_id' => $order->id,
            'event_type' => $eventType,
            ...$this->extractSessionAttributes($session),
            'payload' => $context !== [] ? $context : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function extractSessionAttributes(Session $session): array
    {
        $paymentIntent = $session->payment_intent;
        $paymentIntentId = is_string($paymentIntent)
            ? $paymentIntent
            : ($paymentIntent->id ?? null);

        $chargeId = null;
        if (is_object($paymentIntent)) {
            $latestCharge = $paymentIntent->latest_charge ?? null;
            $chargeId = is_string($latestCharge)
                ? $latestCharge
                : (is_object($latestCharge) ? ($latestCharge->id ?? null) : null);
        }

        return [
            'stripe_checkout_session_id' => $session->id,
            'stripe_payment_intent_id' => $paymentIntentId,
            'stripe_charge_id' => $chargeId,
            'amount' => isset($session->amount_total) ? round((int) $session->amount_total / 100, 2) : null,
            'currency' => $session->currency ?? null,
            'status' => $session->payment_status ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function extractPaymentIntentAttributes(PaymentIntent $paymentIntent): array
    {
        $latestCharge = $paymentIntent->latest_charge ?? null;
        $chargeId = is_string($latestCharge)
            ? $latestCharge
            : (is_object($latestCharge) ? ($latestCharge->id ?? null) : null);

        return [
            'stripe_payment_intent_id' => $paymentIntent->id,
            'stripe_charge_id' => $chargeId,
            'amount' => isset($paymentIntent->amount_received)
                ? round((int) $paymentIntent->amount_received / 100, 2)
                : (isset($paymentIntent->amount) ? round((int) $paymentIntent->amount / 100, 2) : null),
            'currency' => $paymentIntent->currency ?? null,
            'status' => $paymentIntent->status ?? null,
        ];
    }

    /**
     * @return array{code: ?string, message: ?string}
     */
    private function extractFailureDetails(Session|PaymentIntent $stripeObject): array
    {
        if ($stripeObject instanceof PaymentIntent) {
            $lastError = $stripeObject->last_payment_error ?? null;

            return [
                'code' => is_object($lastError) ? ($lastError->code ?? null) : null,
                'message' => is_object($lastError) ? ($lastError->message ?? null) : null,
            ];
        }

        return [
            'code' => null,
            'message' => $stripeObject->status === 'expired' ? 'Checkout session expired.' : null,
        ];
    }

    private function hasEvent(Order $order, string $eventType, ?string $sessionId): bool
    {
        if ($sessionId === null || $sessionId === '') {
            return false;
        }

        return OrderPaymentEvent::query()
            ->where('order_id', $order->id)
            ->where('event_type', $eventType)
            ->where('stripe_checkout_session_id', $sessionId)
            ->exists();
    }

    private function hasStripeEventId(string $eventId): bool
    {
        return OrderPaymentEvent::query()
            ->where('stripe_event_id', $eventId)
            ->exists();
    }
}
