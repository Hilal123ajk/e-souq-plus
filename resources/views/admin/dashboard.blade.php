@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Manage your store')

@section('content')
<div class="grid grid-cols-2 md:grid-cols-4 gap-3">
    <a href="{{ route('admin.products') }}" class="flex items-center gap-3 p-4 bg-gradient-to-r from-souq-600 to-souq-700 text-white rounded-2xl hover:from-souq-700 hover:to-souq-800 transition shadow-md">
        <svg class="w-6 h-6 text-accent-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        <span class="text-sm font-semibold">Products</span>
    </a>
    <a href="{{ route('admin.categories') }}" class="flex items-center gap-3 p-4 bg-white border border-stone-200 rounded-2xl hover:shadow-md transition">
        <svg class="w-6 h-6 text-souq-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        <span class="text-sm font-semibold text-stone-800">Categories</span>
    </a>
    <a href="{{ route('admin.orders') }}" class="flex items-center gap-3 p-4 bg-white border border-stone-200 rounded-2xl hover:shadow-md transition">
        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <span class="text-sm font-semibold text-stone-800">Orders</span>
    </a>
    <a href="{{ route('store.home') }}" target="_blank" class="flex items-center gap-3 p-4 bg-white border border-stone-200 rounded-2xl hover:shadow-md transition">
        <svg class="w-6 h-6 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        <span class="text-sm font-semibold text-stone-800">View Store</span>
    </a>
</div>
@endsection
