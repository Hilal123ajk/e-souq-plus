<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'E-Souq Plus — Online Marketplace in Pakistan')</title>
    <meta name="description" content="@yield('meta_description', 'E-Souq Plus — Shop mobile accessories, furniture, home décor, electronics & more. Cash on delivery across Pakistan.')">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        souq: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                            950: '#2e1065',
                        },
                        accent: {
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                        },
                    },
                },
            },
        };
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    @stack('head')
</head>
<body class="bg-stone-50 text-stone-800 font-sans antialiased" x-data="toast()" x-cloak>

    @include('components.header')

    <main>
        @yield('content')
    </main>

    @include('components.footer')
    @include('components.cart-drawer')

    {{-- Toast --}}
    <div x-show="visible"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[100] bg-souq-900 text-white px-6 py-3 rounded-2xl shadow-2xl text-sm font-medium flex items-center gap-2 border border-souq-700">
        <svg class="w-5 h-5 text-accent-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span x-text="message"></span>
    </div>

    <script src="{{ asset('js/store-data.js') }}"></script>
    <script>
        window.ESOUQ_STORE.categories = @json($storeCatalogCategories ?? []);
        window.ESOUQ_STORE.products = @json($storeCatalogProducts ?? []);
        window.ESOUQ_STORE.brands = @json($storeCatalogBrands ?? []);
        window.ESOUQ_STORE.delivery = @json($storeDeliveryConfig ?? \App\Support\DeliveryPolicy::frontendConfig());
        window.ESOUQ_STORE.banners = @json($heroBanners ?? \App\Support\StaticHomeBanners::slides());
    </script>
    <script src="{{ asset('js/store-app.js') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>
