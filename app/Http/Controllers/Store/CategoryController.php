<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\StoreCatalogService;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private readonly StoreCatalogService $catalog,
    ) {}

    public function show(string $slug): View
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->firstOrFail();

        return view('categories.show', [
            'category' => $category,
            'parentCategory' => null,
            'subcategories' => $this->catalog->getSubcategoriesForCategory($category),
            'showSubcategorySection' => true,
            'filterSlug' => $category->slug,
            'rootCategorySlug' => $category->slug,
            'includeChildProducts' => true,
        ]);
    }

    public function showSubcategory(string $parentSlug, string $slug): View
    {
        $parent = Category::query()
            ->where('slug', $parentSlug)
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->firstOrFail();

        $category = Category::query()
            ->where('slug', $slug)
            ->where('parent_id', $parent->id)
            ->where('is_active', true)
            ->firstOrFail();

        return view('categories.show', [
            'category' => $category,
            'parentCategory' => $parent,
            'subcategories' => $this->catalog->getSubcategoriesForCategory($category),
            'showSubcategorySection' => true,
            'filterSlug' => $category->slug,
            'rootCategorySlug' => $parent->slug,
            'includeChildProducts' => false,
        ]);
    }
}
