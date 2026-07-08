@extends('layouts.admin', ['guest' => true])

@section('title', 'Admin Login')

@section('content')
<div class="min-h-screen flex">
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-stone-800 via-stone-900 to-stone-950 p-12 flex-col justify-between relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 right-0 w-96 h-96 bg-souq-500 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-accent-500 rounded-full blur-3xl"></div>
        </div>
        <div class="relative">
            <div class="flex items-center gap-2">
                <span class="w-10 h-10 rounded-xl bg-accent-500 text-white font-extrabold flex items-center justify-center">E+</span>
                <span class="text-white font-bold text-xl">E-Souq Plus</span>
            </div>
        </div>
        <div class="relative">
            <h2 class="text-3xl font-extrabold text-white mb-3">Manage your marketplace</h2>
            <p class="text-stone-300 text-sm leading-relaxed max-w-md">Products, orders, categories, and customers — all in one admin panel.</p>
        </div>
        <p class="relative text-stone-500 text-xs">&copy; {{ date('Y') }} E-Souq Plus</p>
    </div>

    <div class="flex-1 flex items-center justify-center p-6 bg-stone-50">
        <div class="w-full max-w-md">
            <div class="lg:hidden text-center mb-8">
                <span class="inline-flex items-center gap-2">
                    <span class="w-10 h-10 rounded-xl bg-souq-600 text-white font-extrabold flex items-center justify-center">E+</span>
                    <span class="font-bold text-xl text-stone-900">E-Souq Plus Admin</span>
                </span>
            </div>

            <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-stone-200">
                <h1 class="text-xl font-extrabold text-stone-900 mb-1">Welcome back</h1>
                <p class="text-stone-500 text-sm mb-6">Sign in to manage your store</p>

                @if (session('status'))
                <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-800">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4">
                    @csrf

                    @if ($errors->any())
                    <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                    @endif

                    <div>
                        <label for="email" class="text-xs font-semibold text-stone-600 block mb-1.5">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full px-4 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500 @error('email') border-red-300 @enderror">
                    </div>
                    <div>
                        <label for="password" class="text-xs font-semibold text-stone-600 block mb-1.5">Password</label>
                        <input type="password" id="password" name="password" required
                               class="w-full px-4 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500">
                    </div>
                    <label class="flex items-center gap-2 text-sm text-stone-600">
                        <input type="checkbox" name="remember" value="1" class="rounded accent-souq-600" {{ old('remember') ? 'checked' : '' }}>
                        Remember me
                    </label>
                    <button type="submit" class="w-full py-3 bg-souq-600 hover:bg-souq-700 text-white font-bold rounded-xl transition shadow-md">
                        Sign In
                    </button>
                </form>
            </div>

            <p class="text-center mt-6">
                <a href="{{ url('/') }}" class="text-sm text-stone-500 hover:text-souq-600 transition">&larr; Back to store</a>
            </p>
        </div>
    </div>
</div>
@endsection
