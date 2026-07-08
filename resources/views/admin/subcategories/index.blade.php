@extends('layouts.admin')

@section('title', 'Sub Categories')
@section('page_title', 'Sub Categories')
@section('page_subtitle', 'Manage sub-categories under main categories')

@section('header_action')
<button type="button" @click="$dispatch('open-subcategory-drawer')" class="px-4 py-2 bg-souq-600 text-white text-sm font-semibold rounded-xl hover:bg-souq-700 transition flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
    Add Sub Category
</button>
@endsection

@section('content')
@php
    $filterParams = array_filter([
        'search' => ($search ?? '') !== '' ? $search : null,
        'status' => ($status ?? '') !== '' ? $status : null,
        'parent_id' => ($parentId ?? null) ?: null,
    ], fn ($value) => $value !== null && $value !== '');
    $filterQuery = $filterParams ? '?'.http_build_query($filterParams) : '';
    $shouldOpenForm = $errors->any() && old('_form');
    $isEditing = old('_form') === 'edit';
    $editFromOld = $isEditing ? [
        'id' => (int) old('_category_id'),
        'parent_id' => old('parent_id'),
        'title' => old('title'),
        'slug' => old('slug'),
        'description' => old('description'),
        'meta_keywords' => old('meta_keywords'),
        'is_active' => (bool) old('is_active'),
        'image' => null,
    ] : null;
    $grouped = $subcategories->groupBy('parent_id');
@endphp

<div x-data="{
    formDrawerOpen: {{ $shouldOpenForm ? 'true' : 'false' }},
    detailDrawerOpen: false,
    editing: {{ $isEditing ? 'true' : 'false' }},
    editingId: {{ $isEditing ? (int) old('_category_id') : 'null' }},
    selected: @js($editFromOld),
    editForm: { parent_id: '', title: '', slug: '', description: '', meta_keywords: '', is_active: true },

    resetEditForm(item = null) {
        this.editForm = {
            parent_id: item?.parent_id ?? '',
            title: item?.title ?? '',
            slug: item?.slug ?? '',
            description: item?.description ?? '',
            meta_keywords: item?.meta_keywords ?? '',
            is_active: item?.is_active ?? true,
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
        this.selected = null;
        this.detailDrawerOpen = false;
        this.openDrawer('formDrawerOpen');
    },

    openEdit(item) {
        this.editing = true;
        this.editingId = item.id;
        this.selected = item;
        this.resetEditForm(item);
        this.detailDrawerOpen = false;
        this.openDrawer('formDrawerOpen');
    },

    openDetail(item) {
        this.selected = item;
        this.formDrawerOpen = false;
        this.openDrawer('detailDrawerOpen');
    }
}" x-init="@if($isEditing) resetEditForm(selected) @endif" @open-subcategory-drawer.window="openCreate()">

    <form method="GET" action="{{ route('admin.subcategories') }}" class="bg-white rounded-2xl border border-stone-200 p-4 mb-6 flex flex-col sm:flex-row gap-3">
        <input type="search" name="search" value="{{ $search ?? '' }}" placeholder="Search sub-categories..."
               class="flex-1 px-4 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
        <select name="parent_id" class="px-4 py-2.5 border border-stone-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-souq-500">
            <option value="">All Main Categories</option>
            @foreach ($parentCategories as $parent)
            <option value="{{ $parent->id }}" @selected(($parentId ?? null) === $parent->id)>{{ $parent->title }}</option>
            @endforeach
        </select>
        <select name="status" class="px-4 py-2.5 border border-stone-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-souq-500">
            <option value="">Active</option>
            <option value="trashed" @selected(($status ?? '') === 'trashed')>Trash</option>
        </select>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2.5 bg-souq-600 text-white text-sm font-semibold rounded-xl hover:bg-souq-700 transition">Filter</button>
            @if ($filtersActive)
            <a href="{{ route('admin.subcategories') }}" class="px-4 py-2.5 border border-stone-200 text-sm font-medium rounded-xl hover:bg-stone-50 transition">Clear</a>
            @endif
        </div>
    </form>

    @if ($subcategories->isEmpty())
    <div class="bg-white rounded-2xl border border-stone-200 p-10 text-center">
        <p class="text-stone-500 mb-4">{{ $filtersActive ? 'No sub-categories match your filters.' : 'No sub-categories yet. Create sub-categories under a main category.' }}</p>
        @if (! $showingTrashed)
        <button type="button" @click="openCreate()" class="px-5 py-2.5 bg-souq-600 text-white text-sm font-semibold rounded-xl hover:bg-souq-700 transition">Add Sub Category</button>
        @endif
    </div>
    @else
    <div class="space-y-8">
        @foreach ($parentCategories as $parent)
            @php
                $items = $grouped->get($parent->id, collect());
            @endphp
            @if ($items->isNotEmpty())
            <section>
                <div class="flex items-center gap-3 mb-4">
                    <h3 class="text-sm font-bold text-stone-900 uppercase tracking-wide">{{ $parent->title }}</h3>
                    <span class="text-xs text-stone-400">{{ $items->count() }} sub-categor{{ $items->count() === 1 ? 'y' : 'ies' }}</span>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach ($items as $sub)
                    @php
                        $itemData = [
                            'id' => $sub->id,
                            'parent_id' => $sub->parent_id,
                            'parent_title' => $sub->parent?->title,
                            'title' => $sub->title,
                            'slug' => $sub->slug,
                            'description' => $sub->description,
                            'meta_keywords' => $sub->meta_keywords,
                            'is_active' => $sub->is_active,
                            'image' => $sub->image_public_url,
                            'store_url' => $sub->storeUrl(),
                        ];
                    @endphp
                    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden group hover:shadow-md transition cursor-pointer"
                         @click="openDetail(@js($itemData))">
                        <div class="aspect-video overflow-hidden bg-stone-100">
                            <img src="{{ $sub->image_public_url }}" alt="{{ $sub->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h4 class="font-bold text-stone-900">{{ $sub->title }}</h4>
                                    <p class="text-xs text-stone-400 mt-0.5">{{ $sub->slug }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold shrink-0 {{ $showingTrashed ? 'bg-red-100 text-red-800' : ($sub->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-600') }}">
                                    {{ $showingTrashed ? 'In Trash' : ($sub->is_active ? 'Active' : 'Inactive') }}
                                </span>
                            </div>
                            <div class="flex gap-2 mt-3" @click.stop>
                                @if ($showingTrashed)
                                <form method="POST" action="{{ route('admin.subcategories.restore', $sub->id) }}{{ $filterQuery }}" class="flex-1">
                                    @csrf
                                    @foreach ($filterParams as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endforeach
                                    <button type="submit" class="w-full py-2 text-xs font-semibold text-center bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">Restore</button>
                                </form>
                                <form method="POST" action="{{ route('admin.subcategories.force-destroy', $sub->id) }}{{ $filterQuery }}" class="flex-1">
                                    @csrf
                                    @method('DELETE')
                                    @foreach ($filterParams as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endforeach
                                    <button type="button" @click="$store.adminConfirm.ask({ title: 'Delete permanently?', message: 'This cannot be undone.', confirmLabel: 'Delete', cancelLabel: 'Cancel', tone: 'danger', form: $el.closest('form') })" class="w-full py-2 text-xs font-semibold text-center border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition">Delete</button>
                                </form>
                                @else
                                <a href="{{ $sub->storeUrl() }}" target="_blank" class="flex-1 py-2 text-xs font-semibold text-center border border-stone-200 rounded-lg hover:bg-stone-50 transition">View Store</a>
                                <button type="button" @click="openEdit(@js($itemData))" class="flex-1 py-2 text-xs font-semibold text-center bg-souq-600 text-white rounded-lg hover:bg-souq-700 transition">Edit</button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif
        @endforeach
    </div>
    @endif

    {{-- Drawer --}}
    <div x-show="formDrawerOpen" x-cloak class="fixed inset-0 z-[60]">
        <div x-show="formDrawerOpen" x-transition.opacity @click="closeDrawer('formDrawerOpen')" class="absolute inset-0 bg-black/40"></div>
        <div x-show="formDrawerOpen" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             class="absolute right-0 top-0 bottom-0 w-full max-w-lg bg-white shadow-2xl flex flex-col border-l border-stone-200">
            <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200 shrink-0">
                <h2 class="text-lg font-bold text-stone-900" x-text="editing ? 'Edit Sub Category' : 'Add Sub Category'"></h2>
                <button type="button" @click="closeDrawer('formDrawerOpen')" class="p-2 text-stone-500 hover:bg-stone-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form x-show="!editing" method="POST" action="{{ route('admin.subcategories.store') }}" enctype="multipart/form-data" class="flex-1 overflow-y-auto p-5 space-y-4">
                @csrf
                <input type="hidden" name="_form" value="create">
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Main Category <span class="text-red-500">*</span></label>
                    <select name="parent_id" required class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-souq-500">
                        <option value="">Select main category</option>
                        @foreach ($parentCategories as $parent)
                        <option value="{{ $parent->id }}" @selected(old('_form') === 'create' && (int) old('parent_id') === $parent->id)>{{ $parent->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('_form') === 'create' ? old('title') : '' }}" required class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Slug</label>
                    <input type="text" name="slug" value="{{ old('_form') === 'create' ? old('slug') : '' }}" placeholder="Auto-generated if empty" class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">{{ old('_form') === 'create' ? old('description') : '' }}</textarea>
                    <p class="text-[11px] text-stone-400 mt-1">SEO only — not shown on the storefront.</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Image <span class="text-red-500">*</span></label>
                    <input type="file" name="image" accept="image/*" required class="w-full text-sm text-stone-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-stone-100 file:font-semibold file:text-stone-900">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Meta Keywords</label>
                    <input type="text" name="meta_keywords" value="{{ old('_form') === 'create' ? old('meta_keywords') : '' }}" class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" class="rounded accent-souq-600" checked> Active
                </label>
                <div class="border-t border-stone-200 pt-4 flex gap-3">
                    <button type="button" @click="closeDrawer('formDrawerOpen')" class="flex-1 py-2.5 border border-stone-200 rounded-xl text-sm font-medium">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 bg-souq-600 text-white rounded-xl text-sm font-semibold hover:bg-souq-700">Save</button>
                </div>
            </form>

            <form x-show="editing" method="POST" :action="'/admin/sub-categories/' + editingId" enctype="multipart/form-data" class="flex-1 overflow-y-auto p-5 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form" value="edit">
                <input type="hidden" name="_category_id" :value="editingId">
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Main Category <span class="text-red-500">*</span></label>
                    <select name="parent_id" required x-model="editForm.parent_id" class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-souq-500">
                        @foreach ($parentCategories as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required x-model="editForm.title" class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Slug</label>
                    <input type="text" name="slug" x-model="editForm.slug" class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Description</label>
                    <textarea name="description" rows="3" x-model="editForm.description" class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500"></textarea>
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Replace Image</label>
                    <template x-if="selected?.image">
                        <img :src="selected.image" alt="" class="w-full h-32 object-cover rounded-xl border border-stone-200 mb-2">
                    </template>
                    <input type="file" name="image" accept="image/*" class="w-full text-sm text-stone-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-stone-100 file:font-semibold file:text-stone-900">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Meta Keywords</label>
                    <input type="text" name="meta_keywords" x-model="editForm.meta_keywords" class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" class="rounded accent-souq-600" x-model="editForm.is_active"> Active
                </label>
                <div class="border-t border-stone-200 pt-4 flex gap-3">
                    <button type="button" @click="closeDrawer('formDrawerOpen')" class="flex-1 py-2.5 border border-stone-200 rounded-xl text-sm font-medium">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 bg-souq-600 text-white rounded-xl text-sm font-semibold hover:bg-souq-700">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
