@extends('layouts.app')

@section('meta_title', 'FAQs')
@section('meta_description', 'Frequently asked questions about shopping at E-Souq Plus — delivery, cash on delivery, returns, and product authenticity.')

@section('content')
<x-page-hero title="Frequently Asked Questions" subtitle="Answers to common questions about shopping at E-Souq Plus." />

<section class="max-w-3xl mx-auto px-4 py-10 md:py-14">
    <div class="space-y-3" x-data="{ open: 0 }">
        @foreach ($faqs as $index => $faq)
        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-sm">
            <button type="button" @click="open = open === {{ $index }} ? null : {{ $index }}"
                    class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left hover:bg-stone-50 transition">
                <span class="font-semibold text-stone-900 text-sm md:text-base">{{ $faq['question'] }}</span>
                <svg class="w-5 h-5 text-stone-400 shrink-0 transition-transform" :class="open === {{ $index }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open === {{ $index }}" x-transition class="px-5 pb-5 text-stone-600 text-sm leading-relaxed border-t border-stone-100 pt-4">
                {{ $faq['answer'] }}
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-10 text-center">
        <p class="text-stone-500 text-sm mb-4">Still have questions?</p>
        <a href="{{ route('store.pages.contact') }}" class="inline-flex px-8 py-3 bg-souq-600 hover:bg-souq-700 text-white font-semibold rounded-full text-sm transition">Contact Us</a>
    </div>
</section>
@endsection
