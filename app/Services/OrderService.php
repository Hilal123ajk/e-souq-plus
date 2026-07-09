<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Support\DeliveryPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Stripe\Checkout\Session;

class OrderService
{
    public function __construct(
        private readonly OrderPaymentEventService $paymentEventService,
    ) {}

    /**
     * @param  array<string, mixed>  $customer
     * @param  list<array{product_id: int, quantity: int, variant_image_id?: int|null, variant_label?: string|null}>  $items
     * @return array{order: Order, order_number: string}
     */
    public function placeCodOrder(array $customer, array $items): array
    {
        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => ['Your cart is empty.'],
            ]);
        }

        return DB::transaction(function () use ($customer, $items): array {
            $lineItems = $this->resolveLineItems($items);
            $totals = $this->calculateTotals($lineItems);

            $order = Order::query()->create([
                'order_number' => $this->generateOrderNumber(),
                'status' => Order::STATUS_PENDING,
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'email' => $customer['email'],
                'phone' => $customer['phone'],
                'address' => $customer['address'],
                'city' => $customer['city'],
                'country' => $customer['country'],
                'notes' => $customer['notes'] ?? null,
                'payment_method' => Order::PAYMENT_METHOD_COD,
                'payment_status' => Order::PAYMENT_STATUS_UNPAID,
                'subtotal' => $totals['subtotal'],
                'delivery_fee' => $totals['delivery_fee'],
                'total' => $totals['total'],
            ]);

            $this->createOrderItemsAndDecrementStock($order, $lineItems);
            $order->load('items');

            return [
                'order' => $order,
                'order_number' => $order->order_number,
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $customer
     * @param  list<array{product_id: int, quantity: int, variant_image_id?: int|null, variant_label?: string|null}>  $items
     */
    public function createPendingStripeOrder(array $customer, array $items): Order
    {
        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => ['Your cart is empty.'],
            ]);
        }

        return DB::transaction(function () use ($customer, $items): Order {
            $lineItems = $this->resolveLineItems($items);
            $totals = $this->calculateTotals($lineItems);

            $order = Order::query()->create([
                'order_number' => $this->generateOrderNumber(),
                'status' => Order::STATUS_PENDING,
                'first_name' => $customer['first_name'],
                'last_name' => $customer['last_name'],
                'email' => $customer['email'],
                'phone' => $customer['phone'],
                'address' => $customer['address'],
                'city' => $customer['city'],
                'country' => $customer['country'],
                'notes' => $customer['notes'] ?? null,
                'payment_method' => Order::PAYMENT_METHOD_STRIPE,
                'payment_status' => Order::PAYMENT_STATUS_UNPAID,
                'subtotal' => $totals['subtotal'],
                'delivery_fee' => $totals['delivery_fee'],
                'total' => $totals['total'],
            ]);

            foreach ($lineItems as $lineItem) {
                $order->items()->create($lineItem);
            }

            return $order->load('items');
        });
    }

    public function attachStripeSession(Order $order, Session $session): void
    {
        $order->update([
            'stripe_checkout_session_id' => $session->id,
        ]);

        $this->paymentEventService->recordCheckoutStarted($order, $session);
    }

    public function completeStripePayment(Order $order, Session $session): Order
    {
        if ($order->payment_status === Order::PAYMENT_STATUS_PAID) {
            return $order->load('items');
        }

        if ($order->payment_method !== Order::PAYMENT_METHOD_STRIPE) {
            throw ValidationException::withMessages([
                'payment' => ['This order is not a card payment order.'],
            ]);
        }

        if ($session->payment_status !== 'paid') {
            throw ValidationException::withMessages([
                'payment' => ['Payment has not been completed yet.'],
            ]);
        }

        $expectedTotal = $this->toStripeAmount((float) $order->total);
        $paidTotal = (int) ($session->amount_total ?? 0);

        if ($paidTotal !== $expectedTotal) {
            throw ValidationException::withMessages([
                'payment' => ['Payment amount does not match the order total.'],
            ]);
        }

        return DB::transaction(function () use ($order, $session): Order {
            $order->refresh()->load('items');

            if ($order->payment_status === Order::PAYMENT_STATUS_PAID) {
                return $order;
            }

            $this->validateStockForOrder($order);

            foreach ($order->items as $item) {
                Product::query()
                    ->whereKey($item->product_id)
                    ->decrement('stock_quantity', $item->quantity);
            }

            $paymentIntentId = is_string($session->payment_intent)
                ? $session->payment_intent
                : ($session->payment_intent->id ?? null);

            $order->update([
                'payment_status' => Order::PAYMENT_STATUS_PAID,
                'stripe_checkout_session_id' => $session->id,
                'stripe_payment_intent_id' => $paymentIntentId,
                'status' => Order::STATUS_PROCESSING,
                'paid_at' => now(),
            ]);

            $this->paymentEventService->recordPaymentSucceeded($order, $session, [
                'source' => 'fulfillment',
            ]);

            return $order->fresh(['items', 'paymentEvents']);
        });
    }

    /**
     * @param  list<array<string, mixed>>  $lineItems
     * @return array{subtotal: float, delivery_fee: float, total: float}
     */
    private function calculateTotals(array $lineItems): array
    {
        $subtotal = round(array_sum(array_column($lineItems, 'line_total')), 2);
        $deliveryFee = $subtotal > 0 ? (float) DeliveryPolicy::frontendConfig()['fee'] : 0.0;

        return [
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => round($subtotal + $deliveryFee, 2),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $lineItems
     */
    private function createOrderItemsAndDecrementStock(Order $order, array $lineItems): void
    {
        foreach ($lineItems as $lineItem) {
            $order->items()->create($lineItem);

            Product::query()
                ->whereKey($lineItem['product_id'])
                ->decrement('stock_quantity', $lineItem['quantity']);
        }
    }

    private function validateStockForOrder(Order $order): void
    {
        $productIds = $order->items->pluck('product_id')->filter()->unique()->values()->all();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        foreach ($order->items as $item) {
            $product = $products->get($item->product_id);

            if ($product === null) {
                throw ValidationException::withMessages([
                    'items' => ["{$item->product_name} is no longer available."],
                ]);
            }

            if ($product->stock_quantity < $item->quantity) {
                throw ValidationException::withMessages([
                    'items' => ["Not enough stock for {$product->name}."],
                ]);
            }
        }
    }

    private function toStripeAmount(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * @param  list<array{product_id: int, quantity: int, variant_image_id?: int|null, variant_label?: string|null}>  $items
     * @return list<array<string, mixed>>
     */
    private function resolveLineItems(array $items): array
    {
        $productIds = array_values(array_unique(array_map(
            fn (array $item): int => (int) $item['product_id'],
            $items,
        )));

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->with('images')
            ->get()
            ->keyBy('id');

        $lineItems = [];
        $errors = [];

        foreach ($items as $index => $item) {
            $productId = (int) $item['product_id'];
            $quantity = (int) $item['quantity'];
            $product = $products->get($productId);

            if (! $product) {
                $errors["items.{$index}.product_id"] = ['One or more products are no longer available.'];
                continue;
            }

            if ($quantity < 1 || $quantity > 99) {
                $errors["items.{$index}.quantity"] = ['Quantity must be between 1 and 99.'];
                continue;
            }

            if ($product->stock_quantity < $quantity) {
                $errors["items.{$index}.quantity"] = ["Not enough stock for {$product->name}."];
                continue;
            }

            $unitPrice = (float) $product->price;
            $imagePath = $product->getStoredImagePath();

            if (isset($item['variant_image_id']) && $item['variant_image_id'] !== null) {
                $variantImage = $product->images->firstWhere('id', (int) $item['variant_image_id']);
                if ($variantImage !== null) {
                    $imagePath = $variantImage->getStoredImagePath();
                }
            }

            $lineItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'product_image_url' => $imagePath !== '' ? $imagePath : null,
                'variant_label' => isset($item['variant_label']) && $item['variant_label'] !== ''
                    ? (string) $item['variant_label']
                    : null,
                'variant_image_id' => isset($item['variant_image_id']) && $item['variant_image_id'] !== null
                    ? (int) $item['variant_image_id']
                    : null,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'line_total' => round($unitPrice * $quantity, 2),
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        if ($lineItems === []) {
            throw ValidationException::withMessages([
                'items' => ['Your cart is empty.'],
            ]);
        }

        return $lineItems;
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'ESP-'.now()->format('ymd').'-'.strtoupper(Str::random(6));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
