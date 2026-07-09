@extends('layouts.app')

@section('meta_title', 'About Us')
@section('meta_description', 'Learn about E-Souq Plus — Pakistan\'s growing online marketplace for mobile accessories, furniture, home décor, electronics and more.')

@section('content')
<x-page-hero title="About Us" subtitle="Pakistan's growing online marketplace for accessories, furniture & more." />

<section class="max-w-4xl mx-auto px-4 py-10 md:py-14">
    <p class="text-stone-600 leading-relaxed mb-6">
        <strong class="text-stone-900">E-Souq Plus</strong> is built for Pakistanis who want a single trusted place to shop online — from mobile accessories and electronics to furniture and home décor — without the hassle of unreliable sellers or fake products.
    </p>
    <p class="text-stone-600 leading-relaxed mb-6">
        We started with mobile accessories and are expanding into furniture, kitchenware, fashion, and more. Every category is curated so you get quality products at fair prices, delivered to your doorstep with cash on delivery.
    </p>

    <h2 class="text-xl font-bold text-stone-900 mt-10 mb-4">Why Shop With Us?</h2>
    <ul class="space-y-4 text-stone-600">
        @foreach([
            ['Quality Assured', 'Curated products from trusted suppliers — no cheap imitations.'],
            ['Cash on Delivery', 'Pay when your order arrives. No advance payment needed.'],
            ['Nationwide Delivery', 'We ship to Lahore, Karachi, Islamabad, and cities across Pakistan.'],
            ['Easy Returns', 'If a product does not match your requirements, you can return it. See our Returns policy.'],
        ] as [$title, $text])
        <li class="flex items-start gap-3 p-4 bg-white rounded-2xl border border-stone-200">
            <span class="w-8 h-8 bg-souq-100 text-souq-700 rounded-lg flex items-center justify-center shrink-0 font-bold text-sm">✓</span>
            <span><strong class="text-stone-900">{{ $title }}</strong> — {{ $text }}</span>
        </li>
        @endforeach
    </ul>

    <p class="text-stone-600 leading-relaxed mt-10">
        Thank you for choosing E-Souq Plus. We're committed to earning your trust with every order.
    </p>
</section>
@endsection
