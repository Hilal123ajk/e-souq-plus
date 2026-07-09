<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPaymentEvent extends Model
{
    public const TYPE_CHECKOUT_STARTED = 'checkout_started';

    public const TYPE_PAYMENT_SUCCEEDED = 'payment_succeeded';

    public const TYPE_PAYMENT_FAILED = 'payment_failed';

    public const TYPE_CHECKOUT_CANCELLED = 'checkout_cancelled';

    public const TYPE_WEBHOOK_RECEIVED = 'webhook_received';

    public const TYPE_PAYMENT_VERIFICATION_FAILED = 'payment_verification_failed';

    protected $fillable = [
        'order_id',
        'event_type',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'stripe_event_id',
        'amount',
        'currency',
        'status',
        'failure_code',
        'failure_message',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payload' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function toAdminArray(): array
    {
        return [
            'eventType' => $this->event_type,
            'sessionId' => $this->stripe_checkout_session_id,
            'paymentIntentId' => $this->stripe_payment_intent_id,
            'chargeId' => $this->stripe_charge_id,
            'eventId' => $this->stripe_event_id,
            'amount' => $this->amount !== null ? (float) $this->amount : null,
            'currency' => $this->currency,
            'status' => $this->status,
            'failureCode' => $this->failure_code,
            'failureMessage' => $this->failure_message,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
