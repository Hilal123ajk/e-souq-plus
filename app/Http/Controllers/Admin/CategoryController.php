<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {}

    public function index(): View
    {
        $search = request()->string('search')->trim()->toString();
        $status = request()->string('status')->toString();

        $categories = Category::query()
            ->whereNull('parent_id')
            ->withCount('children')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($status === 'trashed', fn ($query) => $query->onlyTrashed())
            ->orderBy('title')
            ->get();

        $showingTrashed = $status === 'trashed';

        return view('admin.categories.index', compact(
            'categories',
            'search',
            'status',
            'showingTrashed',
        ));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['parent_id'] = null;
        $data['image_url'] = $request->file('image')->store('categories', 'public');
        unset($data['image']);

        if (empty($data['slug'])) {
            unset($data['slug']);
        }

        $category = Category::query()->create($data);

        $this->activityLog->log('created', 'category', $category->id, $category->title);

        return redirect()
            ->route('admin.categories')
            ->with('success', 'Category created successfully.');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        if ($category->parent_id !== null) {
            abort(404);
        }

        $data = $request->validated();
        $data['parent_id'] = null;

        if ($request->hasFile('image')) {
            $existingPath = $category->getStoredImagePath();

            if ($existingPath !== '' && ! str_starts_with($existingPath, 'http')) {
                Storage::disk('public')->delete($existingPath);
            }

            $data['image_url'] = $request->file('image')->store('categories', 'public');
        }

        unset($data['image']);

        if (array_key_exists('slug', $data) && $data['slug'] === '') {
            unset($data['slug']);
        }

        $category->update($data);

        $this->activityLog->log('updated', 'category', $category->id, $category->title);

        return redirect()
            ->route('admin.categories', request()->only(['search', 'status']))
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->parent_id !== null) {
            abort(404);
        }

        if ($category->products()->exists()) {
            return redirect()
                ->route('admin.categories', request()->only(['search', 'status']))
                ->withErrors(['category' => 'Cannot delete a category that has products assigned.']);
        }

        if ($category->children()->exists()) {
            return redirect()
                ->route('admin.categories', request()->only(['search', 'status']))
                ->withErrors(['category' => 'Cannot delete a category that has sub-categories. Remove them from Sub Categories first.']);
        }

        $name = $category->title;
        $id = $category->id;

        $category->delete();

        $this->activityLog->log('deleted', 'category', $id, $name);

        return redirect()
            ->route('admin.categories', request()->only(['search', 'status']))
            ->with('success', 'Category moved to trash. You can restore it from the Trash filter.');
    }

    public function restore(int $categoryId): RedirectResponse
    {
        $category = Category::onlyTrashed()
            ->whereNull('parent_id')
            ->findOrFail($categoryId);

        $category->restore();

        $this->activityLog->log('restored', 'category', $category->id, $category->title);

        return redirect()
            ->route('admin.categories', request()->only(['search', 'status']))
            ->with('success', 'Category restored successfully.');
    }

    public function forceDestroy(int $categoryId): RedirectResponse
    {
        $category = Category::onlyTrashed()
            ->whereNull('parent_id')
            ->findOrFail($categoryId);

        if ($category->products()->exists()) {
            return redirect()
                ->route('admin.categories', ['status' => 'trashed'])
                ->withErrors(['category' => 'Cannot permanently delete a category that still has products assigned.']);
        }

        if ($category->children()->exists()) {
            return redirect()
                ->route('admin.categories', ['status' => 'trashed'])
                ->withErrors(['category' => 'Cannot permanently delete a category that still has sub-categories.']);
        }

        $name = $category->title;
        $id = $category->id;

        $category->forceDelete();

        $this->activityLog->log('force_deleted', 'category', $id, $name);

        return redirect()
            ->route('admin.categories', request()->only(['search', 'status']))
            ->with('success', 'Category permanently deleted.');
    }
}
