@extends('layouts.admin')

@section('title', 'Categories')
@section('page_title', 'Categories')
@section('page_subtitle', 'Main categories shown on the store homepage')

@section('header_action')
<button type="button" @click="$dispatch('open-category-drawer')" class="px-4 py-2 bg-souq-600 text-white text-sm font-semibold rounded-xl hover:bg-souq-700 transition flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
    Add Category
</button>
@endsection

@section('content')
@php
    $status = $status ?? '';
    $filtersActive = ($search ?? '') !== '' || $status !== '';
    $filterParams = array_filter([
        'search' => ($search ?? '') !== '' ? $search : null,
        'status' => $status !== '' ? $status : null,
    ], fn ($value) => $value !== null && $value !== '');
    $filterQuery = $filterParams ? '?'.http_build_query($filterParams) : '';
    $shouldOpenForm = $errors->any() && old('_form');
    $isEditing = old('_form') === 'edit';
    $editCategoryFromOld = $isEditing ? [
        'id' => (int) old('_category_id'),
        'title' => old('title'),
        'slug' => old('slug'),
        'description' => old('description'),
        'meta_keywords' => old('meta_keywords'),
        'is_active' => (bool) old('is_active'),
        'image' => null,
    ] : null;
@endphp

<div x-data="{
    formDrawerOpen: {{ $shouldOpenForm ? 'true' : 'false' }},
    detailDrawerOpen: false,
    editing: {{ $isEditing ? 'true' : 'false' }},
    editingId: {{ $isEditing ? (int) old('_category_id') : 'null' }},
    selectedCategory: @js($editCategoryFromOld),
    editForm: {
        title: '',
        slug: '',
        description: '',
        meta_keywords: '',
        is_active: true,
    },

    resetEditForm(category = null) {
        this.editForm = {
            title: category?.title ?? '',
            slug: category?.slug ?? '',
            description: category?.description ?? '',
            meta_keywords: category?.meta_keywords ?? '',
            is_active: category?.is_active ?? true,
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
        this.selectedCategory = null;
        this.detailDrawerOpen = false;
        this.openDrawer('formDrawerOpen');
    },

    openEdit(category) {
        this.editing = true;
        this.editingId = category.id;
        this.selectedCategory = category;
        this.resetEditForm(category);
        this.detailDrawerOpen = false;
        this.openDrawer('formDrawerOpen');
    },

    openDetail(category) {
        this.selectedCategory = category;
        this.formDrawerOpen = false;
        this.openDrawer('detailDrawerOpen');
    }
}" x-init="@if($isEditing) resetEditForm(selectedCategory) @endif" @open-category-drawer.window="openCreate()">

    <form method="GET" action="{{ route('admin.categories') }}" class="bg-white rounded-2xl border border-stone-200 p-4 mb-6 flex flex-col sm:flex-row gap-3">
        <input type="search" name="search" value="{{ $search ?? '' }}" placeholder="Search categories by title, slug, or description..."
               class="flex-1 px-4 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
        <select name="status" class="px-4 py-2.5 border border-stone-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-souq-500">
            <option value="">All Categories</option>
            <option value="trashed" @selected($status === 'trashed')>Trash</option>
        </select>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2.5 bg-souq-600 text-white text-sm font-semibold rounded-xl hover:bg-souq-700 transition">Filter</button>
            @if ($filtersActive)
            <a href="{{ route('admin.categories') }}" class="px-4 py-2.5 border border-stone-200 text-sm font-medium rounded-xl hover:bg-stone-50 transition">Clear</a>
            @endif
        </div>
    </form>

    @if ($categories->isEmpty())
    <div class="bg-white rounded-2xl border border-stone-200 p-10 text-center">
        <p class="text-stone-500 mb-4">{{ $filtersActive ? 'No categories match your filters.' : 'No categories yet. Create your first category to organize products.' }}</p>
        @if (! $showingTrashed)
        <button type="button" @click="openCreate()" class="px-5 py-2.5 bg-souq-600 text-white text-sm font-semibold rounded-xl hover:bg-souq-700 transition">
            Add Category
        </button>
        @endif
    </div>
    @else
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach ($categories as $category)
        @php
            $categoryData = [
                'id' => $category->id,
                'title' => $category->title,
                'slug' => $category->slug,
                'description' => $category->description,
                'meta_keywords' => $category->meta_keywords,
                'is_active' => $category->is_active,
                'image' => $category->image_public_url,
                'children_count' => $category->children_count,
                'store_url' => $category->storeUrl(),
            ];
        @endphp
        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden group hover:shadow-md transition cursor-pointer"
             @click="openDetail(@js($categoryData))">
            <div class="aspect-video overflow-hidden bg-stone-100">
                <img src="{{ $category->image_public_url }}" alt="{{ $category->title }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
            </div>
            <div class="p-4">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="font-bold text-stone-900">{{ $category->title }}</h3>
                        <p class="text-xs text-stone-400 mt-0.5">{{ $category->slug }}</p>
                        @if ($category->children_count > 0)
                        <p class="text-[11px] text-stone-500 mt-1">{{ $category->children_count }} sub-categor{{ $category->children_count === 1 ? 'y' : 'ies' }}</p>
                        @endif
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold shrink-0 {{ $showingTrashed ? 'bg-red-100 text-red-800' : ($category->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-600') }}">
                        {{ $showingTrashed ? 'In Trash' : ($category->is_active ? 'Active' : 'Inactive') }}
                    </span>
                </div>
                @if ($category->description)
                <p class="text-sm text-stone-500 mt-2 line-clamp-2">{{ $category->description }}</p>
                @endif
                <div class="flex gap-2 mt-3" @click.stop>
                    @if ($showingTrashed)
                    <form method="POST" action="{{ route('admin.categories.restore', $category->id) }}{{ $filterQuery }}" class="flex-1">
                        @csrf
                        @foreach ($filterParams as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <button type="submit" class="w-full py-2 text-xs font-semibold text-center bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">Restore</button>
                    </form>
                    <form method="POST" action="{{ route('admin.categories.force-destroy', $category->id) }}{{ $filterQuery }}" class="flex-1">
                        @csrf
                        @method('DELETE')
                        @foreach ($filterParams as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <button type="button"
                                @click="$store.adminConfirm.ask({
                                    title: 'Delete category permanently?',
                                    message: 'This will permanently remove {{ addslashes($category->title) }} and its image. This cannot be undone.',
                                    confirmLabel: 'Delete Permanently',
                                    cancelLabel: 'Cancel',
                                    tone: 'danger',
                                    form: $el.closest('form')
                                })"
                                class="w-full py-2 text-xs font-semibold text-center border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition">Delete</button>
                    </form>
                    @else
                    <a href="{{ $category->storeUrl() }}" target="_blank"
                       class="flex-1 py-2 text-xs font-semibold text-center border border-stone-200 rounded-lg hover:bg-stone-50 transition">View Store</a>
                    <button type="button" @click="openEdit(@js($categoryData))"
                            class="flex-1 py-2 text-xs font-semibold text-center bg-souq-600 text-white rounded-lg hover:bg-souq-700 transition">Edit</button>
                    @endif
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
                <h2 class="text-lg font-bold text-stone-900" x-text="editing ? 'Edit Category' : 'Add Category'"></h2>
                <button type="button" @click="closeDrawer('formDrawerOpen')" class="p-2 text-stone-500 hover:bg-stone-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Create form --}}
            <form x-show="!editing" method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data"
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
                    <p class="text-[11px] text-stone-400 mt-1">SEO only — not shown on the storefront category page.</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Image <span class="text-red-500">*</span></label>
                    <input type="file" name="image" accept="image/*" required
                           class="w-full text-sm text-stone-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-stone-100 file:font-semibold file:text-stone-900 hover:file:bg-stone-200">
                </div>

                <div class="pt-2 border-t border-stone-100">
                    <p class="text-xs font-bold text-stone-900 uppercase tracking-wide mb-3">SEO</p>
                    <div>
                        <label class="text-xs font-semibold text-stone-600 block mb-1">Meta Keywords <span class="text-stone-400 font-normal">(optional)</span></label>
                        <input type="text" name="meta_keywords" value="{{ old('_form') === 'create' ? old('meta_keywords') : '' }}"
                               placeholder="phone cases, mobile accessories"
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
                    <button type="submit" class="flex-1 py-2.5 bg-souq-600 text-white rounded-xl text-sm font-semibold hover:bg-souq-700">Save Category</button>
                </div>
            </form>

            {{-- Edit form --}}
            <form x-show="editing" method="POST" :action="'/admin/categories/' + editingId" enctype="multipart/form-data"
                  class="flex-1 overflow-y-auto p-5 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form" value="edit">
                <input type="hidden" name="_category_id" :value="editingId">

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
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Replace Image</label>
                    <template x-if="selectedCategory?.image">
                        <img :src="selectedCategory.image" alt="" class="w-full h-32 object-cover rounded-xl border border-stone-200 mb-2">
                    </template>
                    <input type="file" name="image" accept="image/*"
                           class="w-full text-sm text-stone-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-stone-100 file:font-semibold file:text-stone-900 hover:file:bg-stone-200">
                    <p class="text-[11px] text-stone-400 mt-1">Leave empty to keep the current image.</p>
                </div>

                <div class="pt-2 border-t border-stone-100">
                    <p class="text-xs font-bold text-stone-900 uppercase tracking-wide mb-3">SEO</p>
                    <div>
                        <label class="text-xs font-semibold text-stone-600 block mb-1">Meta Keywords <span class="text-stone-400 font-normal">(optional)</span></label>
                        <input type="text" name="meta_keywords" x-model="editForm.meta_keywords"
                               placeholder="phone cases, mobile accessories"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" class="rounded accent-souq-600" x-model="editForm.is_active">
                    Active
                </label>

                <div class="border-t border-stone-200 pt-4 flex gap-3">
                    <button type="button" @click="closeDrawer('formDrawerOpen')" class="flex-1 py-2.5 border border-stone-200 rounded-xl text-sm font-medium bg-white">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 bg-souq-600 text-white rounded-xl text-sm font-semibold hover:bg-souq-700">Update Category</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Category detail drawer --}}
    <div x-show="detailDrawerOpen" x-cloak @keydown.escape.window="detailDrawerOpen && closeDrawer('detailDrawerOpen')"
         class="fixed inset-0 z-[60]" role="dialog">
        <div x-show="detailDrawerOpen" x-transition.opacity @click="closeDrawer('detailDrawerOpen')" class="absolute inset-0 bg-black/40"></div>
        <div x-show="detailDrawerOpen"
             x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
             class="absolute right-0 top-0 bottom-0 w-full max-w-md bg-white shadow-2xl flex flex-col border-l border-stone-200">
            <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200 shrink-0">
                <h2 class="text-lg font-bold text-stone-900">Category Detail</h2>
                <button type="button" @click="closeDrawer('detailDrawerOpen')" class="p-2 text-stone-500 hover:bg-stone-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <template x-if="selectedCategory">
                <div class="flex-1 overflow-y-auto p-5 space-y-5">
                    <img :src="selectedCategory.image" :alt="selectedCategory.title" class="w-full aspect-video object-cover rounded-2xl border border-stone-200">
                    <div>
                        <h3 class="text-xl font-bold text-stone-900" x-text="selectedCategory.title"></h3>
                        <p class="text-sm text-stone-500 mt-1" x-text="selectedCategory.slug"></p>
                    </div>
                    <p class="text-sm text-stone-600" x-show="selectedCategory.description" x-text="selectedCategory.description"></p>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between py-2 border-b border-stone-100">
                            <dt class="text-stone-500">Status</dt>
                            <dd>
                                <span :class="selectedCategory.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-600'"
                                      class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                      x-text="selectedCategory.is_active ? 'Active' : 'Inactive'"></span>
                            </dd>
                        </div>
                        <template x-if="selectedCategory.meta_keywords">
                            <div class="py-2 border-b border-stone-100">
                                <dt class="text-stone-500 mb-1">Meta Keywords</dt>
                                <dd class="text-stone-700" x-text="selectedCategory.meta_keywords"></dd>
                            </div>
                        </template>
                    </dl>
                    <a :href="selectedCategory.store_url" target="_blank"
                       class="block text-center py-2.5 text-sm font-semibold text-souq-600 border border-souq-200 rounded-xl hover:bg-souq-50">View on Store →</a>
                </div>
            </template>
            <div class="border-t border-stone-200 p-5 shrink-0 bg-stone-50 space-y-3">
                @if ($showingTrashed)
                <form method="POST" :action="`/admin/categories/${selectedCategory?.id}/restore`">
                    @csrf
                    @foreach ($filterParams as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <button type="submit" class="w-full py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700">Restore Category</button>
                </form>
                <form method="POST" :action="`/admin/categories/${selectedCategory?.id}/force`">
                    @csrf
                    @method('DELETE')
                    @foreach ($filterParams as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <button type="button"
                            @click="$store.adminConfirm.ask({
                                title: 'Delete category permanently?',
                                message: 'This will permanently remove this category and its image. This cannot be undone.',
                                confirmLabel: 'Delete Permanently',
                                cancelLabel: 'Cancel',
                                tone: 'danger',
                                form: $el.closest('form')
                            })"
                            class="w-full py-2.5 border border-red-200 text-red-600 rounded-xl text-sm font-semibold hover:bg-red-50">Delete Permanently</button>
                </form>
                @else
                <button type="button" @click="openEdit(selectedCategory); closeDrawer('detailDrawerOpen')"
                        class="w-full py-2.5 bg-souq-600 text-white rounded-xl text-sm font-semibold hover:bg-souq-700">Edit Category</button>
                <form method="POST" :action="`/admin/categories/${selectedCategory?.id}`">
                    @csrf
                    @method('DELETE')
                    @foreach ($filterParams as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <button type="button"
                            @click="$store.adminConfirm.ask({
                                title: 'Move category to trash?',
                                message: 'This category will be hidden from the store. You can restore it later from the Trash filter. Categories with assigned products cannot be deleted.',
                                confirmLabel: 'Move to Trash',
                                cancelLabel: 'Cancel',
                                tone: 'danger',
                                form: $el.closest('form')
                            })"
                            class="w-full py-2.5 border border-red-200 text-red-600 rounded-xl text-sm font-semibold hover:bg-red-50">Move to Trash</button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
