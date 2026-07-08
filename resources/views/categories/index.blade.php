@extends('layouts.app')

@section('title', 'Shop by Category — E-Souq Plus')

@section('content')
<section class="relative overflow-hidden bg-gradient-to-br from-souq-700 to-souq-950 text-white">
    <div class="max-w-7xl mx-auto px-4 py-10 md:py-14">
        <nav class="text-xs text-souq-300 mb-4 flex items-center gap-2">
            <a href="{{ url('/') }}" class="hover:text-white">Home</a><span>/</span><span class="text-souq-100">Categories</span>
        </nav>
        <h1 class="text-3xl md:text-4xl font-extrabold mb-2">Shop by Category</h1>
        <p class="text-souq-200 text-sm md:text-base">Carpets, jewelry, perfumes, traditional finds & more.</p>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-10 md:py-14">
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 gap-4 md:gap-6" x-data="{}">
        <template x-if="ESOUQ_STORE.categories.length === 0">
            <div class="col-span-full text-center py-16 bg-white rounded-3xl border border-stone-200">
                <p class="text-stone-600 font-medium">No categories available yet.</p>
                <a href="{{ route('store.products.index') }}" class="mt-3 inline-block text-souq-600 font-semibold text-sm hover:underline">Browse all products</a>
            </div>
        </template>
        <template x-for="cat in ESOUQ_STORE.categories" :key="cat.slug">
            <a :href="'/categories/' + cat.slug"
               class="group relative bg-white rounded-3xl border border-stone-200 overflow-hidden hover:shadow-2xl hover:border-souq-300 transition-all duration-300">
                <div class="aspect-[4/3] overflow-hidden">
                    <img :src="cat.image" :alt="cat.name" loading="lazy" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                </div>
                <div class="p-4 md:p-5 border-t border-stone-100">
                    <p class="text-base md:text-lg font-bold text-stone-800 group-hover:text-souq-700 transition" x-text="cat.name"></p>
                    <p class="text-xs text-stone-400 mt-1" x-text="cat.count + ' products'"></p>
                </div>
            </a>
        </template>
    </div>
</section>

<section class="bg-white border-y border-stone-200">
    <div class="max-w-7xl mx-auto px-4 py-10">
        <h2 class="text-xl font-extrabold text-stone-900 mb-6 text-center">Shop by Brand</h2>
        <div class="flex flex-wrap justify-center gap-3" x-data="{}">
            <template x-if="ESOUQ_STORE.brands.length === 0">
                <p class="text-stone-500 text-sm">No brands with products yet.</p>
            </template>
            <template x-for="brand in ESOUQ_STORE.brands" :key="brand">
                <a :href="'/categories/all?brand=' + encodeURIComponent(brand)"
                   class="px-5 py-2.5 bg-stone-100 hover:bg-souq-600 hover:text-white text-sm font-semibold text-stone-700 rounded-full transition border border-stone-200 hover:border-souq-600"
                   x-text="brand"></a>
            </template>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-10 md:py-14">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl md:text-2xl font-extrabold text-stone-900">Trending Now</h2>
        <a href="{{ route('store.products.index') }}" class="text-sm font-semibold text-souq-600">View All →</a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4" x-data="{}">
        <template x-if="ESOUQ_STORE.products.length === 0">
            <div class="col-span-full text-center py-12 text-stone-500">
                <p>No products to show yet.</p>
            </div>
        </template>
        <template x-for="product in ESOUQ_STORE.products.slice(0, 8)" :key="'trend-' + product.id">
            <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden group hover:shadow-lg transition flex flex-col">
                <a :href="'/products/' + product.slug" class="aspect-square overflow-hidden bg-stone-50 block">
                    <img :src="product.image" :alt="product.name" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                </a>
                <div class="p-3 flex flex-col flex-1">
                    <p class="text-xs text-stone-400" x-text="product.brand"></p>
                    <a :href="'/products/' + product.slug" class="text-sm font-semibold text-stone-800 line-clamp-2 hover:text-souq-700" x-text="product.name"></a>
                    <div class="mt-auto pt-2 flex items-center justify-between">
                        <p class="text-sm font-bold text-souq-700" x-text="ESOUQ_STORE.formatPrice(product.price)"></p>
                        <button @click="$store.cart.add(product)" :disabled="!product.inStock" class="p-2 bg-souq-600 text-white rounded-lg hover:bg-souq-700 disabled:bg-stone-300 transition" aria-label="Add to cart">
                            <x-cart-icon />
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</section>
@endsection
