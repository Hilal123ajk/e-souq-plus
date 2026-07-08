@extends('layouts.admin', ['guest' => true])

@section('title', 'Verify Login')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 bg-stone-50">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <x-brand-logo theme="admin-light" size="xl" class="items-center mx-auto" />
            <h1 class="text-xl font-bold text-stone-900 mt-4">Verify your login</h1>
            <p class="text-stone-500 text-sm mt-1">Enter the 5-digit code sent to {{ $maskedEmail }}. It may take a few seconds to arrive.</p>
        </div>

        @if (session('status'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-800">
            {{ session('status') }}
        </div>
        @endif

        <form method="POST" action="{{ route('admin.login.verify.submit') }}" class="bg-white rounded-2xl p-6 md:p-8 shadow-sm border border-stone-200 space-y-5">
            @csrf

            @if ($errors->any())
            <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                {{ $errors->first() }}
            </div>
            @endif

            <div>
                <label for="otp" class="text-xs font-semibold text-stone-600 block mb-1.5">Verification code</label>
                <input type="text" id="otp" name="otp" value="{{ old('otp') }}" required autofocus
                       inputmode="numeric" pattern="[0-9]{5}" maxlength="5" autocomplete="one-time-code"
                       placeholder="12345"
                       class="w-full px-4 py-3 border border-stone-200 rounded-xl text-center text-2xl tracking-[0.35em] font-bold text-stone-900 focus:outline-none focus:ring-2 focus:ring-souq-500 @error('otp') border-red-300 @enderror">
            </div>

            <button type="submit" class="w-full py-3 bg-souq-600 hover:bg-souq-700 text-white font-bold rounded-xl transition">
                Verify & Sign In
            </button>
        </form>

        <form method="POST" action="{{ route('admin.login.verify.resend') }}" class="mt-4 text-center">
            @csrf
            <button type="submit" class="text-sm font-semibold text-souq-600 hover:text-souq-700 transition">
                Resend code
            </button>
        </form>

        <p class="text-center mt-6">
            <a href="{{ route('admin.login') }}" class="text-sm text-stone-500 hover:text-souq-600 transition">&larr; Back to login</a>
        </p>
    </div>
</div>
@endsection
