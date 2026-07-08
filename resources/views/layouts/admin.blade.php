<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — E-Souq Plus</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'] },
                    colors: {
                        souq: { 50:'#f5f3ff',100:'#ede9fe',200:'#ddd6fe',300:'#c4b5fd',400:'#a78bfa',500:'#8b5cf6',600:'#7c3aed',700:'#6d28d9',800:'#5b21b6',900:'#4c1d95',950:'#2e1065' },
                        accent: { 400:'#fb923c',500:'#f97316',600:'#ea580c' },
                    },
                },
            },
        };
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .admin-sidebar-link.active { background: rgba(124, 58, 237, 0.12); color: #fff; border-left: 3px solid #f97316; }
        .admin-sidebar-link { border-left: 3px solid transparent; }
    </style>
    @stack('head')
</head>
<body class="bg-stone-100 text-stone-800 font-sans antialiased" x-data x-cloak>

@unless($guest ?? false)
<div class="flex min-h-screen">
    @include('admin.partials.sidebar')

    <div class="flex-1 flex flex-col min-w-0 lg:ml-64">
        @include('admin.partials.topbar')

        <main class="flex-1 p-4 md:p-6 lg:p-8">
            @if (session('success'))
            <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

<div x-show="$store.adminUi.sidebarOpen" x-cloak @click="$store.adminUi.sidebarOpen = false" class="fixed inset-0 bg-stone-900/60 z-40 lg:hidden backdrop-blur-sm"></div>

<div x-show="$store.adminUi.toast.visible" x-transition
     :class="$store.adminUi.toast.type === 'error' ? 'bg-red-600' : 'bg-stone-800'"
     class="fixed bottom-6 right-6 z-[100] text-white px-5 py-3 rounded-xl shadow-xl text-sm font-medium">
    <span x-text="$store.adminUi.toast.message"></span>
</div>
@else
    @yield('content')
@endunless

@unless($guest ?? false)
@include('components.admin-confirm-dialog')
@endunless

<script src="{{ asset('js/admin-data.js') }}"></script>
<script src="{{ asset('js/admin-app.js') }}"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
@stack('scripts')
</body>
</html>
