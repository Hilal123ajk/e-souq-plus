@extends('layouts.admin')

@section('title', 'Brands')
@section('page_title', 'Brands')
@section('page_subtitle', 'Manage product brands and logos')

@section('header_action')
<button type="button" @click="$dispatch('open-brand-drawer')" class="px-4 py-2 bg-souq-600 text-white text-sm font-semibold rounded-xl hover:bg-souq-700 transition flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
    Add Brand
</button>
@endsection

@section('content')
@php
    $shouldOpenForm = $errors->any() && old('_form');
    $isEditing = old('_form') === 'edit';
    $editBrandFromOld = $isEditing ? [
        'id' => (int) old('_brand_id'),
        'title' => old('title'),
        'slug' => old('slug'),
        'description' => old('description'),
        'meta_keywords' => old('meta_keywords'),
        'is_active' => (bool) old('is_active'),
        'image' => null,
        'products_count' => 0,
    ] : null;
@endphp

<div x-data="{
    formDrawerOpen: {{ $shouldOpenForm ? 'true' : 'false' }},
    detailDrawerOpen: false,
    editing: {{ $isEditing ? 'true' : 'false' }},
    editingId: {{ $isEditing ? (int) old('_brand_id') : 'null' }},
    selectedBrand: @js($editBrandFromOld),
    editForm: {
        title: '',
        slug: '',
        description: '',
        meta_keywords: '',
        is_active: true,
    },

    resetEditForm(brand = null) {
        this.editForm = {
            title: brand?.title ?? '',
            slug: brand?.slug ?? '',
            description: brand?.description ?? '',
            meta_keywords: brand?.meta_keywords ?? '',
            is_active: brand?.is_active ?? true,
        };
    },

    openDrawer(flag) {
        this[flag] = true;
        Alpine.store('adminUi').lockScroll();
    },

    closeDrawer(flag) {
        this[flag] = false;
        if (!this.formDrawerOpen && !this.detailDrawerOpen) {
            Alpine.store('adminUi').unlockScroll();
        }
    },

    openCreate() {
        this.editing = false;
        this.editingId = null;
        this.selectedBrand = null;
        this.detailDrawerOpen = false;
        this.openDrawer('formDrawerOpen');
    },

    openEdit(brand) {
        this.editing = true;
        this.editingId = brand.id;
        this.selectedBrand = brand;
        this.resetEditForm(brand);
        this.detailDrawerOpen = false;
        this.openDrawer('formDrawerOpen');
    },

    openDetail(brand) {
        this.selectedBrand = brand;
        this.formDrawerOpen = false;
        this.openDrawer('detailDrawerOpen');
    }
}" x-init="@if($isEditing) resetEditForm(selectedBrand) @endif" @open-brand-drawer.window="openCreate()">

    <form method="GET" action="{{ route('admin.brands') }}" class="bg-white rounded-2xl border border-stone-200 p-4 mb-6 flex flex-col sm:flex-row gap-3">
        <input type="search" name="search" value="{{ $search ?? '' }}" placeholder="Search brands by title, slug, or description..."
               class="flex-1 px-4 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2.5 bg-souq-600 text-white text-sm font-semibold rounded-xl hover:bg-souq-700 transition">Search</button>
            @if(!empty($search))
            <a href="{{ route('admin.brands') }}" class="px-4 py-2.5 border border-stone-200 text-sm font-medium rounded-xl hover:bg-stone-50 transition">Clear</a>
            @endif
        </div>
    </form>

    @if ($brands->isEmpty())
    <div class="bg-white rounded-2xl border border-stone-200 p-10 text-center">
        <p class="text-stone-500 mb-4">{{ !empty($search) ? 'No brands match your search.' : 'No brands yet. Create your first brand to organize products.' }}</p>
        <button type="button" @click="openCreate()" class="px-5 py-2.5 bg-souq-600 text-white text-sm font-semibold rounded-xl hover:bg-souq-700 transition">
            Add Brand
        </button>
    </div>
    @else
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach ($brands as $brand)
        @php
            $brandData = [
                'id' => $brand->id,
                'title' => $brand->title,
                'slug' => $brand->slug,
                'description' => $brand->description,
                'meta_keywords' => $brand->meta_keywords,
                'is_active' => $brand->is_active,
                'image' => $brand->image_public_url,
                'products_count' => $brand->products_count,
            ];
        @endphp
        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden group hover:shadow-md transition cursor-pointer"
             @click="openDetail(@js($brandData))">
            <div class="aspect-video overflow-hidden bg-stone-100 flex items-center justify-center">
                @if ($brand->image_public_url)
                <img src="{{ $brand->image_public_url }}" alt="{{ $brand->title }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                @else
                <div class="flex flex-col items-center justify-center text-stone-400 p-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                    <span class="text-xs mt-2 font-medium">No logo</span>
                </div>
                @endif
            </div>
            <div class="p-4">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="font-bold text-stone-900">{{ $brand->title }}</h3>
                        <p class="text-xs text-stone-400 mt-0.5">{{ $brand->slug }}</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold shrink-0 {{ $brand->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-600' }}">
                        {{ $brand->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <p class="text-xs text-stone-500 mt-2">{{ $brand->products_count }} {{ Str::plural('product', $brand->products_count) }}</p>
                @if ($brand->description)
                <p class="text-sm text-stone-500 mt-2 line-clamp-2">{{ $brand->description }}</p>
                @endif
                <div class="flex gap-2 mt-3" @click.stop>
                    <button type="button" @click="openEdit(@js($brandData))"
                            class="w-full py-2 text-xs font-semibold text-center bg-souq-600 text-white rounded-lg hover:bg-souq-700 transition">Edit</button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Add / Edit drawer --}}
    <div x-show="formDrawerOpen" x-cloak @keydown.escape.window="formDrawerOpen && closeDrawer('formDrawerOpen')"
         class="fixed inset-0 z-[60]" role="dialog">
        <div x-show="formDrawerOpen" x-transition.opacity @click="closeDrawer('formDrawerOpen')" class="absolute inset-0 bg-black/40"></div>
        <div x-show="formDrawerOpen"
             x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
             class="absolute right-0 top-0 bottom-0 w-full max-w-lg bg-white shadow-2xl flex flex-col border-l border-stone-200">
            <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200 shrink-0">
                <h2 class="text-lg font-bold text-stone-900" x-text="editing ? 'Edit Brand' : 'Add Brand'"></h2>
                <button type="button" @click="closeDrawer('formDrawerOpen')" class="p-2 text-stone-500 hover:bg-stone-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Create form --}}
            <form x-show="!editing" method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data"
                  class="flex-1 overflow-y-auto p-5 space-y-4">
                @csrf
                <input type="hidden" name="_form" value="create">

                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('_form') === 'create' ? old('title') : '' }}" required
                           class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Slug</label>
                    <input type="text" name="slug" value="{{ old('_form') === 'create' ? old('slug') : '' }}" placeholder="Auto-generated if empty"
                           class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">{{ old('_form') === 'create' ? old('description') : '' }}</textarea>
                    <p class="text-[11px] text-stone-400 mt-1">Used on brand pages and as the SEO meta description.</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Logo <span class="text-stone-400 font-normal">(optional)</span></label>
                    <input type="file" name="image" accept="image/*"
                           class="w-full text-sm text-stone-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-stone-100 file:font-semibold file:text-stone-900 hover:file:bg-stone-200">
                </div>

                <div class="pt-2 border-t border-stone-100">
                    <p class="text-xs font-bold text-stone-900 uppercase tracking-wide mb-3">SEO</p>
                    <div>
                        <label class="text-xs font-semibold text-stone-600 block mb-1">Meta Keywords <span class="text-stone-400 font-normal">(optional)</span></label>
                        <input type="text" name="meta_keywords" value="{{ old('_form') === 'create' ? old('meta_keywords') : '' }}"
                               placeholder="apple, samsung, accessories"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                        <p class="text-[11px] text-stone-400 mt-1">Title and description above are used for SEO meta tags.</p>
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" class="rounded accent-souq-600" {{ old('_form') === 'create' ? (old('is_active', true) ? 'checked' : '') : 'checked' }}>
                    Active
                </label>

                <div class="border-t border-stone-200 pt-4 flex gap-3">
                    <button type="button" @click="closeDrawer('formDrawerOpen')" class="flex-1 py-2.5 border border-stone-200 rounded-xl text-sm font-medium bg-white">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 bg-souq-600 text-white rounded-xl text-sm font-semibold hover:bg-souq-700">Save Brand</button>
                </div>
            </form>

            {{-- Edit form --}}
            <form x-show="editing" method="POST" :action="'/admin/brands/' + editingId" enctype="multipart/form-data"
                  class="flex-1 overflow-y-auto p-5 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form" value="edit">
                <input type="hidden" name="_brand_id" :value="editingId">

                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required x-model="editForm.title"
                           class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Slug</label>
                    <input type="text" name="slug" x-model="editForm.slug"
                           class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Description</label>
                    <textarea name="description" rows="3" x-model="editForm.description"
                              class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500"></textarea>
                    <p class="text-[11px] text-stone-400 mt-1">Used on brand pages and as the SEO meta description.</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Replace Logo <span class="text-stone-400 font-normal">(optional)</span></label>
                    <template x-if="selectedBrand?.image">
                        <img :src="selectedBrand.image" alt="" class="w-full h-32 object-contain rounded-xl border border-stone-200 mb-2 bg-stone-50">
                    </template>
                    <input type="file" name="image" accept="image/*"
                           class="w-full text-sm text-stone-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-stone-100 file:font-semibold file:text-stone-900 hover:file:bg-stone-200">
                    <p class="text-[11px] text-stone-400 mt-1">Leave empty to keep the current logo.</p>
                </div>

                <div class="pt-2 border-t border-stone-100">
                    <p class="text-xs font-bold text-stone-900 uppercase tracking-wide mb-3">SEO</p>
                    <div>
                        <label class="text-xs font-semibold text-stone-600 block mb-1">Meta Keywords <span class="text-stone-400 font-normal">(optional)</span></label>
                        <input type="text" name="meta_keywords" x-model="editForm.meta_keywords"
                               placeholder="apple, samsung, accessories"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                        <p class="text-[11px] text-stone-400 mt-1">Title and description above are used for SEO meta tags.</p>
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" class="rounded accent-souq-600" x-model="editForm.is_active">
                    Active
                </label>

                <div class="border-t border-stone-200 pt-4 flex gap-3">
                    <button type="button" @click="closeDrawer('formDrawerOpen')" class="flex-1 py-2.5 border border-stone-200 rounded-xl text-sm font-medium bg-white">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 bg-souq-600 text-white rounded-xl text-sm font-semibold hover:bg-souq-700">Update Brand</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Brand detail drawer --}}
    <div x-show="detailDrawerOpen" x-cloak @keydown.escape.window="detailDrawerOpen && closeDrawer('detailDrawerOpen')"
         class="fixed inset-0 z-[60]" role="dialog">
        <div x-show="detailDrawerOpen" x-transition.opacity @click="closeDrawer('detailDrawerOpen')" class="absolute inset-0 bg-black/40"></div>
        <div x-show="detailDrawerOpen"
             x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
             class="absolute right-0 top-0 bottom-0 w-full max-w-md bg-white shadow-2xl flex flex-col border-l border-stone-200">
            <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200 shrink-0">
                <h2 class="text-lg font-bold text-stone-900">Brand Detail</h2>
                <button type="button" @click="closeDrawer('detailDrawerOpen')" class="p-2 text-stone-500 hover:bg-stone-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <template x-if="selectedBrand">
                <div class="flex-1 overflow-y-auto p-5 space-y-5">
                    <template x-if="selectedBrand.image">
                        <img :src="selectedBrand.image" :alt="selectedBrand.title" class="w-full aspect-video object-contain rounded-2xl border border-stone-200 bg-stone-50">
                    </template>
                    <template x-if="!selectedBrand.image">
                        <div class="w-full aspect-video rounded-2xl border border-stone-200 bg-stone-100 flex flex-col items-center justify-center text-stone-400">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                            <span class="text-sm mt-2 font-medium">No logo uploaded</span>
                        </div>
                    </template>
                    <div>
                        <h3 class="text-xl font-bold text-stone-900" x-text="selectedBrand.title"></h3>
                        <p class="text-sm text-stone-500 mt-1" x-text="selectedBrand.slug"></p>
                    </div>
                    <p class="text-sm text-stone-600" x-show="selectedBrand.description" x-text="selectedBrand.description"></p>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between py-2 border-b border-stone-100">
                            <dt class="text-stone-500">Products</dt>
                            <dd class="font-medium text-stone-900" x-text="selectedBrand.products_count + ' ' + (selectedBrand.products_count === 1 ? 'product' : 'products')"></dd>
                        </div>
                        <div class="flex justify-between py-2 border-b border-stone-100">
                            <dt class="text-stone-500">Status</dt>
                            <dd>
                                <span :class="selectedBrand.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-600'"
                                      class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                      x-text="selectedBrand.is_active ? 'Active' : 'Inactive'"></span>
                            </dd>
                        </div>
                        <template x-if="selectedBrand.meta_keywords">
                            <div class="py-2 border-b border-stone-100">
                                <dt class="text-stone-500 mb-1">Meta Keywords</dt>
                                <dd class="text-stone-700" x-text="selectedBrand.meta_keywords"></dd>
                            </div>
                        </template>
                    </dl>
                </div>
            </template>
            <div class="border-t border-stone-200 p-5 shrink-0 bg-stone-50 space-y-3">
                <button type="button" @click="openEdit(selectedBrand); closeDrawer('detailDrawerOpen')"
                        class="w-full py-2.5 bg-souq-600 text-white rounded-xl text-sm font-semibold hover:bg-souq-700">Edit Brand</button>
                <form method="POST" :action="'/admin/brands/' + (selectedBrand?.id ?? '')"
                      onsubmit="return confirm('Delete this brand? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-2.5 border border-red-200 text-red-600 rounded-xl text-sm font-semibold hover:bg-red-50">Delete Brand</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
