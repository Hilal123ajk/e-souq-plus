<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {}

    public function index(): View
    {
        $search = request()->string('search')->trim()->toString();
        $categoryId = request()->integer('category_id') ?: null;
        $brandId = request()->integer('brand_id') ?: null;
        $status = request()->string('status')->toString();

        $products = Product::query()
            ->with([
                'category:id,title,slug,parent_id',
                'category.parent:id,title',
                'brand:id,title,slug',
                'images' => fn ($query) => $query->orderBy('sort_order'),
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, function ($query) use ($categoryId): void {
                $category = Category::query()->find($categoryId);

                if ($category !== null && $category->parent_id === null) {
                    $query->whereIn('category_id', $category->descendantIds());
                } else {
                    $query->where('category_id', $categoryId);
                }
            })
            ->when($brandId, fn ($query) => $query->where('brand_id', $brandId))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($status === 'low', fn ($query) => $query->where('stock_quantity', '<=', 10))
            ->when($status === 'trashed', fn ($query) => $query->onlyTrashed())
            ->orderByDesc('created_at')
            ->get();

        $mainCategories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('title')
            ->get(['id', 'title']);

        $subcategories = Category::query()
            ->whereNotNull('parent_id')
            ->where('is_active', true)
            ->with('parent:id,title')
            ->orderBy('title')
            ->get(['id', 'title', 'parent_id']);

        $subcategoriesByParent = $subcategories
            ->groupBy('parent_id')
            ->map(fn ($items) => $items->map(fn (Category $category) => [
                'id' => $category->id,
                'title' => $category->title,
            ])->values())
            ->all();

        $brands = Brand::query()->where('is_active', true)->orderBy('title')->get(['id', 'title']);
        $showingTrashed = $status === 'trashed';

        $oldMainCategoryId = old('_form') ? old('main_category_id') : null;
        $oldSubCategoryId = old('_form') ? old('sub_category_id') : null;

        return view('admin.products.index', compact(
            'products',
            'mainCategories',
            'subcategories',
            'subcategoriesByParent',
            'brands',
            'search',
            'categoryId',
            'brandId',
            'status',
            'showingTrashed',
            'oldMainCategoryId',
            'oldSubCategoryId',
        ));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['image_url'] = $request->file('image')->store('products', 'public');
        unset($data['image'], $data['gallery_images'], $data['gallery_labels'], $data['remove_gallery_ids']);

        if (empty($data['slug'])) {
            unset($data['slug']);
        }

        if (empty($data['brand_id'])) {
            $data['brand_id'] = null;
        }

        $product = Product::query()->create($data);
        $this->storeGalleryImages($product, $request);

        $this->activityLog->log('created', 'product', $product->id, $product->name);

        return redirect()
            ->route('admin.products', $request->only(['search', 'category_id', 'brand_id', 'status']))
            ->with('success', 'Product created successfully.');
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $existingPath = $product->getStoredImagePath();

            if ($existingPath !== '' && ! str_starts_with($existingPath, 'http')) {
                Storage::disk('public')->delete($existingPath);
            }

            $data['image_url'] = $request->file('image')->store('products', 'public');
        }

        unset($data['image'], $data['gallery_images'], $data['gallery_labels'], $data['remove_gallery_ids']);

        if (array_key_exists('slug', $data) && $data['slug'] === '') {
            unset($data['slug']);
        }

        if (empty($data['brand_id'])) {
            $data['brand_id'] = null;
        }

        $product->update($data);
        $this->syncGalleryLabels($product, $request);
        $this->removeGalleryImages($product, $request);
        $this->storeGalleryImages($product, $request);

        $this->activityLog->log('updated', 'product', $product->id, $product->name);

        return redirect()
            ->route('admin.products', $request->only(['search', 'category_id', 'brand_id', 'status']))
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name;
        $id = $product->id;

        $product->delete();

        $this->activityLog->log('deleted', 'product', $id, $name);

        return redirect()
            ->route('admin.products', request()->only(['search', 'category_id', 'brand_id', 'status']))
            ->with('success', 'Product moved to trash. You can restore it from the Trash filter.');
    }

    public function restore(int $productId): RedirectResponse
    {
        $product = Product::onlyTrashed()->findOrFail($productId);
        $product->restore();

        $this->activityLog->log('restored', 'product', $product->id, $product->name);

        return redirect()
            ->route('admin.products', request()->only(['search', 'category_id', 'brand_id', 'status']))
            ->with('success', 'Product restored successfully.');
    }

    public function forceDestroy(int $productId): RedirectResponse
    {
        $product = Product::onlyTrashed()->findOrFail($productId);
        $name = $product->name;
        $id = $product->id;

        $product->forceDelete();

        $this->activityLog->log('force_deleted', 'product', $id, $name);

        return redirect()
            ->route('admin.products', request()->only(['search', 'category_id', 'brand_id', 'status']))
            ->with('success', 'Product permanently deleted.');
    }

    private function storeGalleryImages(Product $product, Request $request): void
    {
        if (! $request->hasFile('gallery_images')) {
            return;
        }

        $labels = $request->input('gallery_labels', []);
        $sortOrder = (int) $product->images()->max('sort_order');

        foreach ($request->file('gallery_images') as $index => $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $sortOrder++;

            $product->images()->create([
                'image_url' => $file->store('products/gallery', 'public'),
                'label' => is_array($labels) ? ($labels[$index] ?? null) : null,
                'sort_order' => $sortOrder,
            ]);
        }
    }

    private function syncGalleryLabels(Product $product, Request $request): void
    {
        $labels = $request->input('gallery_labels', []);

        if (! is_array($labels)) {
            return;
        }

        foreach ($labels as $imageId => $label) {
            $product->images()
                ->where('id', (int) $imageId)
                ->update(['label' => $label !== '' ? $label : null]);
        }
    }

    private function removeGalleryImages(Product $product, Request $request): void
    {
        $removeIds = $request->input('remove_gallery_ids', []);

        if (! is_array($removeIds) || $removeIds === []) {
            return;
        }

        $product->images()
            ->whereIn('id', array_map('intval', $removeIds))
            ->get()
            ->each(fn (ProductImage $image) => $image->delete());
    }
}
