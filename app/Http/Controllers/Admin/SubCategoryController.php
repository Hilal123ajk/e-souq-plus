<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubCategoryRequest;
use App\Http\Requests\Admin\UpdateSubCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SubCategoryController extends Controller
{
    public function index(): View
    {
        $search = request()->string('search')->trim()->toString();
        $status = request()->string('status')->toString();
        $parentId = request()->integer('parent_id') ?: null;

        $subcategories = Category::query()
            ->whereNotNull('parent_id')
            ->with(['parent:id,title,slug'])
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($parentId, fn ($query) => $query->where('parent_id', $parentId))
            ->when($status === 'trashed', fn ($query) => $query->onlyTrashed())
            ->orderBy('title')
            ->get();

        $parentCategories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('title')
            ->get(['id', 'title']);

        $showingTrashed = $status === 'trashed';
        $filtersActive = $search !== '' || $status !== '' || $parentId !== null;

        return view('admin.subcategories.index', compact(
            'subcategories',
            'parentCategories',
            'search',
            'status',
            'parentId',
            'showingTrashed',
            'filtersActive',
        ));
    }

    public function store(StoreSubCategoryRequest $request): RedirectResponse
    {
        $parent = Category::query()
            ->whereNull('parent_id')
            ->find($request->integer('parent_id'));

        if ($parent === null) {
            return redirect()
                ->route('admin.subcategories')
                ->withErrors(['parent_id' => 'Sub-categories must belong to a main category.']);
        }

        $data = $request->validated();
        $data['image_url'] = $request->file('image')->store('categories', 'public');
        unset($data['image']);

        if (empty($data['slug'])) {
            unset($data['slug']);
        }

        $category = Category::query()->create($data);

        return redirect()
            ->route('admin.subcategories')
            ->with('success', 'Sub-category created successfully.');
    }

    public function update(UpdateSubCategoryRequest $request, Category $category): RedirectResponse
    {
        if ($category->parent_id === null) {
            abort(404);
        }

        $parent = Category::query()
            ->whereNull('parent_id')
            ->find($request->integer('parent_id'));

        if ($parent === null) {
            return redirect()
                ->route('admin.subcategories', request()->only(['search', 'status', 'parent_id']))
                ->withErrors(['parent_id' => 'Sub-categories must belong to a main category.']);
        }

        $data = $request->validated();

        if ($request->hasFile('image')) {
            $existingPath = $category->getStoredImagePath();

            if ($existingPath !== '' && ! str_starts_with($existingPath, 'http')) {
                Storage::disk('public')->delete($existingPath);
            }

            $data['image_url'] = $request->file('image')->store('categories', 'public');
        }

        unset($data['image']);

        if (array_key_exists('slug', $data) && blank($data['slug'])) {
            $data['slug'] = Category::generateUniqueSlug($data['title'], $category->id);
        }

        $category->update($data);

        return redirect()
            ->route('admin.subcategories', request()->only(['search', 'status', 'parent_id']))
            ->with('success', 'Sub-category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->parent_id === null) {
            abort(404);
        }

        if ($category->products()->exists()) {
            return redirect()
                ->route('admin.subcategories', request()->only(['search', 'status', 'parent_id']))
                ->withErrors(['category' => 'Cannot delete a sub-category that has products assigned.']);
        }

        $category->delete();

        return redirect()
            ->route('admin.subcategories', request()->only(['search', 'status', 'parent_id']))
            ->with('success', 'Sub-category moved to trash.');
    }

    public function restore(int $categoryId): RedirectResponse
    {
        $category = Category::onlyTrashed()
            ->whereNotNull('parent_id')
            ->findOrFail($categoryId);

        $category->restore();

        return redirect()
            ->route('admin.subcategories', request()->only(['search', 'status', 'parent_id']))
            ->with('success', 'Sub-category restored successfully.');
    }

    public function forceDestroy(int $categoryId): RedirectResponse
    {
        $category = Category::onlyTrashed()
            ->whereNotNull('parent_id')
            ->findOrFail($categoryId);

        if ($category->products()->exists()) {
            return redirect()
                ->route('admin.subcategories', ['status' => 'trashed'])
                ->withErrors(['category' => 'Cannot permanently delete a sub-category that still has products assigned.']);
        }

        $category->forceDelete();

        return redirect()
            ->route('admin.subcategories', request()->only(['search', 'status', 'parent_id']))
            ->with('success', 'Sub-category permanently deleted.');
    }
}
