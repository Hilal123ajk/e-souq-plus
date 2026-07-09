@extends('layouts.app')

@section('meta_title', 'Contact Us')
@section('meta_description', 'Contact E-Souq Plus by phone, WhatsApp, or email. We help with orders, products, and delivery across Pakistan.')

@section('content')
<x-page-hero title="Contact Us" subtitle="We're here to help with orders, products, and delivery questions." />

<section class="max-w-4xl mx-auto px-4 py-10 md:py-14">
    <p class="text-stone-600 leading-relaxed mb-10">
        Have a question about an order, product, or delivery? Reach out through any channel below. Our team responds on WhatsApp and phone during business hours.
    </p>

    <div class="grid gap-6 md:grid-cols-3">
        <div class="bg-white rounded-3xl border border-stone-200 p-6 shadow-sm hover:border-souq-300 transition">
            <div class="w-12 h-12 bg-souq-100 rounded-2xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-souq-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            </div>
            <h2 class="text-lg font-bold text-stone-900 mb-2">Phone</h2>
            <a href="tel:+923001234567" class="text-souq-600 font-semibold hover:underline">+92 300 1234567</a>
            <p class="text-sm text-stone-500 mt-2">Call or SMS for order updates.</p>
        </div>

        <div class="bg-white rounded-3xl border border-stone-200 p-6 shadow-sm hover:border-emerald-300 transition">
            <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <h2 class="text-lg font-bold text-stone-900 mb-2">WhatsApp</h2>
            <a href="https://wa.me/923001234567" target="_blank" rel="noopener" class="text-emerald-600 font-semibold hover:underline">+92 300 1234567</a>
            <p class="text-sm text-stone-500 mt-2">Fastest way to reach us.</p>
        </div>

        <div class="bg-white rounded-3xl border border-stone-200 p-6 shadow-sm hover:border-accent-300 transition">
            <div class="w-12 h-12 bg-accent-100 rounded-2xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <h2 class="text-lg font-bold text-stone-900 mb-2">Email</h2>
            <a href="mailto:hello@e-souq-plus.com" class="text-accent-600 font-semibold hover:underline break-all">hello@e-souq-plus.com</a>
            <p class="text-sm text-stone-500 mt-2">For detailed inquiries.</p>
        </div>
    </div>

    <div class="mt-12 bg-gradient-to-br from-souq-50 to-white rounded-3xl border border-souq-200 p-6 md:p-8">
        <h2 class="text-lg font-bold text-stone-900 mb-3">Before You Contact Us</h2>
        <p class="text-stone-600 text-sm leading-relaxed">
            Many questions are answered on our <a href="{{ route('store.pages.faqs') }}" class="text-souq-600 hover:underline font-medium">FAQs</a> and
            <a href="{{ route('store.pages.shipping') }}" class="text-souq-600 hover:underline font-medium">Delivery Process</a> pages.
            When messaging about an order, include your order number and checkout phone number.
        </p>
    </div>
</section>
@endsection
