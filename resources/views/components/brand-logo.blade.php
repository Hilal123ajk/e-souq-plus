@props([
    'theme' => 'light',
    'size' => 'md',
    'tagline' => null,
    'href' => null,
])

@php
    $sizeClasses = [
        'sm' => 'text-base',
        'md' => 'text-xl',
        'lg' => 'text-2xl',
        'xl' => 'text-[1.75rem] md:text-3xl',
    ];

    [$mainClass, $plusClass] = match ($theme) {
        'dark' => ['text-white', 'text-accent-400'],
        'admin-light' => ['text-stone-900', 'text-accent-500'],
        default => ['text-souq-950', 'text-accent-500'],
    };

    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
    $taglineClass = $theme === 'dark'
        ? 'text-stone-400'
        : 'text-stone-500';
@endphp

@if ($href)
<a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex flex-col group']) }} aria-label="E-Souq Plus Home">
@else
<div {{ $attributes->merge(['class' => 'inline-flex flex-col']) }}>
@endif
    <span class="esouq-brand-logo {{ $sizeClass }} leading-none transition-opacity group-hover:opacity-90">
        <span class="{{ $mainClass }}">E-Souq</span><span class="{{ $plusClass }}"> Plus</span>
    </span>
    @if ($tagline)
        <span class="text-[10px] font-semibold uppercase tracking-[0.18em] mt-1.5 {{ $taglineClass }}">{{ $tagline }}</span>
    @endif
@if ($href)
</a>
@else
</div>
@endif
