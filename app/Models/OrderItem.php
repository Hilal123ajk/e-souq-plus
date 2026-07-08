<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'product_image_url',
        'variant_label',
        'variant_image_id',
        'unit_price',
        'quantity',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'quantity' => 'integer',
            'variant_image_id' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getImagePublicUrlAttribute(): string
    {
        $path = $this->product_image_url ?? '';

        if ($path === '' && $this->relationLoaded('product') && $this->product !== null) {
            $path = $this->product->getStoredImagePath();
        }

        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $relativePath = '/storage/'.ltrim($path, '/');

        if (app()->runningInConsole()) {
            return rtrim((string) config('app.url'), '/').$relativePath;
        }

        return rtrim(request()->getSchemeAndHttpHost().request()->getBaseUrl(), '/').$relativePath;
    }
}
