<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBrandRequest;
use App\Http\Requests\Admin\UpdateBrandRequest;
use App\Models\Brand;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {}

    public function index(): View
    {
        $search = request()->string('search')->trim()->toString();

        $brands = Brand::query()
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('title')
            ->get();

        return view('admin.brands.index', compact('brands', 'search'));
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_url'] = $request->file('image')->store('brands', 'public');
        }

        unset($data['image']);

        if (empty($data['slug'])) {
            unset($data['slug']);
        }

        $brand = Brand::query()->create($data);

        $this->activityLog->log('created', 'brand', $brand->id, $brand->title);

        return redirect()
            ->route('admin.brands')
            ->with('success', 'Brand created successfully.');
    }

    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $existingPath = $brand->getStoredImagePath();

            if ($existingPath !== '' && ! str_starts_with($existingPath, 'http')) {
                Storage::disk('public')->delete($existingPath);
            }

            $data['image_url'] = $request->file('image')->store('brands', 'public');
        }

        unset($data['image']);

        if (array_key_exists('slug', $data) && $data['slug'] === '') {
            unset($data['slug']);
        }

        $brand->update($data);

        $this->activityLog->log('updated', 'brand', $brand->id, $brand->title);

        return redirect()
            ->route('admin.brands')
            ->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $name = $brand->title;
        $id = $brand->id;

        $brand->delete();

        $this->activityLog->log('deleted', 'brand', $id, $name);

        return redirect()
            ->route('admin.brands')
            ->with('success', 'Brand deleted successfully.');
    }
}
