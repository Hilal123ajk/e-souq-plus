@extends('layouts.admin')

@section('title', 'Products')
@section('page_title', 'Products')
@section('page_subtitle', 'Manage your product catalog')

@section('header_action')
<button type="button" @click="$dispatch('open-product-drawer')" class="px-4 py-2 bg-souq-600 text-white text-sm font-semibold rounded-xl hover:bg-souq-700 transition flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
    Add Product
</button>
@endsection

@section('content')
@php
    $filtersActive = $search !== '' || $categoryId || $brandId || $status !== '';
    $filterParams = array_filter([
        'search' => $search !== '' ? $search : null,
        'category_id' => $categoryId ?: null,
        'brand_id' => $brandId ?: null,
        'status' => $status !== '' ? $status : null,
    ], fn ($value) => $value !== null && $value !== '');
    $filterQuery = $filterParams ? '?'.http_build_query($filterParams) : '';
    $shouldOpenForm = $errors->any() && old('_form');
    $isEditing = old('_form') === 'edit';
    $editProductFromOld = $isEditing ? [
        'id' => (int) old('_product_id'),
        'name' => old('name'),
        'slug' => old('slug'),
        'description' => old('description'),
        'sku' => old('sku'),
        'category_id' => old('sub_category_id') ?: old('main_category_id'),
        'category_parent_id' => old('sub_category_id') ? old('main_category_id') : null,
        'brand_id' => old('brand_id'),
        'price' => old('price'),
        'cost_price' => old('cost_price'),
        'stock_quantity' => old('stock_quantity'),
        'meta_keywords' => old('meta_keywords'),
        'is_active' => (bool) old('is_active'),
        'is_featured' => (bool) old('is_featured'),
        'has_variants' => (bool) old('has_variants', true),
        'image' => null,
    ] : null;
@endphp

<div x-data="{
    formDrawerOpen: {{ $shouldOpenForm ? 'true' : 'false' }},
    detailDrawerOpen: false,
    menuOpenId: null,
    colorLabelCount: 0,
    newGalleryLabelCount: 0,
    hasVariants: {{ old('_form') === 'create' ? (old('has_variants', true) ? 'true' : 'false') : 'true' }},
    editing: {{ $isEditing ? 'true' : 'false' }},
    editingId: {{ $isEditing ? (int) old('_product_id') : 'null' }},
    selectedProduct: @js($editProductFromOld),
    subcategoriesByParent: @js($subcategoriesByParent),
    mainCategoryId: @js($oldMainCategoryId ?? ''),
    createSubCategoryId: @js($oldSubCategoryId ?? ''),
    editMainCategoryId: '',
    editForm: {
        name: '',
        slug: '',
        description: '',
        sku: '',
        sub_category_id: '',
        brand_id: '',
        price: '',
        cost_price: '',
        stock_quantity: '',
        meta_keywords: '',
        is_active: true,
        is_featured: false,
        has_variants: true,
    },

    subcategoriesFor(mainId) {
        return this.subcategoriesByParent[mainId] ?? this.subcategoriesByParent[String(mainId)] ?? [];
    },

    resetEditForm(product = null) {
        const isSubCategory = product?.category_parent_id != null && product?.category_parent_id !== '';

        this.editForm = {
            name: product?.name ?? '',
            slug: product?.slug ?? '',
            description: product?.description ?? '',
            sku: product?.sku ?? '',
            sub_category_id: isSubCategory ? String(product.category_id) : '',
            brand_id: product?.brand_id ?? '',
            price: product?.price ?? '',
            cost_price: product?.cost_price ?? '',
            stock_quantity: product?.stock_quantity ?? '',
            meta_keywords: product?.meta_keywords ?? '',
            is_active: product?.is_active ?? true,
            is_featured: product?.is_featured ?? false,
            has_variants: product?.has_variants ?? true,
        };
        this.editMainCategoryId = product
            ? String(isSubCategory ? product.category_parent_id : product.category_id)
            : '';
        this.hasVariants = product?.has_variants ?? true;
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

    toggleMenu(id) {
        this.menuOpenId = this.menuOpenId === id ? null : id;
    },

    openCreate() {
        this.editing = false;
        this.editingId = null;
        this.selectedProduct = null;
        this.mainCategoryId = '';
        this.createSubCategoryId = '';
        this.colorLabelCount = 0;
        this.newGalleryLabelCount = 0;
        this.hasVariants = true;
        this.detailDrawerOpen = false;
        this.menuOpenId = null;
        this.openDrawer('formDrawerOpen');
    },

    openEdit(product) {
        this.editing = true;
        this.editingId = product.id;
        this.selectedProduct = product;
        this.newGalleryLabelCount = 0;
        this.resetEditForm(product);
        this.detailDrawerOpen = false;
        this.menuOpenId = null;
        this.openDrawer('formDrawerOpen');
    },

    openDetail(product) {
        this.selectedProduct = product;
        this.formDrawerOpen = false;
        this.menuOpenId = null;
        this.openDrawer('detailDrawerOpen');
    },

    formatPrice(value) {
        const amount = Number(value ?? 0);
        return 'AED ' + amount.toLocaleString('en-AE', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }
}" x-init="@if($isEditing) resetEditForm(selectedProduct) @endif"
   @click.outside="menuOpenId = null"
   @open-product-drawer.window="openCreate()">

    <form method="GET" action="{{ route('admin.products') }}" class="bg-white rounded-2xl border border-stone-200 p-4 mb-6 flex flex-col lg:flex-row gap-3">
        <input type="search" name="search" value="{{ $search }}" placeholder="Search by name, SKU, slug..."
               class="flex-1 px-4 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
        <select name="category_id" class="px-4 py-2.5 border border-stone-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-souq-500">
            <option value="">All Categories</option>
            @foreach ($mainCategories as $main)
            <optgroup label="{{ $main->title }}">
                @foreach ($subcategories->where('parent_id', $main->id) as $sub)
                <option value="{{ $sub->id }}" @selected($categoryId === $sub->id)>{{ $sub->title }}</option>
                @endforeach
            </optgroup>
            @endforeach
        </select>
        <select name="brand_id" class="px-4 py-2.5 border border-stone-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-souq-500">
            <option value="">All Brands</option>
            @foreach ($brands as $brand)
            <option value="{{ $brand->id }}" @selected($brandId === $brand->id)>{{ $brand->title }}</option>
            @endforeach
        </select>
        <select name="status" class="px-4 py-2.5 border border-stone-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-souq-500">
            <option value="">All Status</option>
            <option value="active" @selected($status === 'active')>Active</option>
            <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            <option value="low" @selected($status === 'low')>Low Stock</option>
            <option value="trashed" @selected($status === 'trashed')>Trash</option>
        </select>
        <div class="flex gap-2 shrink-0">
            <button type="submit" class="px-4 py-2.5 bg-souq-600 text-white text-sm font-semibold rounded-xl hover:bg-souq-700 transition">Filter</button>
            @if ($filtersActive)
            <a href="{{ route('admin.products') }}" class="px-4 py-2.5 border border-stone-200 text-sm font-medium rounded-xl hover:bg-stone-50 transition">Clear</a>
            @endif
        </div>
    </form>

    @if ($products->isEmpty())
    <div class="bg-white rounded-2xl border border-stone-200 p-10 text-center">
        <p class="text-stone-500 mb-4">{{ $filtersActive ? 'No products match your filters.' : 'No products yet. Add your first product to get started.' }}</p>
        <button type="button" @click="openCreate()" class="px-5 py-2.5 bg-souq-600 text-white text-sm font-semibold rounded-xl hover:bg-souq-700 transition">
            Add Product
        </button>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-xs text-stone-500 uppercase">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold">Product</th>
                        <th class="text-left px-5 py-3 font-semibold hidden md:table-cell">SKU</th>
                        <th class="text-left px-5 py-3 font-semibold hidden lg:table-cell">Category</th>
                        <th class="text-left px-5 py-3 font-semibold hidden lg:table-cell">Brand</th>
                        <th class="text-left px-5 py-3 font-semibold">Price</th>
                        <th class="text-left px-5 py-3 font-semibold hidden sm:table-cell">Stock</th>
                        <th class="text-left px-5 py-3 font-semibold">Status</th>
                        <th class="text-right px-5 py-3 font-semibold w-12"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($products as $product)
                    @php
                        $productData = [
                            'id' => $product->id,
                            'name' => $product->name,
                            'slug' => $product->slug,
                            'description' => $product->description,
                            'sku' => $product->sku,
                            'category_id' => $product->category_id,
                            'category_parent_id' => $product->category?->parent_id,
                            'category_title' => $product->category
                                ? ($product->category->parent
                                    ? $product->category->parent->title.' › '.$product->category->title
                                    : $product->category->title)
                                : null,
                            'brand_id' => $product->brand_id,
                            'brand_title' => $product->brand?->title,
                            'price' => $product->price,
                            'cost_price' => $product->cost_price,
                            'stock_quantity' => $product->stock_quantity,
                            'meta_keywords' => $product->meta_keywords,
                            'is_active' => $product->is_active,
                            'is_featured' => $product->is_featured,
                            'has_variants' => $product->has_variants,
                            'image' => $product->image_public_url,
                            'gallery' => $product->images->map(fn ($img) => [
                                'id' => $img->id,
                                'url' => $img->image_public_url,
                                'label' => $img->label,
                            ])->values()->all(),
                        ];
                    @endphp
                    <tr class="hover:bg-stone-50">
                        <td class="px-5 py-3">
                            <button type="button" @click="openDetail(@js($productData))" class="flex items-center gap-3 text-left group">
                                @if ($product->image_public_url)
                                <img src="{{ $product->image_public_url }}" alt="{{ $product->name }}" class="w-10 h-10 rounded-lg object-cover bg-stone-100 shrink-0">
                                @else
                                <div class="w-10 h-10 rounded-lg bg-stone-100 shrink-0 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-semibold text-stone-900 truncate max-w-[180px] group-hover:text-souq-600">{{ $product->name }}</p>
                                    @if ($product->is_featured)
                                    <p class="text-[10px] text-souq-600 font-semibold uppercase">Featured</p>
                                    @endif
                                </div>
                            </button>
                        </td>
                        <td class="px-5 py-3 hidden md:table-cell text-stone-600 font-mono text-xs">{{ $product->sku }}</td>
                        <td class="px-5 py-3 hidden lg:table-cell text-stone-600">{{ $product->category?->title ?? '—' }}</td>
                        <td class="px-5 py-3 hidden lg:table-cell text-stone-600">{{ $product->brand?->title ?? '—' }}</td>
                        <td class="px-5 py-3 font-semibold">AED {{ number_format((float) $product->price, 0) }}</td>
                        <td class="px-5 py-3 hidden sm:table-cell">
                            <span class="{{ $product->stock_quantity <= 10 ? 'text-red-600 font-bold' : 'text-stone-600' }}">{{ $product->stock_quantity }}</span>
                        </td>
                        <td class="px-5 py-3">
                            @if ($showingTrashed)
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                In Trash
                            </span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $product->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-600' }}">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right relative">
                            <button type="button" @click.stop="toggleMenu({{ $product->id }})" class="p-2 text-stone-500 hover:text-stone-900 hover:bg-stone-100 rounded-lg transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>
                            </button>
                            <div x-show="menuOpenId === {{ $product->id }}" x-cloak @click.stop
                                 class="absolute right-5 top-full mt-1 w-44 bg-white border border-stone-200 rounded-xl shadow-lg py-1 z-20 text-left">
                                <button type="button" @click="openDetail(@js($productData))" class="w-full px-4 py-2.5 text-sm text-stone-700 hover:bg-stone-50 text-left">View Detail</button>
                                @if ($showingTrashed)
                                <form method="POST" action="{{ route('admin.products.restore', $product->id) }}{{ $filterQuery }}">
                                    @csrf
                                    @foreach ($filterParams as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endforeach
                                    <button type="submit" class="w-full px-4 py-2.5 text-sm text-emerald-700 hover:bg-emerald-50 text-left">Restore</button>
                                </form>
                                <form method="POST" action="{{ route('admin.products.force-destroy', $product->id) }}{{ $filterQuery }}">
                                    @csrf
                                    @method('DELETE')
                                    @foreach ($filterParams as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endforeach
                                    <button type="button"
                                            @click="$store.adminConfirm.ask({
                                                title: 'Delete permanently?',
                                                message: 'This will permanently remove {{ addslashes($product->name) }} and all of its images. This cannot be undone.',
                                                confirmLabel: 'Delete Permanently',
                                                cancelLabel: 'Cancel',
                                                tone: 'danger',
                                                form: $el.closest('form')
                                            })"
                                            class="w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 text-left">Delete Permanently</button>
                                </form>
                                @else
                                <button type="button" @click="openEdit(@js($productData))" class="w-full px-4 py-2.5 text-sm text-stone-700 hover:bg-stone-50 text-left">Edit Product</button>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}{{ $filterQuery }}">
                                    @csrf
                                    @method('DELETE')
                                    @foreach ($filterParams as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endforeach
                                    <button type="button"
                                            @click="$store.adminConfirm.ask({
                                                title: 'Move product to trash?',
                                                message: '{{ addslashes($product->name) }} will be hidden from the store. You can restore it later from the Trash filter.',
                                                confirmLabel: 'Move to Trash',
                                                cancelLabel: 'Cancel',
                                                tone: 'danger',
                                                form: $el.closest('form')
                                            })"
                                            class="w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 text-left">Move to Trash</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-stone-100 text-xs text-stone-500">
            Showing {{ $products->count() }} {{ $products->count() === 1 ? 'product' : 'products' }}
        </div>
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
                <h2 class="text-lg font-bold text-stone-900" x-text="editing ? 'Edit Product' : 'Add Product'"></h2>
                <button type="button" @click="closeDrawer('formDrawerOpen')" class="p-2 text-stone-500 hover:bg-stone-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Create form --}}
            <form x-show="!editing" method="POST" action="{{ route('admin.products.store') }}{{ $filterQuery }}" enctype="multipart/form-data"
                  class="flex-1 overflow-y-auto p-5 space-y-4">
                @csrf
                <input type="hidden" name="_form" value="create">
                @foreach ($filterParams as $key => $value)
                @if (! in_array($key, ['main_category_id', 'sub_category_id', 'brand_id'], true))
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
                @endforeach

                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('_form') === 'create' ? old('name') : '' }}" required
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
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-stone-600 block mb-1">SKU <span class="text-red-500">*</span></label>
                        <input type="text" name="sku" value="{{ old('_form') === 'create' ? old('sku') : '' }}" required
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-souq-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-stone-600 block mb-1">Brand</label>
                        <select name="brand_id" class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-souq-500">
                            <option value="">No brand</option>
                            @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" @selected(old('_form') === 'create' && (int) old('brand_id') === $brand->id)>{{ $brand->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-stone-600 block mb-1">Main Category <span class="text-red-500">*</span></label>
                        <select name="main_category_id" x-model="mainCategoryId" @change="createSubCategoryId = ''" required
                                class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-souq-500">
                            <option value="">Select main category</option>
                            @foreach ($mainCategories as $main)
                            <option value="{{ $main->id }}" @selected(old('_form') === 'create' && (int) old('main_category_id') === $main->id)>{{ $main->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-stone-600 block mb-1">Sub Category</label>
                        <select name="sub_category_id" x-model="createSubCategoryId"
                                :disabled="!mainCategoryId"
                                class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-souq-500 disabled:bg-stone-100 disabled:text-stone-400">
                            <option value="">None (main category only)</option>
                            <template x-for="sub in subcategoriesFor(mainCategoryId)" :key="sub.id">
                                <option :value="sub.id" x-text="sub.title"></option>
                            </template>
                        </select>
                        <p class="text-[11px] text-stone-400 mt-1">Optional. Leave empty to assign the product directly to the main category.</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-stone-600 block mb-1">Price (AED) <span class="text-red-500">*</span></label>
                        <input type="number" name="price" value="{{ old('_form') === 'create' ? old('price') : '' }}" min="0" step="0.01" required
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-stone-600 block mb-1">Cost Price</label>
                        <input type="number" name="cost_price" value="{{ old('_form') === 'create' ? old('cost_price') : '' }}" min="0" step="0.01"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Stock Quantity <span class="text-red-500">*</span></label>
                    <input type="number" name="stock_quantity" value="{{ old('_form') === 'create' ? old('stock_quantity', 0) : 0 }}" min="0" required
                           class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Hero Image <span class="text-red-500">*</span></label>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" required
                           class="w-full text-sm text-stone-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-stone-100 file:font-semibold file:text-stone-900 hover:file:bg-stone-200">
                    <p class="text-[11px] text-stone-400 mt-1">Main image for listings and product page. JPG, PNG, or WebP up to 5MB.</p>
                </div>

                <div class="p-4 rounded-xl border border-stone-200 bg-stone-50 space-y-3">
                    <label class="flex items-start gap-3 text-sm cursor-pointer">
                        <input type="hidden" name="has_variants" value="0">
                        <input type="checkbox" name="has_variants" value="1" x-model="hasVariants"
                               class="rounded accent-souq-600 mt-0.5" {{ old('_form') === 'create' ? (old('has_variants', true) ? 'checked' : '') : 'checked' }}>
                        <span>
                            <span class="font-semibold text-stone-900 block">This product has color/variant options</span>
                            <span class="text-xs text-stone-500">Turn off for furniture and items with gallery-only photos (different angles, detail shots).</span>
                        </span>
                    </label>
                </div>

                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">
                        <span x-show="hasVariants">Color Images</span>
                        <span x-show="!hasVariants">Gallery Images</span>
                        <span class="text-stone-400 font-normal">(optional)</span>
                    </label>
                    <input type="file" name="gallery_images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple
                           @change="
                               const files = $event.target.files;
                               colorLabelCount = files ? files.length : 0;
                           "
                           class="w-full text-sm text-stone-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-stone-100 file:font-semibold file:text-stone-900 hover:file:bg-stone-200">
                    <p class="text-[11px] text-stone-400 mt-1" x-show="hasVariants">Each image is a color option on the store. Add a color name for every image.</p>
                    <p class="text-[11px] text-stone-400 mt-1" x-show="!hasVariants">Add different angles or detail shots. Labels are optional (e.g. Front view, Side angle).</p>
                    <div class="mt-3 space-y-2" x-show="colorLabelCount > 0">
                        <template x-for="index in colorLabelCount" :key="'color-label-' + index">
                            <div>
                                <label class="text-[11px] font-semibold text-stone-500 block mb-1">
                                    <span x-show="hasVariants" x-text="'Color name for image ' + index"></span>
                                    <span x-show="!hasVariants" x-text="'Caption for image ' + index + ' (optional)'"></span>
                                </label>
                                <input type="text" :name="'gallery_labels[' + (index - 1) + ']'" :required="hasVariants"
                                       :placeholder="hasVariants ? 'e.g. Matte Black, Clear, Navy Blue' : 'e.g. Front view, Side angle, Detail'"
                                       class="w-full px-3 py-2 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                            </div>
                        </template>
                    </div>
                </div>

                <div class="pt-2 border-t border-stone-100">
                    <p class="text-xs font-bold text-stone-900 uppercase tracking-wide mb-3">SEO</p>
                    <div>
                        <label class="text-xs font-semibold text-stone-600 block mb-1">Meta Keywords <span class="text-stone-400 font-normal">(optional)</span></label>
                        <input type="text" name="meta_keywords" value="{{ old('_form') === 'create' ? old('meta_keywords') : '' }}"
                               placeholder="phone case, iPhone 15, clear case"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" class="rounded accent-souq-600" {{ old('_form') === 'create' ? (old('is_active', true) ? 'checked' : '') : 'checked' }}>
                        Active
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_featured" value="1" class="rounded accent-souq-600" {{ old('_form') === 'create' && old('is_featured') ? 'checked' : '' }}>
                        Featured
                    </label>
                </div>

                <div class="border-t border-stone-200 pt-4 flex gap-3">
                    <button type="button" @click="closeDrawer('formDrawerOpen')" class="flex-1 py-2.5 border border-stone-200 rounded-xl text-sm font-medium bg-white hover:bg-stone-50">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 bg-souq-600 text-white rounded-xl text-sm font-semibold hover:bg-souq-700">Save Product</button>
                </div>
            </form>

            {{-- Edit form --}}
            <form x-show="editing" method="POST" :action="'/admin/products/' + editingId + '{{ $filterQuery }}'" enctype="multipart/form-data"
                  class="flex-1 overflow-y-auto p-5 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form" value="edit">
                <input type="hidden" name="_product_id" :value="editingId">
                @foreach ($filterParams as $key => $value)
                @if (! in_array($key, ['main_category_id', 'sub_category_id', 'brand_id'], true))
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
                @endforeach

                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required x-model="editForm.name"
                           class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Slug</label>
                    <input type="text" name="slug" x-model="editForm.slug" placeholder="Auto-generated if empty"
                           class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Description</label>
                    <textarea name="description" rows="3" x-model="editForm.description"
                              class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-stone-600 block mb-1">SKU <span class="text-red-500">*</span></label>
                        <input type="text" name="sku" required x-model="editForm.sku"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-souq-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-stone-600 block mb-1">Brand</label>
                        <select name="brand_id" x-model="editForm.brand_id"
                                class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-souq-500">
                            <option value="">No brand</option>
                            @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-stone-600 block mb-1">Main Category <span class="text-red-500">*</span></label>
                        <select name="main_category_id" x-model="editMainCategoryId" @change="editForm.sub_category_id = ''" required
                                class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-souq-500">
                            <option value="">Select main category</option>
                            @foreach ($mainCategories as $main)
                            <option value="{{ $main->id }}">{{ $main->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-stone-600 block mb-1">Sub Category</label>
                        <select name="sub_category_id" x-model="editForm.sub_category_id"
                                :disabled="!editMainCategoryId"
                                class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-souq-500 disabled:bg-stone-100 disabled:text-stone-400">
                            <option value="">None (main category only)</option>
                            <template x-for="sub in subcategoriesFor(editMainCategoryId)" :key="sub.id">
                                <option :value="sub.id" x-text="sub.title"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-stone-600 block mb-1">Price (AED) <span class="text-red-500">*</span></label>
                        <input type="number" name="price" min="0" step="0.01" required x-model="editForm.price"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-stone-600 block mb-1">Cost Price</label>
                        <input type="number" name="cost_price" min="0" step="0.01" x-model="editForm.cost_price"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Stock Quantity <span class="text-red-500">*</span></label>
                    <input type="number" name="stock_quantity" min="0" required x-model="editForm.stock_quantity"
                           class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">Replace Hero Image</label>
                    <template x-if="selectedProduct?.image">
                        <img :src="selectedProduct.image" :alt="editForm.name" class="w-full h-32 object-cover rounded-xl border border-stone-200 mb-2">
                    </template>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"
                           class="w-full text-sm text-stone-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-stone-100 file:font-semibold file:text-stone-900 hover:file:bg-stone-200">
                    <p class="text-[11px] text-stone-400 mt-1">Leave empty to keep the current hero image.</p>
                </div>

                <div class="p-4 rounded-xl border border-stone-200 bg-stone-50 space-y-3">
                    <label class="flex items-start gap-3 text-sm cursor-pointer">
                        <input type="hidden" name="has_variants" value="0">
                        <input type="checkbox" name="has_variants" value="1" x-model="editForm.has_variants"
                               @change="hasVariants = editForm.has_variants"
                               class="rounded accent-souq-600 mt-0.5">
                        <span>
                            <span class="font-semibold text-stone-900 block">This product has color/variant options</span>
                            <span class="text-xs text-stone-500">Turn off for gallery-only products (furniture, etc.).</span>
                        </span>
                    </label>
                </div>

                <template x-if="selectedProduct?.gallery?.length">
                    <div class="space-y-3">
                        <p class="text-xs font-bold text-stone-900 uppercase tracking-wide" x-text="editForm.has_variants ? 'Color Images' : 'Gallery Images'"></p>
                        <template x-for="item in selectedProduct.gallery" :key="item.id">
                            <div class="flex gap-3 items-start p-3 border border-stone-200 rounded-xl bg-stone-50">
                                <img :src="item.url" :alt="item.label || 'Image'" class="w-16 h-16 rounded-lg object-cover shrink-0 border border-stone-200">
                                <div class="flex-1 min-w-0 space-y-2">
                                    <input type="text" :name="'gallery_labels[' + item.id + ']'" :value="item.label || ''"
                                           :placeholder="editForm.has_variants ? 'Color name e.g. Matte Black' : 'Caption e.g. Front view (optional)'"
                                           class="w-full px-3 py-2 border border-stone-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-souq-500">
                                    <label class="flex items-center gap-2 text-xs text-red-600">
                                        <input type="checkbox" name="remove_gallery_ids[]" :value="item.id" class="rounded accent-red-500">
                                        Remove this image
                                    </label>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <div>
                    <label class="text-xs font-semibold text-stone-600 block mb-1">
                        <span x-show="editForm.has_variants">Add Color Images</span>
                        <span x-show="!editForm.has_variants">Add Gallery Images</span>
                    </label>
                    <input type="file" name="gallery_images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple
                           @change="newGalleryLabelCount = $event.target.files ? $event.target.files.length : 0"
                           class="w-full text-sm text-stone-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-stone-100 file:font-semibold file:text-stone-900 hover:file:bg-stone-200">
                    <p class="text-[11px] text-stone-400 mt-1" x-show="editForm.has_variants">Upload one image per color. Set the color name above for existing images.</p>
                    <p class="text-[11px] text-stone-400 mt-1" x-show="!editForm.has_variants">Upload angle or detail shots. Captions are optional.</p>
                    <div class="mt-3 space-y-2" x-show="newGalleryLabelCount > 0">
                        <template x-for="index in newGalleryLabelCount" :key="'new-gallery-label-' + index">
                            <div>
                                <label class="text-[11px] font-semibold text-stone-500 block mb-1">
                                    <span x-show="editForm.has_variants" x-text="'Color name for new image ' + index"></span>
                                    <span x-show="!editForm.has_variants" x-text="'Caption for new image ' + index + ' (optional)'"></span>
                                </label>
                                <input type="text" :name="'gallery_labels[' + (index - 1) + ']'" :required="editForm.has_variants"
                                       :placeholder="editForm.has_variants ? 'e.g. Matte Black' : 'e.g. Side angle'"
                                       class="w-full px-3 py-2 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                            </div>
                        </template>
                    </div>
                </div>

                <div class="pt-2 border-t border-stone-100">
                    <p class="text-xs font-bold text-stone-900 uppercase tracking-wide mb-3">SEO</p>
                    <div>
                        <label class="text-xs font-semibold text-stone-600 block mb-1">Meta Keywords <span class="text-stone-400 font-normal">(optional)</span></label>
                        <input type="text" name="meta_keywords" x-model="editForm.meta_keywords"
                               placeholder="phone case, iPhone 15, clear case"
                               class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" class="rounded accent-souq-600" x-model="editForm.is_active">
                        Active
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_featured" value="1" class="rounded accent-souq-600" x-model="editForm.is_featured">
                        Featured
                    </label>
                </div>

                <div class="border-t border-stone-200 pt-4 flex gap-3">
                    <button type="button" @click="closeDrawer('formDrawerOpen')" class="flex-1 py-2.5 border border-stone-200 rounded-xl text-sm font-medium bg-white hover:bg-stone-50">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 bg-souq-600 text-white rounded-xl text-sm font-semibold hover:bg-souq-700">Update Product</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Product detail drawer --}}
    <div x-show="detailDrawerOpen" x-cloak @keydown.escape.window="detailDrawerOpen && closeDrawer('detailDrawerOpen')"
         class="fixed inset-0 z-[60]" role="dialog">
        <div x-show="detailDrawerOpen" x-transition.opacity @click="closeDrawer('detailDrawerOpen')" class="absolute inset-0 bg-black/40"></div>
        <div x-show="detailDrawerOpen"
             x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
             class="absolute right-0 top-0 bottom-0 w-full max-w-md bg-white shadow-2xl flex flex-col border-l border-stone-200">
            <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200 shrink-0">
                <h2 class="text-lg font-bold text-stone-900">Product Detail</h2>
                <button type="button" @click="closeDrawer('detailDrawerOpen')" class="p-2 text-stone-500 hover:bg-stone-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <template x-if="selectedProduct">
                <div class="flex-1 overflow-y-auto p-5 space-y-5">
                    <template x-if="selectedProduct.image">
                        <img :src="selectedProduct.image" :alt="selectedProduct.name" class="w-full aspect-square object-cover rounded-2xl border border-stone-200 bg-stone-50">
                    </template>
                    <template x-if="selectedProduct.gallery?.length">
                        <div>
                            <p class="text-xs font-semibold text-stone-500 mb-2" x-text="selectedProduct.has_variants ? 'Color options' : 'Gallery'"></p>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="item in selectedProduct.gallery" :key="item.id">
                                    <div class="text-center">
                                        <img :src="item.url" :alt="item.label || 'Image'" class="w-14 h-14 rounded-lg object-cover border border-stone-200">
                                        <p class="text-[10px] text-stone-500 mt-1 max-w-[56px] truncate" x-text="item.label || (selectedProduct.has_variants ? 'Color' : 'View')"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    <template x-if="!selectedProduct.image">
                        <div class="w-full aspect-square rounded-2xl border border-stone-200 bg-stone-50 flex items-center justify-center">
                            <svg class="w-16 h-16 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    </template>
                    <div>
                        <p class="text-xs text-stone-500 uppercase" x-text="selectedProduct.brand_title || 'No brand'"></p>
                        <h3 class="text-xl font-bold text-stone-900 mt-1" x-text="selectedProduct.name"></h3>
                        <p class="text-2xl font-extrabold text-stone-900 mt-2" x-text="formatPrice(selectedProduct.price)"></p>
                    </div>
                    <p class="text-sm text-stone-600" x-show="selectedProduct.description" x-text="selectedProduct.description"></p>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-xs text-stone-500">SKU</dt>
                            <dd class="font-medium font-mono" x-text="selectedProduct.sku"></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-stone-500">Category</dt>
                            <dd class="font-medium" x-text="selectedProduct.category_title || '—'"></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-stone-500">Brand</dt>
                            <dd class="font-medium" x-text="selectedProduct.brand_title || '—'"></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-stone-500">Stock</dt>
                            <dd class="font-medium" x-text="selectedProduct.stock_quantity"></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-stone-500">Cost Price</dt>
                            <dd class="font-medium" x-text="formatPrice(selectedProduct.cost_price)"></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-stone-500">Status</dt>
                            <dd>
                                <span :class="selectedProduct.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-600'"
                                      class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                      x-text="selectedProduct.is_active ? 'Active' : 'Inactive'"></span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-stone-500">Featured</dt>
                            <dd class="font-medium" x-text="selectedProduct.is_featured ? 'Yes' : 'No'"></dd>
                        </div>
                        <template x-if="selectedProduct.meta_keywords">
                            <div class="col-span-2">
                                <dt class="text-xs text-stone-500 mb-1">Meta Keywords</dt>
                                <dd class="text-stone-700" x-text="selectedProduct.meta_keywords"></dd>
                            </div>
                        </template>
                    </dl>
                    <a :href="'/products/' + selectedProduct.slug" target="_blank"
                       class="block text-center py-2.5 text-sm font-semibold text-souq-600 border border-souq-200 rounded-xl hover:bg-souq-50">View on Store →</a>
                </div>
            </template>
            <div class="border-t border-stone-200 p-5 shrink-0 bg-stone-50">
                <button type="button" @click="openEdit(selectedProduct); closeDrawer('detailDrawerOpen')"
                        class="w-full py-2.5 bg-souq-600 text-white rounded-xl text-sm font-semibold hover:bg-souq-700">Edit Product</button>
            </div>
        </div>
    </div>
</div>
@endsection
