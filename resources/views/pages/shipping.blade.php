@extends('layouts.app')

@section('content')
<x-page-hero title="Delivery Process" subtitle="How we ship your order across Pakistan — step by step." breadcrumb="Delivery" />

<section class="max-w-4xl mx-auto px-4 py-10 md:py-14">
    <p class="text-stone-600 leading-relaxed mb-10">
        E-Souq Plus delivers nationwide across Pakistan. Here's exactly what happens from the moment you place an order until it arrives at your door.
    </p>

    {{-- Steps --}}
    <div class="space-y-4 mb-12">
        @foreach([
            ['1', 'Order Placed', 'You checkout with your name, phone, and full address. No account needed — we confirm via phone or WhatsApp.'],
            ['2', 'Order Confirmed', 'Our team verifies your order within a few hours. If anything needs clarification, we\'ll contact you.'],
            ['3', 'Packed & Dispatched', 'Your items are carefully packed and handed to our courier partner within 1–2 business days.'],
            ['4', 'Out for Delivery', 'The courier delivers to your address. You pay the total (products + delivery fee) in cash on delivery.'],
        ] as [$step, $title, $desc])
        <div class="flex gap-4 p-5 bg-white rounded-2xl border border-stone-200">
            <span class="w-10 h-10 bg-souq-600 text-white font-bold rounded-full flex items-center justify-center shrink-0">{{ $step }}</span>
            <div>
                <h3 class="font-bold text-stone-900">{{ $title }}</h3>
                <p class="text-sm text-stone-600 mt-1">{{ $desc }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <h2 class="text-lg font-bold text-stone-900 mb-4">Delivery Areas</h2>
    <div class="flex flex-wrap gap-2 mb-8">
        @foreach (['Lahore', 'Karachi', 'Islamabad', 'Rawalpindi', 'Faisalabad', 'Multan', 'Sialkot', 'Gujranwala'] as $city)
        <span class="px-4 py-2 bg-souq-50 text-souq-800 text-sm font-medium rounded-full border border-souq-200">{{ $city }}</span>
        @endforeach
    </div>

    <h2 class="text-lg font-bold text-stone-900 mb-4">Delivery Fee</h2>
    <p class="text-stone-600 leading-relaxed mb-8">
        A flat delivery fee of <strong class="text-souq-800">AED 25</strong> applies to all orders. Free delivery on orders above AED 500. The fee is shown at checkout before you confirm.
    </p>

    <h2 class="text-lg font-bold text-stone-900 mb-4">Processing & Delivery Time</h2>
    <ul class="space-y-3 text-stone-600 mb-8">
        <li class="flex items-start gap-3"><span class="text-souq-500">✓</span><span><strong class="text-stone-800">Processing:</strong> 1–2 business days after confirmation.</span></li>
        <li class="flex items-start gap-3"><span class="text-souq-500">✓</span><span><strong class="text-stone-800">Major cities:</strong> 2–4 business days after dispatch.</span></li>
        <li class="flex items-start gap-3"><span class="text-souq-500">✓</span><span><strong class="text-stone-800">Other areas:</strong> 4–7 business days depending on location.</span></li>
    </ul>

    <h2 class="text-lg font-bold text-stone-900 mb-4">Cash on Delivery</h2>
    <p class="text-stone-600 leading-relaxed mb-8">
        All orders use <strong class="text-stone-800">Cash on Delivery (COD)</strong>. Pay the courier when your package arrives — no advance payment required.
    </p>

    <p class="text-stone-600 text-sm">
        See also: <a href="{{ route('store.pages.returns') }}" class="text-souq-600 hover:underline">Returns & Exchange</a> ·
        <a href="{{ route('store.pages.faqs') }}" class="text-souq-600 hover:underline">FAQs</a> ·
        <a href="{{ route('store.pages.contact') }}" class="text-souq-600 hover:underline">Contact Us</a>
    </p>
</section>
@endsection
