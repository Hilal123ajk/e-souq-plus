<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\StripeCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stripe\Checkout\Session;
use Tests\TestCase;

class StripeCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_stripe_checkout_creates_pending_order_without_decrementing_stock(): void
    {
        $product = $this->createProduct(stock: 10, price: 100);

        $this->mock(StripeCheckoutService::class, function ($mock): void {
            $mock->shouldReceive('createCheckoutSession')
                ->once()
                ->andReturn(Session::constructFrom([
                    'id' => 'cs_test_123',
                    'url' => 'https://checkout.stripe.com/pay/cs_test_123',
                ]));
        });

        $response = $this->postJson(route('store.checkout.stripe.store'), [
            'first_name' => 'Ahmed',
            'last_name' => 'Ali',
            'email' => 'ahmed@example.com',
            'phone' => '0501234567',
            'address' => '123 Marina Walk',
            'city' => 'Dubai',
            'country' => 'United Arab Emirates',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('checkout_url', 'https://checkout.stripe.com/pay/cs_test_123');

        $this->assertDatabaseHas('orders', [
            'email' => 'ahmed@example.com',
            'payment_method' => 'stripe',
            'payment_status' => 'unpaid',
            'status' => 'pending',
            'total' => 225.00,
            'stripe_checkout_session_id' => 'cs_test_123',
        ]);

        $this->assertSame(10, $product->fresh()->stock_quantity);

        $this->assertDatabaseHas('order_payment_events', [
            'event_type' => 'checkout_started',
            'stripe_checkout_session_id' => 'cs_test_123',
            'status' => null,
        ]);
    }

    private function createProduct(int $stock, float $price): Product
    {
        $category = Category::query()->create([
            'title' => 'Accessories',
            'slug' => 'accessories',
            'image_url' => 'categories/test.jpg',
            'is_active' => true,
        ]);

        $brand = Brand::query()->create([
            'title' => 'Souq Brand',
            'slug' => 'souq-brand',
            'is_active' => true,
        ]);

        return Product::query()->create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TEST-SKU-001',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => $price,
            'image_url' => 'products/test.jpg',
            'stock_quantity' => $stock,
            'is_active' => true,
        ]);
    }
}
