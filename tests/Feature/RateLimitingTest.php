<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_placement_is_rate_limited(): void
    {
        $product = $this->createProduct();

        $payload = $this->orderPayload($product);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('store.orders.store'), $payload)->assertCreated();
        }

        $this->postJson(route('store.orders.store'), $payload)
            ->assertStatus(429);
    }

    public function test_admin_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('admin.login.submit'), [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $this->post(route('admin.login.submit'), [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    private function createProduct(): Product
    {
        $category = Category::query()->create([
            'title' => 'Test Category',
            'slug' => 'test-category',
            'image_url' => 'categories/test.jpg',
            'is_active' => true,
        ]);

        $brand = Brand::query()->create([
            'title' => 'Test Brand',
            'slug' => 'test-brand',
            'is_active' => true,
        ]);

        return Product::query()->create([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Test',
            'sku' => 'SKU-001',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => 100,
            'image_url' => 'products/test.jpg',
            'stock_quantity' => 100,
            'is_active' => true,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function orderPayload(Product $product): array
    {
        return [
            'first_name' => 'Ahmed',
            'last_name' => 'Ali',
            'email' => 'ahmed@example.com',
            'phone' => '0501234567',
            'address' => '123 Marina Walk',
            'city' => 'Dubai',
            'country' => 'United Arab Emirates',
            'payment_method' => 'cod',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ];
    }
}
