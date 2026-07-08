@props(['title', 'subtitle' => null, 'breadcrumb' => null])

<section class="relative overflow-hidden bg-gradient-to-br from-souq-700 via-souq-800 to-souq-950 text-white">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 right-0 w-96 h-96 bg-accent-400 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-souq-400 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 py-10 md:py-14">
        <nav class="text-xs text-souq-300 mb-4 flex items-center gap-2">
            <a href="{{ route('store.home') }}" class="hover:text-white transition">Home</a>
            <span>/</span>
            <span class="text-souq-100">{{ $breadcrumb ?? $title }}</span>
        </nav>
        <h1 class="text-3xl md:text-4xl font-extrabold mb-2">{{ $title }}</h1>
        @if ($subtitle)
            <p class="text-souq-200 text-sm md:text-base max-w-2xl">{{ $subtitle }}</p>
        @endif
    </div>
</section>
