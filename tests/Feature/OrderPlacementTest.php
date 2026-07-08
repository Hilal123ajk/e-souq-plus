<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPlacementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_place_order_from_checkout(): void
    {
        $product = $this->createProduct(stock: 10, price: 100);

        $response = $this->postJson(route('store.orders.store'), [
            'first_name' => 'Ahmed',
            'last_name' => 'Ali',
            'email' => 'ahmed@example.com',
            'phone' => '0501234567',
            'address' => '123 Marina Walk',
            'city' => 'Dubai',
            'country' => 'United Arab Emirates',
            'notes' => 'Call before delivery',
            'payment_method' => 'cod',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['order_number', 'total']);

        $this->assertDatabaseHas('orders', [
            'email' => 'ahmed@example.com',
            'phone' => '0501234567',
            'status' => 'pending',
            'total' => 225.00,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 100.00,
            'line_total' => 200.00,
        ]);

        $this->assertSame(8, $product->fresh()->stock_quantity);
    }

    public function test_admin_can_update_order_status(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'admin_otp_verified_at' => now(),
        ]);

        $order = Order::query()->create([
            'order_number' => 'ESP-TEST-001',
            'status' => 'pending',
            'first_name' => 'Sara',
            'last_name' => 'Khan',
            'email' => 'sara@example.com',
            'phone' => '0509876543',
            'address' => '456 Downtown',
            'city' => 'Abu Dhabi',
            'country' => 'United Arab Emirates',
            'payment_method' => 'cod',
            'subtotal' => 50,
            'delivery_fee' => 25,
            'total' => 75,
        ]);

        $response = $this->actingAs($admin)->putJson(
            route('admin.orders.update-status', $order),
            ['status' => 'processing'],
        );

        $response->assertOk()
            ->assertJsonPath('order.status', 'processing');

        $this->assertSame('processing', $order->fresh()->status);
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
