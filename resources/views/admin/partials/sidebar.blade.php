<aside :class="$store.adminUi.sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       class="fixed top-0 left-0 z-50 w-64 h-full bg-stone-900 flex flex-col shadow-xl transition-transform duration-300 lg:translate-x-0 border-r border-stone-800">

    <div class="flex items-center gap-3 px-5 py-5 border-b border-stone-800/80">
        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-souq-600/90 to-souq-700/90 text-white font-extrabold text-sm">E+</span>
        <div>
            <p class="text-white font-bold leading-tight">E-Souq Plus</p>
            <p class="text-[10px] text-stone-400 uppercase tracking-wider">Admin Panel</p>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        @php
            $links = [
                ['route' => 'admin.dashboard', 'url' => '/admin', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'admin.products', 'url' => '/admin/products', 'label' => 'Products', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
            ];
            $catalogLinks = [
                ['route' => 'admin.categories', 'url' => '/admin/categories', 'label' => 'Categories'],
                ['route' => 'admin.subcategories', 'url' => '/admin/sub-categories', 'label' => 'Sub Categories'],
                ['route' => 'admin.brands', 'url' => '/admin/brands', 'label' => 'Brands'],
            ];
            $bottomLinks = [
                ['route' => 'admin.orders', 'url' => '/admin/orders', 'label' => 'Orders', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ['route' => 'admin.customers', 'url' => '/admin/customers', 'label' => 'Customers', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
            ];
        @endphp

        @foreach($links as $link)
        <a href="{{ url($link['url']) }}"
           class="admin-sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-r-lg text-sm font-medium text-stone-300 hover:text-white hover:bg-stone-800/60 transition {{ request()->routeIs($link['route']) ? 'active' : '' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"/></svg>
            {{ $link['label'] }}
        </a>
        @endforeach

        <div class="pt-3 pb-1">
            <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-stone-500 mb-1">Catalog</p>
            @foreach($catalogLinks as $link)
            <a href="{{ url($link['url']) }}"
               class="admin-sidebar-link flex items-center gap-3 pl-8 pr-3 py-2 rounded-r-lg text-sm font-medium text-stone-400 hover:text-white hover:bg-stone-800/60 transition {{ request()->routeIs($link['route']) ? 'active' : '' }}">
                <span class="w-1.5 h-1.5 rounded-full bg-souq-500/70 shrink-0"></span>
                {{ $link['label'] }}
            </a>
            @endforeach
        </div>

        @foreach($bottomLinks as $link)
        <a href="{{ url($link['url']) }}"
           class="admin-sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-r-lg text-sm font-medium text-stone-300 hover:text-white hover:bg-stone-800/60 transition {{ request()->routeIs($link['route']) ? 'active' : '' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"/></svg>
            {{ $link['label'] }}
        </a>
        @endforeach

        <hr class="border-stone-800 my-3">

        <a href="{{ url('/') }}" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-stone-500 hover:text-white hover:bg-stone-800/60 transition">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            View Store
        </a>
    </nav>

    <div class="p-4 border-t border-stone-800/80">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 bg-souq-700/40 border border-souq-600/30 rounded-full flex items-center justify-center text-accent-400 font-bold text-sm">
                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                <p class="text-xs text-stone-400 truncate">{{ auth()->user()->email ?? '' }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="block w-full py-2 text-xs font-medium text-center text-stone-400 hover:text-white bg-stone-800/50 border border-stone-700 rounded-xl hover:border-stone-600 transition">
                Sign Out
            </button>
        </form>
    </div>
</aside>
