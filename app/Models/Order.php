<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_METHOD_COD = 'cod';

    public const PAYMENT_METHOD_STRIPE = 'stripe';

    public const PAYMENT_STATUS_UNPAID = 'unpaid';

    public const PAYMENT_STATUS_PAID = 'paid';

    public const PAYMENT_STATUS_FAILED = 'failed';

    /** @var list<string> */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_SHIPPED,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'order_number',
        'status',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'notes',
        'payment_method',
        'payment_status',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'paid_at',
        'customer_notified_at',
        'admin_notified_user_ids',
        'subtotal',
        'delivery_fee',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
            'customer_notified_at' => 'datetime',
            'admin_notified_user_ids' => 'array',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentEvents(): HasMany
    {
        return $this->hasMany(OrderPaymentEvent::class)->orderBy('created_at');
    }

    public function getCustomerNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getItemCountAttribute(): int
    {
        if ($this->relationLoaded('items')) {
            return (int) $this->items->sum('quantity');
        }

        return (int) $this->items()->sum('quantity');
    }

    /**
     * @return array<string, mixed>
     */
    public function toAdminArray(): array
    {
        $this->loadMissing(['items', 'paymentEvents']);

        $itemCount = $this->item_count;

        return [
            'id' => $this->order_number,
            'dbId' => $this->id,
            'customer' => $this->customer_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'notes' => $this->notes,
            'items' => $itemCount.' '.($itemCount === 1 ? 'item' : 'items'),
            'total' => (float) $this->total,
            'subtotal' => (float) $this->subtotal,
            'deliveryFee' => (float) $this->delivery_fee,
            'status' => $this->status,
            'paymentMethod' => $this->payment_method,
            'paymentStatus' => $this->payment_status,
            'paidAt' => $this->paid_at?->toIso8601String(),
            'stripeCheckoutSessionId' => $this->stripe_checkout_session_id,
            'stripePaymentIntentId' => $this->stripe_payment_intent_id,
            'paymentEvents' => $this->paymentEvents->map(
                fn (OrderPaymentEvent $event): array => $event->toAdminArray(),
            )->values()->all(),
            'createdAt' => $this->created_at?->toIso8601String(),
            'lineItems' => $this->items->map(fn (OrderItem $item): array => [
                'name' => $item->product_name,
                'qty' => $item->quantity,
                'price' => (float) $item->unit_price,
                'lineTotal' => (float) $item->line_total,
                'image' => $item->image_public_url,
                'variantLabel' => $item->variant_label,
            ])->values()->all(),
        ];
    }
}
