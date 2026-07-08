<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasPublicStorageImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Brand extends Model
{
    use HasPublicStorageImage;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'image_url',
        'meta_keywords',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Brand $brand): void {
            if (empty($brand->slug)) {
                $brand->slug = static::generateUniqueSlug($brand->title);
            }
        });

        static::updating(function (Brand $brand): void {
            if ($brand->isDirty('title') && ! $brand->isDirty('slug')) {
                $brand->slug = static::generateUniqueSlug($brand->title, $brand->id);
            }
        });

        static::deleting(function (Brand $brand): void {
            static::deleteStoredImage($brand);
        });
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $counter = 1;

        while (static::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
