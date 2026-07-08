<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasPublicStorageImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasPublicStorageImage;

    protected $fillable = [
        'product_id',
        'image_url',
        'label',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (ProductImage $image): void {
            static::deleteStoredImage($image);
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
