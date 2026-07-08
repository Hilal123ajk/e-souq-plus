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
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
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
        $this->loadMissing('items');

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
            'createdAt' => $this->created_at?->toIso8601String(),
            'lineItems' => $this->items->map(fn (OrderItem $item): array => [
                'name' => $item->product_name,
                'qty' => $item->quantity,
                'price' => (float) $item->unit_price,
                'variantLabel' => $item->variant_label,
            ])->values()->all(),
        ];
    }
}
