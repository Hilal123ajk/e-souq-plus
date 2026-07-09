<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — E-Souq Plus</title>

    <x-favicon />

    @vite(['resources/css/app.css'])
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
<script defer src="{{ asset('vendor/alpine/alpine.min.js') }}"></script>
@stack('scripts')
</body>
</html>
