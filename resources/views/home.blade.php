@extends('layouts.app')

@section('title', 'E-Souq Plus — Online Marketplace in UAE')

@section('content')
{{-- Hero bento grid --}}
<section class="max-w-7xl mx-auto px-4 py-6 md:py-10" x-data="heroSlider()">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-5">
        {{-- Main hero (static banners) --}}
        <div class="md:col-span-8 relative rounded-3xl overflow-hidden min-h-[280px] md:min-h-[380px] bg-souq-900">
            <template x-for="(banner, index) in banners" :key="index">
                <div x-show="current === index"
                     x-transition:enter="transition ease-out duration-700"
                     x-transition:enter-start="opacity-0 scale-105"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute inset-0">
                    <template x-if="banner.image">
                        <img :src="banner.image" :alt="banner.title" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!banner.image">
                        <div class="w-full h-full bg-gradient-to-br from-souq-700 to-souq-950"></div>
                    </template>
                    <div class="absolute inset-0 bg-gradient-to-r" :class="banner.accent"></div>
                    <div class="absolute inset-0 flex items-end p-6 md:p-10">
                        <div class="max-w-lg">
                            <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur text-white text-xs font-semibold rounded-full mb-3" x-text="banner.badge || 'Featured'"></span>
                            <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white mb-2 leading-tight" x-text="banner.title"></h1>
                            <p class="text-sm md:text-base text-white/80 mb-5" x-text="banner.subtitle"></p>
                            <a :href="banner.link" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-souq-800 font-bold rounded-full text-sm hover:bg-accent-50 transition shadow-lg">
                                <span x-text="banner.cta"></span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </template>
            <div class="absolute bottom-4 right-4 flex gap-2 z-10">
                <button @click="prev()" class="w-9 h-9 bg-white/20 hover:bg-white/40 backdrop-blur rounded-full flex items-center justify-center text-white transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button @click="next()" class="w-9 h-9 bg-white/20 hover:bg-white/40 backdrop-blur rounded-full flex items-center justify-center text-white transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        {{-- Side promo cards (static) --}}
        <div class="md:col-span-4 grid grid-rows-2 gap-4 md:gap-5">
            @foreach ($sidePromoCategories as $promo)
            <a href="{{ $promo['url'] }}" class="group relative rounded-3xl overflow-hidden min-h-[140px] hover:shadow-xl transition">
                @if ($promo['image'])
                <img src="{{ $promo['image'] }}" alt="{{ $promo['name'] }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                @else
                <div class="absolute inset-0 bg-gradient-to-br from-souq-600 to-souq-800"></div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/40 to-transparent"></div>
                <div class="relative h-full p-6 flex flex-col justify-end">
                    <p class="text-white/80 text-xs font-semibold uppercase tracking-wider mb-1">Category</p>
                    <h3 class="text-xl font-extrabold text-white mb-2">{{ $promo['name'] }}</h3>
                    <span class="text-white text-sm font-semibold group-hover:underline">Shop now →</span>
                </div>
            </a>
            @endforeach
            <a href="{{ route('store.products.index') }}" class="group relative rounded-3xl overflow-hidden bg-gradient-to-br from-souq-600 to-souq-800 p-6 flex flex-col justify-end min-h-[140px] hover:shadow-xl transition">
                <p class="text-souq-200 text-xs font-semibold uppercase tracking-wider mb-1">Browse</p>
                <h3 class="text-xl font-extrabold text-white mb-2">All Products</h3>
                <span class="text-white text-sm font-semibold group-hover:underline">Shop now →</span>
            </a>
        </div>
    </div>
</section>

{{-- Trust badges --}}
<section class="max-w-7xl mx-auto px-4 pb-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @foreach([
            ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'title' => 'Quality Assured', 'sub' => 'Curated products'],
            ['icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'title' => 'Cash on Delivery', 'sub' => 'Pay on arrival'],
            ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'title' => 'Fast Delivery', 'sub' => '2–5 business days'],
            ['icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'title' => 'Easy Returns', 'sub' => 'Hassle-free policy'],
        ] as $badge)
        <div class="flex items-center gap-3 p-4 bg-white rounded-2xl border border-stone-200 shadow-sm">
            <div class="w-10 h-10 bg-souq-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-souq-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $badge['icon'] }}"/></svg>
            </div>
            <div>
                <p class="text-sm font-bold text-stone-800">{{ $badge['title'] }}</p>
                <p class="text-xs text-stone-500">{{ $badge['sub'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- Categories (static tiles matching hero) --}}
<section class="max-w-7xl mx-auto px-4 py-8 md:py-12">
    <div class="flex items-end justify-between mb-6">
        <div>
            <h2 class="text-2xl md:text-3xl font-extrabold text-stone-900">Browse Categories</h2>
            <p class="text-stone-500 text-sm mt-1">Carpets, jewelry, stones & beads, and perfumes</p>
        </div>
        <a href="{{ route('store.categories.index') }}" class="hidden sm:flex items-center gap-1 text-sm font-semibold text-souq-600 hover:text-souq-800 transition">
            View All <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
        @foreach ($staticCategoryTiles as $cat)
        <a href="{{ route('store.categories.show', $cat['slug']) }}" class="group text-center">
            <div class="aspect-square rounded-2xl overflow-hidden bg-stone-100 border-2 border-stone-200 group-hover:border-souq-400 group-hover:shadow-lg transition-all mb-2">
                <img src="{{ $cat['image'] }}" alt="{{ $cat['name'] }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
            </div>
            <p class="text-xs md:text-sm font-semibold text-stone-800 line-clamp-2 group-hover:text-souq-700 transition">{{ $cat['name'] }}</p>
        </a>
        @endforeach
    </div>
</section>

{{-- Featured Products --}}
<section class="bg-white border-y border-stone-200">
    <div class="max-w-7xl mx-auto px-4 py-10 md:py-14">
        <div class="flex items-end justify-between mb-8">
            <div>
                <span class="text-accent-500 text-xs font-bold uppercase tracking-wider">Top Picks</span>
                <h2 class="text-2xl md:text-3xl font-extrabold text-stone-900 mt-1">Featured Products</h2>
            </div>
            <a href="{{ route('store.products.index') }}" class="text-sm font-semibold text-souq-600 hover:text-souq-800">See All →</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-5">
            @forelse ($featuredProducts as $product)
            <div class="group bg-stone-50 rounded-2xl border border-stone-200 overflow-hidden hover:shadow-xl hover:border-souq-200 transition-all duration-300 flex flex-col">
                <a href="{{ route('store.products.show', $product['slug']) }}" class="relative block aspect-square overflow-hidden bg-white">
                    <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @unless ($product['inStock'])
                    <span class="absolute inset-0 bg-black/40 flex items-center justify-center">
                        <span class="px-3 py-1 bg-white text-stone-800 text-xs font-bold rounded-full">Out of Stock</span>
                    </span>
                    @endunless
                </a>
                <div class="p-3 md:p-4 flex flex-col flex-1 border-l-4 border-l-transparent group-hover:border-l-souq-500 transition-colors">
                    @if ($product['brand'])
                    <p class="text-[10px] text-stone-400 uppercase tracking-wide">{{ $product['brand'] }}</p>
                    @endif
                    <a href="{{ route('store.products.show', $product['slug']) }}" class="text-xs md:text-sm font-semibold text-stone-800 line-clamp-2 mt-0.5 hover:text-souq-700 transition">{{ $product['name'] }}</a>
                    <div class="mt-auto pt-3 flex items-end justify-between gap-2">
                        <div>
                            <p class="text-base font-bold text-souq-800">AED {{ number_format($product['price'], 0) }}</p>
                        </div>
                        <button type="button" @click="$store.cart.add(@js($product))" @disabled(!$product['inStock'])
                                class="p-2.5 bg-souq-600 hover:bg-souq-700 disabled:bg-stone-300 text-white rounded-xl transition shrink-0" aria-label="Add to cart">
                            <x-cart-icon />
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12">
                <p class="text-stone-500 font-medium">No products available yet.</p>
                <a href="{{ route('store.categories.index') }}" class="mt-3 inline-block text-souq-600 font-semibold text-sm hover:underline">Browse categories</a>
            </div>
            @endforelse
        </div>

        @if ($featuredProducts->hasPages())
        <nav class="mt-8 flex flex-wrap items-center justify-center gap-2" aria-label="Featured products pagination">
            @if ($featuredProducts->onFirstPage())
            <span class="px-4 py-2 text-sm text-stone-300 border border-stone-200 rounded-xl bg-stone-50">Previous</span>
            @else
            <a href="{{ $featuredProducts->previousPageUrl() }}" class="px-4 py-2 text-sm font-medium text-stone-800 border border-stone-200 rounded-xl hover:bg-stone-50 transition">Previous</a>
            @endif
            <span class="px-4 py-2 text-sm text-stone-500">Page {{ $featuredProducts->currentPage() }} of {{ $featuredProducts->lastPage() }}</span>
            @if ($featuredProducts->hasMorePages())
            <a href="{{ $featuredProducts->nextPageUrl() }}" class="px-4 py-2 text-sm font-medium text-stone-800 border border-stone-200 rounded-xl hover:bg-stone-50 transition">Next</a>
            @else
            <span class="px-4 py-2 text-sm text-stone-300 border border-stone-200 rounded-xl bg-stone-50">Next</span>
            @endif
        </nav>
        @endif
    </div>
</section>

{{-- Promo strip --}}
<section class="max-w-7xl mx-auto px-4 py-12">
    <div class="relative rounded-3xl overflow-hidden bg-gradient-to-r from-souq-800 to-souq-950 p-8 md:p-12 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="text-center md:text-left">
            <h2 class="text-2xl md:text-3xl font-extrabold text-white mb-2">Shop Our Collection</h2>
            <p class="text-souq-200 text-sm max-w-md">Carpets · Artificial Jewelry · Stone and Beads · Perfumes</p>
        </div>
        <a href="{{ route('store.categories.index') }}" class="shrink-0 px-8 py-3.5 bg-accent-500 hover:bg-accent-600 text-white font-bold rounded-full transition shadow-lg">
            View Categories
        </a>
    </div>
</section>

{{-- New Arrivals --}}
<section class="max-w-7xl mx-auto px-4 pb-16">
    <div class="flex items-end justify-between mb-6">
        <h2 class="text-2xl md:text-3xl font-extrabold text-stone-900">New Arrivals</h2>
        <a href="{{ route('store.products.index') }}" class="text-sm font-semibold text-souq-600 hover:text-souq-800">View All →</a>
    </div>
    @if (count($newArrivals) > 0)
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 md:gap-4">
        @foreach ($newArrivals as $product)
        <a href="{{ route('store.products.show', $product['slug']) }}" class="bg-white rounded-2xl border border-stone-200 p-3 hover:shadow-md hover:border-souq-200 transition group">
            <div class="aspect-square rounded-xl overflow-hidden bg-stone-50 mb-3">
                <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
            </div>
            <p class="text-xs font-semibold text-stone-800 line-clamp-2">{{ $product['name'] }}</p>
            <p class="text-sm font-bold text-souq-700 mt-1">AED {{ number_format($product['price'], 0) }}</p>
        </a>
        @endforeach
    </div>
    @else
    <div class="text-center py-12 bg-white rounded-2xl border border-stone-200">
        <p class="text-stone-500">New arrivals will appear here once products are added in admin.</p>
    </div>
    @endif
</section>
@endsection
