<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasPublicStorageImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasPublicStorageImage;
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
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
            'parent_id' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Category $category): void {
            if (empty($category->slug)) {
                $category->slug = static::generateUniqueSlug($category->title);
            }
        });

        static::updating(function (Category $category): void {
            if ($category->isDirty('title') && ! $category->isDirty('slug')) {
                $category->slug = static::generateUniqueSlug($category->title, $category->id);
            }
        });

        static::deleting(function (Category $category): void {
            if ($category->isForceDeleting()) {
                static::deleteStoredImage($category);
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public function isSubcategory(): bool
    {
        return $this->parent_id !== null;
    }

    public function storeUrl(): string
    {
        if ($this->isSubcategory() && $this->parent) {
            return route('store.categories.sub.show', [
                'parentSlug' => $this->parent->slug,
                'slug' => $this->slug,
            ]);
        }

        return route('store.categories.show', $this->slug);
    }

    /**
     * @return Collection<int, int>
     */
    public function descendantIds(): Collection
    {
        $ids = collect([$this->id]);

        $this->loadMissing('children');

        foreach ($this->children as $child) {
            $ids = $ids->merge($child->descendantIds());
        }

        return $ids->unique()->values();
    }
}
