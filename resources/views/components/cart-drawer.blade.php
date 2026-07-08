<div x-show="$store.cart.drawerOpen"
     x-cloak
     @keydown.escape.window="$store.cart.closeDrawer()"
     class="fixed inset-0 z-[60]"
     aria-modal="true"
     role="dialog">
    <div x-show="$store.cart.drawerOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="$store.cart.closeDrawer()"
         class="absolute inset-0 bg-stone-900/50 backdrop-blur-sm"></div>

    <div x-show="$store.cart.drawerOpen"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         @click.outside="$store.cart.closeDrawer()"
         class="absolute right-0 top-0 bottom-0 w-full max-w-md bg-white shadow-2xl flex flex-col">

        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200 shrink-0 bg-gradient-to-r from-souq-50 to-white">
            <div>
                <h2 class="text-lg font-bold text-souq-900">Shopping Cart</h2>
                <p class="text-xs text-stone-500" x-show="$store.cart.count > 0">
                    <span x-text="$store.cart.count"></span> item(s)
                </p>
            </div>
            <button @click="$store.cart.closeDrawer()" class="p-2 text-stone-400 hover:text-stone-700 rounded-lg hover:bg-stone-100 transition" aria-label="Close cart">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div x-show="$store.cart.items.length === 0" class="flex-1 flex flex-col items-center justify-center px-6 text-center">
            <div class="w-20 h-20 bg-stone-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-10 h-10 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <p class="font-semibold text-stone-700 mb-1">Your cart is empty</p>
            <p class="text-sm text-stone-500 mb-6">Discover something you love</p>
            <button @click="$store.cart.closeDrawer(); window.location.href = '{{ route('store.home') }}'" class="px-6 py-2.5 bg-souq-600 text-white text-sm font-semibold rounded-full hover:bg-souq-700 transition">
                Start Shopping
            </button>
        </div>

        <div x-show="$store.cart.items.length > 0" class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
            <template x-for="item in $store.cart.displayItems" :key="item.lineKey">
                <div class="flex gap-3 pb-4 border-b border-stone-100 last:border-0">
                    <a :href="'/products/' + item.slug" @click="$store.cart.closeDrawer()" class="w-16 h-16 rounded-xl overflow-hidden bg-stone-50 shrink-0 border border-stone-200">
                        <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                    </a>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] text-stone-400 uppercase font-medium" x-text="item.brand"></p>
                        <a :href="'/products/' + item.slug" @click="$store.cart.closeDrawer()" class="text-sm font-semibold text-stone-800 line-clamp-2 hover:text-souq-700" x-text="item.name"></a>
                        <p x-show="item.variantLabel" class="text-xs text-stone-500 mt-0.5">Variant: <span x-text="item.variantLabel"></span></p>
                        <p class="text-sm font-bold text-souq-700 mt-1" x-text="ESOUQ_STORE.formatPrice(item.price)"></p>
                        <div class="flex items-center justify-between mt-2">
                            <div class="flex items-center border border-stone-200 rounded-lg overflow-hidden text-sm">
                                <button @click="$store.cart.updateQuantity(item.lineKey, item.quantity - 1)" class="px-2.5 py-1 hover:bg-stone-50">−</button>
                                <span class="px-2.5 py-1 font-medium min-w-[1.75rem] text-center" x-text="item.quantity"></span>
                                <button @click="$store.cart.updateQuantity(item.lineKey, item.quantity + 1)" class="px-2.5 py-1 hover:bg-stone-50">+</button>
                            </div>
                            <button @click="$store.cart.remove(item.lineKey)" class="text-xs text-red-500 hover:text-red-700 font-medium">Remove</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="$store.cart.items.length > 0" class="border-t border-stone-200 px-5 py-4 bg-stone-50 shrink-0 space-y-3">
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-stone-500">Subtotal</span><span class="font-medium" x-text="ESOUQ_STORE.formatPrice($store.cart.total)"></span></div>
                <div class="flex justify-between"><span class="text-stone-500">Delivery</span><span class="font-medium" x-text="ESOUQ_STORE.formatPrice($store.cart.deliveryFee)"></span></div>
                <div class="flex justify-between pt-2 border-t border-stone-200">
                    <span class="font-bold text-stone-800">Total</span>
                    <span class="font-extrabold text-souq-800" x-text="ESOUQ_STORE.formatPrice($store.cart.grandTotal)"></span>
                </div>
            </div>
            <a href="{{ url('/checkout') }}" @click="$store.cart.closeDrawer()" class="block w-full py-3.5 bg-gradient-to-r from-souq-600 to-souq-700 hover:from-souq-700 hover:to-souq-800 text-white font-bold rounded-xl text-center transition shadow-md">
                Proceed to Checkout
            </a>
            <a href="{{ route('store.home') }}" @click="$store.cart.closeDrawer()" class="block w-full py-2 text-sm font-medium text-stone-500 hover:text-souq-700 text-center transition">
                Continue Shopping
            </a>
        </div>
    </div>
</div>
