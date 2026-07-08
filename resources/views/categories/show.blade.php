@extends('layouts.app')

@section('title', $category->title . ' — E-Souq Plus')
@section('meta_description', $category->description ?? 'Shop ' . $category->title . ' at E-Souq Plus. Quality products delivered across the UAE.')

@section('content')
@php
    $backFallback = $parentCategory
        ? route('store.categories.show', $parentCategory->slug)
        : route('store.categories.index');
@endphp

@if (! ($showSubcategorySection && count($subcategories) > 0))
<x-mobile-back-nav :fallback="$backFallback" />
@endif

<section x-data="productFilters()" x-init="
    rootCategory = '{{ $rootCategorySlug }}';
    category = '{{ $filterSlug }}';
    includeChildProducts = {{ $includeChildProducts ? 'true' : 'false' }};
">
    <div class="bg-gradient-to-r from-souq-800 to-souq-950 text-white">
        <div class="max-w-7xl mx-auto px-4 py-8 md:py-10">
            <nav class="text-xs text-souq-300 mb-3 flex items-center gap-2 flex-wrap">
                <a href="{{ url('/') }}" class="hover:text-white">Home</a>
                <span>/</span>
                <a href="{{ route('store.categories.index') }}" class="hover:text-white">Categories</a>
                @if ($parentCategory)
                <span>/</span>
                <a href="{{ route('store.categories.show', $parentCategory->slug) }}" class="hover:text-white">{{ $parentCategory->title }}</a>
                @endif
                <span>/</span>
                <span class="text-souq-100">{{ $category->title }}</span>
            </nav>
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-extrabold">{{ $category->title }}</h1>
                    <p class="text-souq-200 text-sm mt-1"><span x-text="filteredProducts.length"></span> products available</p>
                </div>
                <select x-model="sort" class="px-4 py-2 bg-white/10 border border-white/20 rounded-xl text-sm text-white focus:outline-none focus:ring-2 focus:ring-accent-400 w-full md:w-auto">
                    <option value="featured" class="text-stone-800">Featured</option>
                    <option value="price-low" class="text-stone-800">Price: Low to High</option>
                    <option value="price-high" class="text-stone-800">Price: High to Low</option>
                    <option value="rating" class="text-stone-800">Top Rated</option>
                    <option value="discount" class="text-stone-800">Best Discount</option>
                </select>
            </div>
        </div>
    </div>

    @if ($showSubcategorySection && count($subcategories) > 0)
    @php
        $navParent = $parentCategory ?? $category;
        $isAllActive = $parentCategory === null;
    @endphp
    <nav class="sticky top-16 z-30 bg-white border-b border-stone-200 shadow-sm" aria-label="Sub-categories">
        <div class="max-w-7xl mx-auto px-4">
            <ul class="flex items-center overflow-x-auto scrollbar-hide -mb-px">
                <li class="shrink-0 lg:hidden">
                    <button type="button"
                            onclick="window.ESOUQ_STORE.goBack(@js($backFallback))"
                            class="inline-flex items-center px-3 py-3.5 text-stone-700 hover:text-souq-700 transition"
                            aria-label="Go back">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                </li>
                <li class="shrink-0">
                    <a href="{{ route('store.categories.show', $navParent->slug) }}"
                       class="block px-4 py-3.5 text-sm font-semibold whitespace-nowrap border-b-2 transition {{ $isAllActive ? 'text-souq-700 border-souq-600' : 'text-stone-500 border-transparent hover:text-souq-700' }}">
                        All
                    </a>
                </li>
                @foreach ($subcategories as $sub)
                <li class="shrink-0">
                    <a href="{{ $sub['url'] }}"
                       class="block px-4 py-3.5 text-sm font-medium whitespace-nowrap border-b-2 transition {{ $sub['slug'] === $category->slug ? 'text-souq-700 border-souq-600' : 'text-stone-500 border-transparent hover:text-souq-700' }}">
                        {{ $sub['name'] }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
    </nav>
    @endif

    <div class="max-w-7xl mx-auto px-4 py-6 md:py-8">
        <div x-show="filteredProducts.length === 0" x-cloak class="text-center py-16">
            <div class="w-16 h-16 mx-auto mb-4 bg-stone-100 rounded-2xl flex items-center justify-center">
                <svg class="w-8 h-8 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <p class="text-stone-600 font-medium" x-text="emptyMessage"></p>
            <p class="text-stone-400 text-sm mt-2">Check back soon — we're adding new items to {{ $category->title }}.</p>
            <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('store.products.index') }}" class="inline-block px-5 py-2.5 bg-souq-600 text-white text-sm font-semibold rounded-full hover:bg-souq-700 transition">Browse all products</a>
                <a href="{{ route('store.categories.index') }}" class="inline-block px-5 py-2.5 border border-stone-300 text-stone-700 text-sm font-semibold rounded-full hover:bg-stone-50 transition">Other categories</a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <template x-for="product in filteredProducts" :key="product.id">
                <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden group hover:shadow-lg transition flex flex-col">
                    <a :href="'/products/' + product.slug" class="relative aspect-square overflow-hidden bg-stone-50 block">
                        <img :src="product.image" :alt="product.name" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                        <span x-show="!product.inStock" class="absolute inset-0 bg-black/40 flex items-center justify-center">
                            <span class="px-3 py-1 bg-white text-stone-800 text-xs font-bold rounded-full">Out of Stock</span>
                        </span>
                    </a>
                    <div class="p-3 md:p-4 flex flex-col flex-1">
                        <p class="text-xs text-stone-400" x-text="product.brand"></p>
                        <a :href="'/products/' + product.slug" class="text-sm font-semibold text-stone-800 line-clamp-2 hover:text-souq-700" x-text="product.name"></a>
                        <div class="mt-auto pt-3 flex items-end justify-between gap-2">
                            <p class="text-base font-bold text-souq-700" x-text="ESOUQ_STORE.formatPrice(product.price)"></p>
                            <button @click="$store.cart.add(product)" :disabled="!product.inStock" class="p-2.5 bg-souq-600 hover:bg-souq-700 disabled:bg-stone-300 text-white rounded-xl transition" aria-label="Add to cart">
                                <x-cart-icon />
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</section>
@endsection
