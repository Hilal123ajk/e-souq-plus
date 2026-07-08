@extends('layouts.app')

@section('title', 'Product — E-Souq Plus')

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
}" x-init="if (product) document.title = product.name + ' — E-Souq Plus'">

    <template x-if="!product">
        <div class="max-w-7xl mx-auto px-4 py-20 text-center">
            <h1 class="text-2xl font-bold text-stone-800 mb-4">Product not found</h1>
            <a href="{{ route('store.products.index') }}" class="text-souq-600 font-semibold hover:underline">Browse all products</a>
        </div>
    </template>

    <template x-if="product">
        <div>
            <div class="lg:hidden bg-white border-b border-stone-200 sticky top-16 z-30">
                <div class="max-w-7xl mx-auto px-2">
                    <button type="button" @click="ESOUQ_STORE.goBack(product.categoryUrl || '/categories/all')" class="inline-flex items-center gap-1.5 px-3 py-2.5 text-sm font-medium text-stone-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg> Back
                    </button>
                </div>
            </div>

            <div class="bg-white border-b border-stone-200">
                <div class="max-w-7xl mx-auto px-4 py-3">
                    <nav class="text-xs text-stone-400 flex items-center gap-2 flex-wrap">
                        <a href="{{ url('/') }}" class="hover:text-souq-600">Home</a><span>/</span>
                        <template x-if="product.categoryName">
                            <span class="flex items-center gap-2">
                                <a :href="product.categoryUrl || ('/categories/' + product.parentCategory)" class="hover:text-souq-600" x-text="product.categoryName"></a><span>/</span>
                            </span>
                        </template>
                        <span class="text-stone-600 line-clamp-1" x-text="product.name"></span>
                    </nav>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 py-6 md:py-10">
                <div class="grid md:grid-cols-2 gap-8 md:gap-12">
                    <div>
                        <div class="aspect-square rounded-3xl overflow-hidden bg-stone-100 border border-stone-200 mb-3">
                            <img :src="displayImage" :alt="product.name" class="w-full h-full object-contain">
                        </div>
                        <div class="flex gap-2 flex-wrap" x-show="thumbnails.length > 1">
                            <template x-for="thumb in thumbnails" :key="thumb.key">
                                <button type="button" @click="selectThumbnail(thumb.key)" :class="selectedImageKey === thumb.key ? 'ring-2 ring-souq-500' : 'ring-1 ring-stone-200'" class="w-16 h-16 rounded-xl overflow-hidden shrink-0 bg-stone-50">
                                    <img :src="thumb.url" :alt="thumb.label" class="w-full h-full object-contain">
                                </button>
                            </template>
                        </div>
                    </div>

                    <div>
                        <span class="inline-block px-3 py-1 bg-souq-100 text-souq-700 text-xs font-semibold rounded-full mb-3" x-text="product.brand"></span>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-stone-900" x-text="product.name"></h1>
                        <div class="mt-4 flex items-baseline gap-3">
                            <span class="text-3xl font-extrabold text-souq-700" x-text="ESOUQ_STORE.formatPrice(product.price)"></span>
                            <span x-show="product.originalPrice > product.price" class="text-lg text-stone-400 line-through" x-text="ESOUQ_STORE.formatPrice(product.originalPrice)"></span>
                        </div>
                        <p class="mt-3 text-sm font-medium flex items-center gap-1.5" :class="product.inStock ? 'text-emerald-600' : 'text-red-600'">
                            <span class="w-2 h-2 rounded-full" :class="product.inStock ? 'bg-emerald-500' : 'bg-red-500'"></span>
                            <span x-text="product.inStock ? 'In Stock' : 'Out of Stock'"></span>
                        </p>
                        <div class="mt-3" x-show="product.hasVariants && product.hasColors">
                            <p class="text-sm font-semibold">Color: <span class="font-normal text-stone-500" x-text="selectedVariant.label"></span></p>
                        </div>
                        <p class="mt-4 text-sm text-stone-600 leading-relaxed" x-text="product.description"></p>

                        <div class="mt-6 flex flex-col sm:flex-row gap-3">
                            <div class="flex items-center border-2 border-stone-200 rounded-full overflow-hidden w-fit">
                                <button @click="quantity = Math.max(1, quantity - 1)" class="px-4 py-2.5 hover:bg-stone-50">−</button>
                                <span class="px-4 py-2.5 font-bold" x-text="quantity"></span>
                                <button @click="quantity++" class="px-4 py-2.5 hover:bg-stone-50">+</button>
                            </div>
                            <button @click="addToCart()" :disabled="!product.inStock" class="flex-1 py-3 bg-gradient-to-r from-souq-600 to-souq-700 text-white font-bold rounded-full disabled:from-stone-300 transition shadow-md">Add to Cart</button>
                        </div>
                        <button @click="addToCart(false); window.location.href = '{{ route('store.checkout') }}'" :disabled="!product.inStock" class="mt-3 w-full py-3 border-2 border-souq-600 text-souq-700 font-bold rounded-full hover:bg-souq-600 hover:text-white disabled:opacity-50 transition">Buy Now — COD</button>
                    </div>
                </div>

                <div class="mt-16 border-t border-stone-200 pt-10" x-show="relatedProducts.length">
                    <h2 class="text-xl font-extrabold text-stone-900 mb-6">You May Also Like</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <template x-for="related in relatedProducts" :key="related.id">
                            <a :href="'/products/' + related.slug" class="bg-white rounded-2xl border border-stone-200 overflow-hidden group hover:shadow-md transition">
                                <div class="aspect-square overflow-hidden bg-stone-50">
                                    <img :src="related.image" :alt="related.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                </div>
                                <div class="p-3">
                                    <p class="text-sm font-semibold text-stone-800 line-clamp-2" x-text="related.name"></p>
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
