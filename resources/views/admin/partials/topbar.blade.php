<header class="bg-white border-b border-stone-200 sticky top-0 z-30 shadow-sm">
    <div class="flex items-center justify-between px-4 md:px-6 py-3 md:py-4">
        <div class="flex items-center gap-3">
            <button @click="$store.adminUi.toggleSidebar()" class="lg:hidden p-2 -ml-2 text-stone-500 hover:text-souq-700 rounded-lg hover:bg-stone-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div>
                <h1 class="text-lg md:text-xl font-bold text-stone-900">@yield('page_title', 'Dashboard')</h1>
                @hasSection('page_subtitle')
                <p class="text-xs text-stone-500 hidden sm:block">@yield('page_subtitle')</p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2 md:gap-4">
            <span class="hidden md:inline text-xs text-stone-400">{{ now()->format('l, M j, Y') }}</span>
            @hasSection('header_action')
            @yield('header_action')
            @endif
        </div>
    </div>
</header>
