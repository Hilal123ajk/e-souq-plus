@extends('layouts.app')

@section('content')
<x-page-hero title="Returns & Exchange" subtitle="A simple, customer-friendly return policy." />

<section class="max-w-4xl mx-auto px-4 py-10 md:py-14">
    <div class="bg-gradient-to-br from-souq-50 to-accent-50 border border-souq-200 rounded-3xl p-6 md:p-8 mb-10">
        <h2 class="text-xl font-bold text-stone-900 mb-3">Our Policy Is Simple</h2>
        <p class="text-stone-700 leading-relaxed">
            If the product does not match your requirements, <strong>you can return it</strong>. Whether the colour is wrong, the fit is off, or you changed your mind — contact us and we'll help with a return or exchange.
        </p>
    </div>

    <h2 class="text-lg font-bold text-stone-900 mb-4">When Can I Return?</h2>
    <ul class="space-y-3 text-stone-600 mb-10">
        @foreach(['Product does not match your expectations.', 'You received a damaged, defective, or wrong item.', 'You want to exchange for a different variant (colour or model).'] as $item)
        <li class="flex items-start gap-3"><span class="text-souq-500 shrink-0">✓</span><span>{{ $item }}</span></li>
        @endforeach
    </ul>

    <h2 class="text-lg font-bold text-stone-900 mb-4">How to Request a Return</h2>
    <ol class="list-decimal list-inside space-y-3 text-stone-600 mb-10">
        <li>Contact us on <a href="https://wa.me/923001234567" target="_blank" rel="noopener" class="text-souq-600 hover:underline">WhatsApp</a> or call +92 300 1234567.</li>
        <li>Share your order number, phone number, and reason for return.</li>
        <li>Our team confirms eligibility and guides you through next steps.</li>
        <li>Once we receive the product (if applicable), we process refund or replacement.</li>
    </ol>

    <h2 class="text-lg font-bold text-stone-900 mb-4">Conditions</h2>
    <ul class="space-y-3 text-stone-600 mb-10 text-sm">
        <li>• Products should be in original condition with tags and packaging where possible.</li>
        <li>• Opened screen protectors or hygiene items may be handled case by case.</li>
        <li>• COD refunds are processed via bank transfer or JazzCash/EasyPaisa as agreed.</li>
    </ul>

    <p class="text-stone-600 text-sm">
        <a href="{{ route('store.pages.contact') }}" class="text-souq-600 hover:underline">Contact Us</a> ·
        <a href="{{ route('store.pages.faqs') }}" class="text-souq-600 hover:underline">FAQs</a>
    </p>
</section>
@endsection
