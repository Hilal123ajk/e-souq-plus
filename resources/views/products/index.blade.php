@extends('layouts.app')

@section('title', 'All Products — E-Souq Plus')

@section('content')
<section x-data="productFilters()" x-init="
    category = '{{ request('category', '') }}';
    brand = '{{ request('brand', '') }}';
    search = '{{ request('q', '') }}';
    sort = '{{ request('sort', 'featured') }}';
">
    <div class="bg-white border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 py-6 md:py-8">
            <nav class="text-xs text-stone-400 mb-2 flex items-center gap-2">
                <a href="{{ url('/') }}" class="hover:text-souq-600">Home</a><span>/</span><span class="text-stone-600">All Products</span>
            </nav>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-stone-900">All Products</h1>
                    <p class="text-sm text-stone-500 mt-1"><span x-text="filteredProducts.length"></span> products found</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="mobileFiltersOpen = true" class="lg:hidden flex items-center gap-2 px-4 py-2 border border-stone-300 rounded-full text-sm font-medium hover:bg-stone-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Filters
                    </button>
                    <select x-model="sort" class="px-4 py-2 border border-stone-300 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                        <option value="featured">Featured</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                        <option value="rating">Top Rated</option>
                        <option value="discount">Best Discount</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-6 md:py-8">
        <div class="flex gap-8">
            <aside class="hidden lg:block w-64 shrink-0">
                <div class="bg-white rounded-2xl border border-stone-200 p-5 sticky top-36 space-y-5 shadow-sm">
                    <div>
                        <label class="text-sm font-bold text-stone-800 block mb-2">Search</label>
                        <input type="search" x-model="search" placeholder="Search products..." class="w-full px-3 py-2 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                    </div>
                    <div>
                        <label class="text-sm font-bold text-stone-800 block mb-2">Category</label>
                        <select x-model="category" class="w-full px-3 py-2 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                            <option value="">All Categories</option>
                            <template x-for="cat in ESOUQ_STORE.categories" :key="cat.slug">
                                <option :value="cat.slug" x-text="cat.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-stone-800 block mb-2">Brand</label>
                        <select x-model="brand" class="w-full px-3 py-2 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                            <option value="">All Brands</option>
                            <template x-for="b in ESOUQ_STORE.brands" :key="b">
                                <option :value="b" x-text="b"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-stone-800 block mb-2">Price Range</label>
                        <input type="range" x-model.number="minPrice" min="0" max="150000" step="500" class="w-full accent-souq-600">
                        <input type="range" x-model.number="maxPrice" min="0" max="150000" step="500" class="w-full accent-souq-600 mt-2">
                        <div class="flex justify-between text-xs text-stone-500 mt-1">
                            <span x-text="ESOUQ_STORE.formatPrice(minPrice)"></span>
                            <span x-text="ESOUQ_STORE.formatPrice(maxPrice)"></span>
                        </div>
                    </div>
                    <button @click="resetFilters()" class="w-full py-2 text-sm text-stone-600 border border-stone-200 rounded-xl hover:bg-stone-50 transition">Clear Filters</button>
                </div>
            </aside>

            <div class="flex-1 min-w-0">
                <div x-show="filteredProducts.length === 0" x-cloak class="text-center py-16">
                    <div class="w-16 h-16 mx-auto mb-4 bg-stone-100 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <p class="text-stone-600 font-medium" x-text="emptyMessage"></p>
                    <button @click="resetFilters()" class="mt-4 text-sm text-souq-600 font-semibold hover:underline">Clear all filters</button>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden group hover:shadow-lg transition flex flex-col">
                            <a :href="'/products/' + product.slug" class="relative aspect-square overflow-hidden bg-stone-50 block">
                                <img :src="product.image" :alt="product.name" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                <span x-show="!product.inStock" class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                    <span class="px-3 py-1 bg-white text-stone-800 text-xs font-bold rounded-full">Out of Stock</span>
                                </span>
                            </a>
                            <div class="p-3 md:p-4 flex flex-col flex-1">
                                <p class="text-xs text-stone-400 uppercase" x-text="product.brand"></p>
                                <a :href="'/products/' + product.slug" class="text-sm font-semibold text-stone-800 line-clamp-2 hover:text-souq-700" x-text="product.name"></a>
                                <div class="mt-auto pt-3 flex items-end justify-between gap-2">
                                    <div>
                                        <p class="text-base font-bold text-souq-700" x-text="ESOUQ_STORE.formatPrice(product.price)"></p>
                                        <p x-show="product.originalPrice > product.price" class="text-xs text-stone-400 line-through" x-text="ESOUQ_STORE.formatPrice(product.originalPrice)"></p>
                                    </div>
                                    <button @click="$store.cart.add(product)" :disabled="!product.inStock" class="inline-flex items-center p-2.5 md:px-3 md:py-2 bg-souq-600 hover:bg-souq-700 disabled:bg-stone-300 text-white rounded-xl transition shrink-0" aria-label="Add to cart">
                                        <x-cart-icon class="w-4 h-4" />
                                        <span class="hidden md:inline text-xs font-semibold ml-1">Add</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile filters --}}
    <div x-show="mobileFiltersOpen" x-cloak class="lg:hidden fixed inset-0 z-50">
        <div @click="mobileFiltersOpen = false" class="absolute inset-0 bg-stone-900/50"></div>
        <div class="absolute right-0 top-0 bottom-0 w-80 max-w-[85vw] bg-white shadow-2xl overflow-y-auto p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-stone-900">Filters</h3>
                <button @click="mobileFiltersOpen = false" class="p-2 text-stone-400"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <input type="search" x-model="search" placeholder="Search..." class="w-full px-3 py-2 border rounded-xl text-sm">
            <select x-model="category" class="w-full px-3 py-2 border border-stone-200 rounded-xl text-sm">
                <option value="">All Categories</option>
                <template x-for="cat in ESOUQ_STORE.categories" :key="cat.slug"><option :value="cat.slug" x-text="cat.name"></option></template>
            </select>
            <select x-model="brand" class="w-full px-3 py-2 border border-stone-200 rounded-xl text-sm">
                <option value="">All Brands</option>
                <template x-for="b in ESOUQ_STORE.brands" :key="b"><option :value="b" x-text="b"></option></template>
            </select>
            <button @click="mobileFiltersOpen = false" class="w-full py-3 bg-souq-600 text-white font-semibold rounded-xl">Apply Filters</button>
        </div>
    </div>
</section>
@endsection
