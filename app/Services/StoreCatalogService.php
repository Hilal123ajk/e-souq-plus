<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

class StoreCatalogService
{
    public function getCategoriesForStore(): array
    {
        $roots = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => fn ($query) => $query->where('is_active', true)->orderBy('title')])
            ->orderBy('title')
            ->get();

        return $roots
            ->map(fn (Category $category) => $this->transformRootCategory($category))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSubcategoriesForCategory(Category $category): array
    {
        $parent = $category->isRoot() ? $category : $category->parent;

        if ($parent === null) {
            return [];
        }

        return Category::query()
            ->where('parent_id', $parent->id)
            ->where('is_active', true)
            ->orderBy('title')
            ->get()
            ->map(fn (Category $sub) => $this->transformSubcategory($sub, $parent))
            ->values()
            ->all();
    }

    public function getProductsForStore(): array
    {
        return $this->transformProducts(
            Product::query()
                ->where('is_active', true)
                ->with(['category.parent:id,slug,title', 'category:id,slug,title,parent_id', 'brand:id,title,slug', 'images'])
                ->orderByDesc('is_featured')
                ->orderByDesc('created_at')
                ->get()
        );
    }

    public function getBrandsForStore(): array
    {
        return Brand::query()
            ->where('is_active', true)
            ->whereHas('products', fn ($query) => $query->where('is_active', true))
            ->orderBy('title')
            ->pluck('title')
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFeaturedProducts(int $limit = 8): array
    {
        $products = Product::query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->with(['category.parent:id,slug,title', 'category:id,slug,title,parent_id', 'brand:id,title,slug', 'images'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        if ($products->isEmpty()) {
            $products = Product::query()
                ->where('is_active', true)
                ->with(['category.parent:id,slug,title', 'category:id,slug,title,parent_id', 'brand:id,title,slug', 'images'])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();
        }

        return $this->transformProducts($products);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getNewArrivals(int $limit = 5): array
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with(['category.parent:id,slug,title', 'category:id,slug,title,parent_id', 'brand:id,title,slug', 'images'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return $this->transformProducts($products);
    }

    public function getShuffledProductsPaginated(int $perPage = 12): LengthAwarePaginator
    {
        $page = max(1, (int) request()->query('page', 1));

        if ($page === 1) {
            $ids = Product::query()
                ->where('is_active', true)
                ->pluck('id')
                ->shuffle()
                ->values()
                ->all();

            session(['home_product_ids' => $ids]);
        } else {
            $ids = session('home_product_ids', []);

            if ($ids === []) {
                $ids = Product::query()
                    ->where('is_active', true)
                    ->pluck('id')
                    ->shuffle()
                    ->values()
                    ->all();

                session(['home_product_ids' => $ids]);
            }
        }

        $total = count($ids);
        $pageIds = array_slice($ids, ($page - 1) * $perPage, $perPage);

        if ($pageIds === []) {
            return new Paginator([], $total, $perPage, $page, [
                'path' => url('/'),
                'pageName' => 'page',
            ]);
        }

        $order = array_flip($pageIds);

        $products = Product::query()
            ->whereIn('id', $pageIds)
            ->with(['category.parent:id,slug,title', 'category:id,slug,title,parent_id', 'brand:id,title,slug', 'images'])
            ->get()
            ->sortBy(fn (Product $product) => $order[$product->id] ?? 0)
            ->values();

        return new Paginator(
            $this->transformProducts($products),
            $total,
            $perPage,
            $page,
            [
                'path' => url('/'),
                'pageName' => 'page',
            ]
        );
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getHeroSlidesFromCategories(int $limit = 4): array
    {
        $accents = [
            'from-violet-600/90 to-indigo-900/70',
            'from-slate-900/85 to-slate-900/40',
            'from-rose-700/80 to-purple-900/50',
            'from-amber-700/80 to-orange-900/50',
        ];

        return Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('title')
            ->limit($limit)
            ->get()
            ->map(function (Category $category, int $index) use ($accents) {
                return [
                    'title' => $category->title,
                    'subtitle' => $category->description ?: ('Discover '.$category->title.' — handpicked for you'),
                    'cta' => 'Shop Now',
                    'link' => route('store.categories.show', $category->slug),
                    'image' => $this->resolveCategoryImage($category),
                    'accent' => $accents[$index % count($accents)],
                    'badge' => $category->title,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSidePromoCategories(int $skip = 4, int $limit = 2): array
    {
        return Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('title')
            ->skip($skip)
            ->limit($limit)
            ->get()
            ->map(fn (Category $category) => [
                'slug' => $category->slug,
                'name' => $category->title,
                'image' => $this->resolveCategoryImage($category),
                'url' => route('store.categories.show', $category->slug),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Product>  $products
     * @return array<int, array<string, mixed>>
     */
    public function transformProducts(Collection $products): array
    {
        return $products
            ->map(fn (Product $product) => $this->transformProduct($product))
            ->values()
            ->all();
    }

    public function transformProduct(Product $product): array
    {
        $galleryUrls = $product->images
            ->map(fn ($image) => $image->image_public_url)
            ->filter()
            ->values()
            ->all();

        $images = array_values(array_unique(array_filter([
            $product->image_public_url,
            ...$galleryUrls,
        ])));

        $category = $product->category;
        $rootCategorySlug = $category?->isSubcategory()
            ? ($category->parent?->slug ?? '')
            : ($category?->slug ?? '');

        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'brand' => $product->brand?->title ?? '',
            'brandSlug' => $product->brand?->slug ?? '',
            'category' => $category?->slug ?? '',
            'categoryName' => $category?->title ?? '',
            'categoryUrl' => $category?->storeUrl() ?? '',
            'parentCategory' => $rootCategorySlug,
            'price' => (float) $product->price,
            'originalPrice' => (float) $product->price,
            'discount' => 0,
            'rating' => 0,
            'reviews' => 0,
            'image' => $product->image_public_url,
            'images' => $images,
            'gallery' => $product->images
                ->map(fn ($image) => [
                    'id' => $image->id,
                    'url' => $image->image_public_url,
                    'label' => $image->label,
                ])
                ->values()
                ->all(),
            'colors' => $product->has_variants
                ? $product->images
                    ->map(fn ($image) => [
                        'id' => $image->id,
                        'url' => $image->image_public_url,
                        'label' => $image->label ?: 'Color',
                    ])
                    ->values()
                    ->all()
                : [],
            'hasVariants' => $product->has_variants,
            'hasColors' => $product->has_variants && $product->images->isNotEmpty(),
            'description' => $product->description,
            'featured' => $product->is_featured,
            'inStock' => $product->stock_quantity > 0,
            'sku' => $product->sku,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformRootCategory(Category $category): array
    {
        $categoryIds = $category->descendantIds();

        $count = Product::query()
            ->where('is_active', true)
            ->whereIn('category_id', $categoryIds)
            ->count();

        return [
            'slug' => $category->slug,
            'name' => $category->title,
            'image' => $this->resolveCategoryImage($category),
            'count' => $count,
            'description' => $category->description,
            'subcategories' => $category->children
                ->map(fn (Category $sub) => $this->transformSubcategory($sub, $category))
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformSubcategory(Category $subcategory, Category $parent): array
    {
        $count = Product::query()
            ->where('is_active', true)
            ->where('category_id', $subcategory->id)
            ->count();

        return [
            'slug' => $subcategory->slug,
            'name' => $subcategory->title,
            'image' => $this->resolveCategoryImage($subcategory),
            'count' => $count,
            'description' => $subcategory->description,
            'parentSlug' => $parent->slug,
            'url' => route('store.categories.sub.show', [
                'parentSlug' => $parent->slug,
                'slug' => $subcategory->slug,
            ]),
        ];
    }

    private function resolveCategoryImage(Category $category): string
    {
        return $category->image_public_url;
    }
}
