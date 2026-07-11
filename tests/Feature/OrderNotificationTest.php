<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendOrderPlacedNotifications;
use App\Mail\NewOrderAdminMail;
use App\Mail\OrderConfirmationMail;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cod_order_sends_customer_and_admin_emails(): void
    {
        Mail::fake();

        User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@e-souq-plus.com',
        ]);

        User::factory()->create([
            'role' => 'manager',
            'email' => 'manager@e-souq-plus.com',
        ]);

        $product = $this->createProduct(stock: 10, price: 100);

        $response = $this->postJson(route('store.orders.store'), [
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
        ]);

        $response->assertCreated();

        Mail::assertSent(OrderConfirmationMail::class, function (OrderConfirmationMail $mail): bool {
            return $mail->hasTo('ahmed@example.com');
        });

        Mail::assertSent(NewOrderAdminMail::class, 2);

        Mail::assertSent(NewOrderAdminMail::class, function (NewOrderAdminMail $mail): bool {
            return $mail->hasTo('admin@e-souq-plus.com') || $mail->hasTo('manager@e-souq-plus.com');
        });
    }

    public function test_order_notification_job_skips_invalid_customer_email(): void
    {
        Mail::fake();

        User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@e-souq-plus.com',
        ]);

        $order = \App\Models\Order::query()->create([
            'order_number' => 'ESP-TEST-EMAIL',
            'status' => 'pending',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'not-an-email',
            'phone' => '0501234567',
            'address' => '123 Test Street',
            'city' => 'Dubai',
            'country' => 'United Arab Emirates',
            'payment_method' => 'cod',
            'subtotal' => 100,
            'delivery_fee' => 25,
            'total' => 125,
        ]);

        (new SendOrderPlacedNotifications($order->id))->handle();

        Mail::assertNotSent(OrderConfirmationMail::class);
        Mail::assertSent(NewOrderAdminMail::class, 1);
    }

    public function test_order_notification_job_is_idempotent_for_customer_email(): void
    {
        Mail::fake();

        User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@e-souq-plus.com',
        ]);

        $order = \App\Models\Order::query()->create([
            'order_number' => 'ESP-TEST-IDEMPOTENT',
            'status' => 'pending',
            'first_name' => 'Ahmed',
            'last_name' => 'Ali',
            'email' => 'ahmed@example.com',
            'phone' => '0501234567',
            'address' => '123 Marina Walk',
            'city' => 'Dubai',
            'country' => 'United Arab Emirates',
            'payment_method' => 'cod',
            'subtotal' => 100,
            'delivery_fee' => 25,
            'total' => 125,
            'customer_notified_at' => now(),
            'admin_notified_user_ids' => [],
        ]);

        $job = new SendOrderPlacedNotifications($order->id);
        $job->handle();
        $job->handle();

        Mail::assertNotSent(OrderConfirmationMail::class);
        Mail::assertSent(NewOrderAdminMail::class, 1);
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
