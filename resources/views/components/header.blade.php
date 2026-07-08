@php
    $navCategories = $storeCatalogCategories ?? [];
    $activeCategorySlug = null;

    if (request()->routeIs('store.categories.show')) {
        $activeCategorySlug = request()->route('slug');
    } elseif (request()->routeIs('store.categories.sub.show')) {
        $activeCategorySlug = request()->route('parentSlug');
    }

    $isAllCategoriesActive = request()->routeIs('store.categories.index');
    $isHotDealsActive = request()->routeIs('store.products.index') && request('sort') === 'discount';

    $navPillActive = 'inline-flex items-center gap-1.5 px-4 py-1.5 bg-souq-600 text-white text-sm font-semibold rounded-full whitespace-nowrap';
    $navPillInactive = 'inline-block px-4 py-1.5 text-sm font-medium text-stone-600 hover:text-souq-700 hover:bg-white rounded-full transition whitespace-nowrap border border-transparent hover:border-stone-200';
    $navPillDealsActive = 'inline-flex items-center gap-1 px-4 py-1.5 text-sm font-semibold text-white bg-accent-500 rounded-full whitespace-nowrap';
    $navPillDealsInactive = 'inline-flex items-center gap-1 px-4 py-1.5 text-sm font-semibold text-accent-600 hover:bg-accent-50 rounded-full transition whitespace-nowrap';
@endphp

<header class="bg-white/95 backdrop-blur-md border-b border-stone-200 sticky top-0 z-40 shadow-sm" x-data="{ mobileMenu: false, mobileSearch: false }">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center gap-3 h-16">
            {{-- Mobile menu --}}
            <button @click="mobileMenu = !mobileMenu" class="lg:hidden p-2 -ml-2 text-stone-500 hover:text-souq-700 transition" aria-label="Menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            {{-- Logo --}}
            <x-brand-logo href="{{ url('/') }}" size="lg" class="shrink-0" />

            {{-- Search (desktop) --}}
            <form action="{{ route('store.products.index') }}" method="GET" class="hidden md:flex flex-1 max-w-xl mx-6">
                <div class="relative w-full">
                    <input type="search" name="q" placeholder="Search accessories, furniture, electronics..."
                           class="w-full pl-4 pr-24 py-2.5 bg-stone-100 border border-stone-200 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-souq-500 focus:border-transparent transition">
                    <button type="submit" class="absolute right-1 top-1 bottom-1 px-5 bg-gradient-to-r from-souq-600 to-souq-700 text-white rounded-full hover:from-souq-700 hover:to-souq-800 transition text-sm font-semibold">
                        Search
                    </button>
                </div>
            </form>

            {{-- Actions --}}
            <div class="flex items-center gap-1 sm:gap-2 ml-auto shrink-0">
                <button @click="mobileSearch = !mobileSearch" class="md:hidden p-2 text-stone-500 hover:text-souq-700" aria-label="Search">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
                <a href="{{ route('store.pages.contact') }}" class="hidden sm:flex items-center gap-1.5 p-2 text-stone-500 hover:text-souq-700 transition text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span class="hidden lg:inline font-medium">Help</span>
                </a>
                <button type="button" @click="$store.cart.openDrawer()" class="relative flex items-center gap-1.5 px-3 py-2 bg-stone-100 hover:bg-souq-50 text-stone-700 hover:text-souq-800 rounded-full transition" aria-label="Open cart">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span class="hidden lg:inline text-sm font-semibold">Cart</span>
                    <span x-show="$store.cart.count > 0"
                          x-text="$store.cart.count"
                          class="absolute -top-1 -right-1 lg:static lg:ml-0 min-w-[20px] h-5 px-1.5 bg-accent-500 text-white text-xs font-bold rounded-full flex items-center justify-center"></span>
                </button>
            </div>
        </div>

        {{-- Mobile search --}}
        <div x-show="mobileSearch" x-cloak class="md:hidden pb-3">
            <form action="{{ route('store.products.index') }}" method="GET">
                <input type="search" name="q" placeholder="Search products..."
                       class="w-full px-4 py-2.5 bg-stone-100 border border-stone-200 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
            </form>
        </div>
    </div>

    {{-- Category pills nav (desktop) --}}
    <nav class="hidden lg:block border-t border-stone-100 bg-stone-50/80" aria-label="Store categories">
        <div class="max-w-7xl mx-auto px-4 py-2">
            <ul class="flex items-center gap-2 overflow-x-auto scrollbar-hide">
                <li>
                    <a href="{{ route('store.categories.index') }}"
                       @class([$isAllCategoriesActive ? $navPillActive : $navPillInactive, 'hover:bg-souq-700 transition' => $isAllCategoriesActive])>
                        All Categories
                    </a>
                </li>
                @foreach ($navCategories as $nav)
                <li>
                    <a href="{{ route('store.categories.show', $nav['slug']) }}"
                       @class([($activeCategorySlug === $nav['slug']) ? $navPillActive : $navPillInactive, 'hover:bg-souq-700 transition' => ($activeCategorySlug === $nav['slug'])])>
                        {{ $nav['name'] }}
                    </a>
                </li>
                @endforeach
                <li>
                    <a href="{{ route('store.products.index', ['sort' => 'discount']) }}"
                       @class([$isHotDealsActive ? $navPillDealsActive : $navPillDealsInactive])>
                        🔥 Hot Deals
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    {{-- Mobile menu drawer --}}
    <div x-show="mobileMenu" x-cloak class="lg:hidden fixed inset-0 z-50">
        <div @click="mobileMenu = false" class="absolute inset-0 bg-stone-900/60 backdrop-blur-sm"></div>
        <div class="absolute left-0 top-0 bottom-0 w-80 max-w-[85vw] bg-white shadow-2xl overflow-y-auto">
            <div class="p-5 border-b border-stone-200 flex items-center justify-between bg-gradient-to-r from-souq-50 to-white">
                <span class="font-bold text-souq-900 text-lg">Menu</span>
                <button @click="mobileMenu = false" class="p-2 text-stone-400 hover:text-stone-700 rounded-lg hover:bg-stone-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-4 space-y-1">
                <a href="{{ url('/') }}" @class(['block px-4 py-3 rounded-xl font-medium transition', request()->routeIs('store.home') ? 'bg-souq-50 text-souq-800' : 'text-stone-800 hover:bg-souq-50 hover:text-souq-800'])>Home</a>
                <a href="{{ route('store.categories.index') }}" @class(['block px-4 py-3 rounded-xl font-medium transition', $isAllCategoriesActive ? 'bg-souq-50 text-souq-800' : 'text-stone-800 hover:bg-souq-50'])>All Categories</a>
                <a href="{{ route('store.products.index') }}" @class(['block px-4 py-3 rounded-xl font-medium transition', request()->routeIs('store.products.index') && ! $isHotDealsActive ? 'bg-souq-50 text-souq-800' : 'text-stone-800 hover:bg-souq-50'])>All Products</a>
                <hr class="my-3 border-stone-200">
                <p class="px-4 text-xs font-bold text-stone-400 uppercase tracking-wider mb-2">Shop</p>
                @foreach ($navCategories as $nav)
                <a href="{{ route('store.categories.show', $nav['slug']) }}"
                   @class(['block px-4 py-2.5 rounded-xl text-sm transition', ($activeCategorySlug === $nav['slug']) ? 'bg-souq-50 text-souq-800 font-semibold' : 'text-stone-600 hover:bg-stone-50 hover:text-souq-700'])>
                    {{ $nav['name'] }}
                </a>
                @endforeach
                <hr class="my-3 border-stone-200">
                <p class="px-4 text-xs font-bold text-stone-400 uppercase tracking-wider mb-2">Support</p>
                <a href="{{ route('store.pages.about') }}" class="block px-4 py-2.5 rounded-xl text-sm text-stone-600 hover:bg-stone-50">About Us</a>
                <a href="{{ route('store.pages.contact') }}" class="block px-4 py-2.5 rounded-xl text-sm text-stone-600 hover:bg-stone-50">Contact</a>
                <a href="{{ route('store.pages.shipping') }}" class="block px-4 py-2.5 rounded-xl text-sm text-stone-600 hover:bg-stone-50">Delivery Process</a>
                <a href="{{ route('store.pages.faqs') }}" class="block px-4 py-2.5 rounded-xl text-sm text-stone-600 hover:bg-stone-50">FAQs</a>
            </div>
        </div>
    </div>
</header>
