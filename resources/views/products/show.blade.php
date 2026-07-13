@extends('layouts.app')

@section('content')
<section x-data="{
    slug: @js($slug),
    quantity: 1,
    selectedImageKey: 'main',
    get product() { return ESOUQ_STORE.getProduct(this.slug); },
    get relatedProducts() {
        if (!this.product) return [];
        return ESOUQ_STORE.products.filter(p => p.id !== this.product.id && (p.parentCategory === this.product.parentCategory || p.category === this.product.category)).slice(0, 4);
    },
    get thumbnails() {
        if (!this.product) return [];
        const items = [{ key: 'main', url: this.product.image, label: 'Main' }];
        if (this.product.hasVariants) {
            (this.product.colors || []).forEach(c => items.push({ key: String(c.id), url: c.url, label: c.label, id: c.id }));
        } else {
            (this.product.gallery || []).forEach(img => items.push({ key: String(img.id), url: img.url, label: img.label || 'View', id: null }));
        }
        return items;
    },
    get displayImage() {
        const t = this.thumbnails.find(i => i.key === this.selectedImageKey);
        return t?.url || this.product?.image;
    },
    get selectedVariant() {
        if (!this.product?.hasVariants || this.selectedImageKey === 'main') return { id: null, url: this.product?.image, label: 'Main' };
        return (this.product.colors || []).find(c => String(c.id) === this.selectedImageKey) ?? { id: null, url: this.product.image, label: 'Main' };
    },
    selectThumbnail(key) { this.selectedImageKey = key; },
    addToCart(openDrawer = true) { this.$store.cart.add(this.product, this.quantity, openDrawer, this.selectedVariant); }
}">

    <template x-if="!product">
        <div class="max-w-7xl mx-auto px-4 py-20 text-center">
            <div class="w-20 h-20 bg-stone-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-10 h-10 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-stone-800 mb-2">Product not found</h1>
            <p class="text-stone-500 text-sm mb-6">This item may have been removed or is no longer available.</p>
            <a href="{{ route('store.products.index') }}" class="inline-flex px-6 py-3 bg-souq-600 text-white font-semibold rounded-full hover:bg-souq-700 transition">Browse all products</a>
        </div>
    </template>

    <template x-if="product">
        <div class="bg-stone-50 min-h-screen">
            <x-mobile-back-nav :fallback="route('store.products.index')" />

            <div class="bg-white border-b border-stone-200">
                <div class="max-w-7xl mx-auto px-4 py-3">
                    <nav class="text-xs text-stone-400 flex items-center gap-2 flex-wrap">
                        <a href="{{ url('/') }}" class="hover:text-souq-600 transition">Home</a>
                        <span>/</span>
                        <a href="{{ route('store.products.index') }}" class="hover:text-souq-600 transition">Products</a>
                        <template x-if="product.categoryName">
                            <span class="flex items-center gap-2">
                                <span>/</span>
                                <a :href="product.categoryUrl || ('/categories/' + product.parentCategory)" class="hover:text-souq-600 transition" x-text="product.categoryName"></a>
                            </span>
                        </template>
                        <span>/</span>
                        <span class="text-stone-600 line-clamp-1 font-medium" x-text="product.name"></span>
                    </nav>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 py-6 md:py-10">
                <div class="grid lg:grid-cols-12 gap-8 lg:gap-10">
                    {{-- Gallery --}}
                    <div class="lg:col-span-7">
                        <div class="flex flex-col lg:flex-row lg:items-start gap-5 lg:gap-6">
                            <div class="flex-1 min-w-0 order-1">
                                <div class="aspect-square md:aspect-[4/3] lg:aspect-square flex items-center justify-center">
                                    <img :src="displayImage" :alt="product.name" class="w-full h-full object-contain">
                                </div>
                            </div>
                            <div class="order-2 shrink-0" x-show="thumbnails.length > 1">
                                <div class="flex gap-3 overflow-x-auto scrollbar-hide lg:flex-col lg:overflow-visible lg:w-20">
                                    <template x-for="thumb in thumbnails" :key="thumb.key">
                                        <button type="button" @click="selectThumbnail(thumb.key)"
                                                :class="selectedImageKey === thumb.key ? 'opacity-100' : 'opacity-45 hover:opacity-75'"
                                                class="w-[4.5rem] h-[4.5rem] lg:w-20 lg:h-20 shrink-0 transition-opacity">
                                            <img :src="thumb.url" :alt="thumb.label" class="w-full h-full object-contain">
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Product info --}}
                    <div class="lg:col-span-5">
                        <div class="lg:sticky lg:top-24 space-y-5">
                            <div class="bg-white rounded-3xl border border-stone-200 shadow-sm p-5 md:p-7">
                                <div class="flex flex-wrap items-center gap-2 mb-3">
                                    <span class="inline-block px-3 py-1 bg-souq-100 text-souq-700 text-xs font-semibold rounded-full" x-text="product.brand"></span>
                                    <span x-show="product.featured" class="inline-block px-3 py-1 bg-accent-100 text-accent-700 text-xs font-semibold rounded-full">Featured</span>
                                </div>

                                <h1 class="text-2xl md:text-3xl font-extrabold text-stone-900 leading-tight" x-text="product.name"></h1>

                                <div class="mt-4 flex flex-wrap items-end gap-3">
                                    <span class="text-3xl md:text-4xl font-extrabold text-souq-700" x-text="ESOUQ_STORE.formatPrice(product.price)"></span>
                                    <span x-show="product.originalPrice > product.price" class="text-lg text-stone-400 line-through mb-1" x-text="ESOUQ_STORE.formatPrice(product.originalPrice)"></span>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold"
                                          :class="product.inStock ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="product.inStock ? 'bg-emerald-500' : 'bg-red-500'"></span>
                                        <span x-text="product.inStock ? 'In Stock' : 'Out of Stock'"></span>
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-stone-100 text-stone-600 text-xs font-semibold">
                                        SKU: <span x-text="product.sku"></span>
                                    </span>
                                </div>

                                <div class="mt-4" x-show="product.hasVariants && product.hasColors">
                                    <p class="text-sm font-semibold text-stone-800 mb-1">Variant</p>
                                    <p class="text-sm text-stone-500" x-text="selectedVariant.label"></p>
                                </div>

                                <p class="mt-5 text-sm text-stone-600 leading-relaxed border-t border-stone-100 pt-5" x-show="product.description" x-text="product.description"></p>

                                <div class="mt-5 border-t border-stone-100 pt-5" x-show="product.specifications && product.specifications.length">
                                    <p class="text-sm font-semibold text-stone-800 mb-3">Specifications</p>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="spec in product.specifications" :key="spec.label">
                                            <span class="inline-flex items-center gap-1.5 max-w-full px-3 py-1.5 rounded-full bg-stone-100 text-stone-700 text-xs font-medium">
                                                <span class="text-stone-500 font-semibold" x-text="spec.label + ':'"></span>
                                                <span class="text-stone-800 truncate" x-text="spec.value"></span>
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-3xl border border-stone-200 shadow-sm p-5 md:p-6">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                    <div class="flex items-center border-2 border-stone-200 rounded-2xl overflow-hidden w-fit shrink-0">
                                        <button type="button" @click="quantity = Math.max(1, quantity - 1)" class="px-4 py-3 hover:bg-stone-50 text-stone-600 font-bold transition">−</button>
                                        <span class="px-5 py-3 font-bold text-stone-900 min-w-[3rem] text-center" x-text="quantity"></span>
                                        <button type="button" @click="quantity = Math.min(99, quantity + 1)" class="px-4 py-3 hover:bg-stone-50 text-stone-600 font-bold transition">+</button>
                                    </div>
                                    <button type="button" @click="addToCart()" :disabled="!product.inStock"
                                            class="flex-1 py-3.5 bg-gradient-to-r from-souq-600 to-souq-700 hover:from-souq-700 hover:to-souq-800 disabled:from-stone-300 disabled:to-stone-300 text-white font-bold rounded-2xl transition shadow-md">
                                        Add to Cart
                                    </button>
                                </div>
                                <button type="button" @click="addToCart(false); window.location.href = '{{ route('store.checkout') }}'" :disabled="!product.inStock"
                                        class="mt-3 w-full py-3.5 border-2 border-souq-600 text-souq-700 font-bold rounded-2xl hover:bg-souq-600 hover:text-white disabled:opacity-50 disabled:hover:bg-transparent disabled:hover:text-souq-700 transition">
                                    Buy Now
                                </button>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-white rounded-2xl border border-stone-200 p-4 flex items-start gap-3">
                                    <div class="w-9 h-9 bg-souq-100 rounded-xl flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-souq-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-stone-800">Cash on Delivery</p>
                                        <p class="text-[11px] text-stone-500 mt-0.5">Pay when delivered</p>
                                    </div>
                                </div>
                                <div class="bg-white rounded-2xl border border-stone-200 p-4 flex items-start gap-3">
                                    <div class="w-9 h-9 bg-souq-100 rounded-xl flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-souq-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-stone-800">Fast Delivery</p>
                                        <p class="text-[11px] text-stone-500 mt-0.5">2–5 business days</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12 md:mt-16" x-show="relatedProducts.length">
                    <div class="flex items-end justify-between mb-6">
                        <h2 class="text-xl md:text-2xl font-extrabold text-stone-900">You May Also Like</h2>
                        <a href="{{ route('store.products.index') }}" class="text-sm font-semibold text-souq-600 hover:text-souq-800">View all →</a>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <template x-for="related in relatedProducts" :key="related.id">
                            <a :href="'/products/' + related.slug" class="bg-white rounded-2xl border border-stone-200 overflow-hidden group hover:shadow-lg hover:border-souq-200 transition">
                                <div class="aspect-square overflow-hidden bg-stone-50 p-3">
                                    <img :src="related.image" :alt="related.name" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300">
                                </div>
                                <div class="p-3 border-t border-stone-100">
                                    <p class="text-sm font-semibold text-stone-800 line-clamp-2 group-hover:text-souq-700 transition" x-text="related.name"></p>
                                    <p class="text-sm font-bold text-souq-700 mt-1" x-text="ESOUQ_STORE.formatPrice(related.price)"></p>
                                </div>
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>
</section>
@endsection
