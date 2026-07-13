<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasPublicStorageImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasPublicStorageImage;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'material',
        'finish',
        'has_dimensions',
        'thickness',
        'height',
        'width',
        'sku',
        'category_id',
        'brand_id',
        'price',
        'cost_price',
        'stock_quantity',
        'image_url',
        'meta_keywords',
        'is_active',
        'is_featured',
        'has_variants',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'thickness' => 'decimal:2',
            'height' => 'decimal:2',
            'width' => 'decimal:2',
            'stock_quantity' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'has_variants' => 'boolean',
            'has_dimensions' => 'boolean',
        ];
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public function specificationRows(): array
    {
        $rows = [];

        if (filled($this->material)) {
            $rows[] = ['label' => 'Material', 'value' => (string) $this->material];
        }

        if (filled($this->finish)) {
            $rows[] = ['label' => 'Finish', 'value' => (string) $this->finish];
        }

        if ($this->has_dimensions) {
            if ($this->thickness !== null) {
                $rows[] = ['label' => 'Thickness', 'value' => $this->formatDimension($this->thickness)];
            }

            if ($this->height !== null) {
                $rows[] = ['label' => 'Height', 'value' => $this->formatDimension($this->height)];
            }

            if ($this->width !== null) {
                $rows[] = ['label' => 'Width', 'value' => $this->formatDimension($this->width)];
            }
        }

        return $rows;
    }

    private function formatDimension(mixed $value): string
    {
        $cm = (float) $value;
        $cmLabel = rtrim(rtrim(number_format($cm, 2, '.', ''), '0'), '.');
        $feet = $cm / 30.48;
        $feetLabel = rtrim(rtrim(number_format($feet, 2, '.', ''), '0'), '.');

        return $cmLabel.' cm / '.$feetLabel.' ft';
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name);
            }
        });

        static::updating(function (Product $product): void {
            if ($product->isDirty('name') && ! $product->isDirty('slug')) {
                $product->slug = static::generateUniqueSlug($product->name, $product->id);
            }
        });

        static::deleting(function (Product $product): void {
            if ($product->isForceDeleting()) {
                static::deleteStoredImage($product);
                $product->images()->get()->each(fn (ProductImage $image) => $image->delete());
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (static::withTrashed()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }
}
