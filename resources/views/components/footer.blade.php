<footer class="mt-20">
    {{-- Newsletter --}}
    <div class="bg-gradient-to-br from-souq-800 via-souq-900 to-souq-950 text-white">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-8">
                <div class="text-center lg:text-left max-w-md">
                    <h3 class="text-2xl font-extrabold mb-2">Subscribe for new updates</h3>
                    <p class="text-souq-200 text-sm">Get the latest deals, new arrivals, and offers delivered to your inbox.</p>
                </div>
                <form x-data="newsletterForm()" @submit.prevent="subscribe" class="flex w-full lg:w-auto gap-2 max-w-md">
                    <input type="email" x-model="email" placeholder="you@email.com" required :disabled="loading"
                           class="flex-1 px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-sm text-white placeholder-souq-300 focus:outline-none focus:ring-2 focus:ring-accent-400 disabled:opacity-60">
                    <button type="submit" :disabled="loading"
                            class="px-6 py-3 bg-accent-500 hover:bg-accent-600 text-white font-bold rounded-xl text-sm transition whitespace-nowrap disabled:opacity-60">
                        <span x-text="loading ? '…' : 'Subscribe'"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Main footer --}}
    <div class="bg-stone-900 text-stone-400">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="col-span-2 md:col-span-1">
                    <div class="mb-4">
                        <x-brand-logo theme="dark" size="lg" tagline="Marketplace" />
                    </div>
                    <p class="text-sm leading-relaxed mb-4">Pakistan's growing online marketplace — mobile accessories, furniture, home décor, electronics & more.</p>
                    <div class="flex gap-2">
                        <a href="#" class="w-9 h-9 bg-stone-800 rounded-lg flex items-center justify-center hover:bg-souq-600 hover:text-white transition" aria-label="Facebook">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" class="w-9 h-9 bg-stone-800 rounded-lg flex items-center justify-center hover:bg-souq-600 hover:text-white transition" aria-label="Instagram">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849C2.945 3.99 4.456 2.458 7.68 2.163 8.945 2.105 9.325 2.093 12 2.093zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </a>
                    </div>
                </div>

                <div>
                    <h4 class="text-white font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('store.categories.index') }}" class="hover:text-accent-400 transition">Categories</a></li>
                        <li><a href="{{ route('store.products.index') }}" class="hover:text-accent-400 transition">All Products</a></li>
                        <li><a href="{{ route('store.products.index', ['sort' => 'discount']) }}" class="hover:text-accent-400 transition">Deals</a></li>
                        <li><a href="{{ route('store.pages.about') }}" class="hover:text-accent-400 transition">About Us</a></li>
                        <li><a href="{{ route('store.pages.contact') }}" class="hover:text-accent-400 transition">Contact</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-semibold mb-4">Customer Care</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('store.pages.delivery') }}" class="hover:text-accent-400 transition">Delivery Process</a></li>
                        <li><a href="{{ route('store.pages.returns') }}" class="hover:text-accent-400 transition">Returns & Exchange</a></li>
                        <li><a href="{{ route('store.pages.faqs') }}" class="hover:text-accent-400 transition">FAQs</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-semibold mb-4">Contact</h4>
                    <ul class="space-y-3 text-sm">
                        <li>
                            <a href="tel:+923001234567" class="text-white font-medium hover:text-accent-400 transition">+92 300 1234567</a>
                            <p class="text-stone-500 text-xs">Phone / SMS</p>
                        </li>
                        <li>
                            <a href="https://wa.me/923001234567" target="_blank" rel="noopener" class="text-white font-medium hover:text-accent-400 transition">WhatsApp</a>
                        </li>
                        <li>
                            <a href="mailto:hello@e-souq-plus.com" class="hover:text-accent-400 transition break-all">hello@e-souq-plus.com</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="border-t border-stone-800">
            <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-stone-500">
                <p>&copy; {{ date('Y') }} E-Souq Plus — All Rights Reserved</p>
                <div class="flex items-center gap-3">
                    <span>Cash on Delivery</span><span>·</span>
                    <span>Secure Shopping</span><span>·</span>
                    <span>Easy Returns</span>
                </div>
            </div>
        </div>
    </div>
</footer>
