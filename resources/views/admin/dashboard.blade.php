@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Overview of your store performance')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6 mb-6 md:mb-8">
    <div class="bg-white rounded-2xl border border-stone-200 p-5 shadow-sm border-l-4 border-l-souq-500">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold text-stone-500 uppercase tracking-wide">Total Orders</span>
            <div class="w-9 h-9 bg-souq-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-souq-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-extrabold text-stone-900" x-text="ESOUQ_ADMIN.stats.totalOrders"></p>
        <p class="text-xs text-stone-500 mt-1"><span x-text="ESOUQ_ADMIN.stats.pendingOrders"></span> pending · <span x-text="ESOUQ_ADMIN.stats.deliveredOrders"></span> delivered</p>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 p-5 shadow-sm border-l-4 border-l-accent-500">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold text-stone-500 uppercase tracking-wide">Customers</span>
            <div class="w-9 h-9 bg-accent-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-extrabold text-stone-900" x-text="ESOUQ_ADMIN.stats.totalCustomers"></p>
        <p class="text-xs text-stone-500 mt-1">Unique checkout customers</p>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 p-5 shadow-sm border-l-4 border-l-emerald-500">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold text-stone-500 uppercase tracking-wide">Revenue</span>
            <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-extrabold text-stone-900" x-text="ESOUQ_ADMIN.formatPrice(ESOUQ_ADMIN.stats.completedRevenue)"></p>
        <p class="text-xs text-stone-500 mt-1"><span x-text="ESOUQ_ADMIN.stats.completedOrderCount"></span> delivered orders</p>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
    <a href="{{ url('/admin/products') }}" class="flex items-center gap-3 p-4 bg-gradient-to-r from-souq-600 to-souq-700 text-white rounded-2xl hover:from-souq-700 hover:to-souq-800 transition shadow-md">
        <svg class="w-6 h-6 text-accent-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        <span class="text-sm font-semibold">Add Product</span>
    </a>
    <a href="{{ url('/admin/categories') }}" class="flex items-center gap-3 p-4 bg-white border border-stone-200 rounded-2xl hover:shadow-md transition">
        <svg class="w-6 h-6 text-souq-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        <span class="text-sm font-semibold text-stone-800">Add Category</span>
    </a>
    <a href="{{ url('/admin/orders') }}" class="flex items-center gap-3 p-4 bg-white border border-stone-200 rounded-2xl hover:shadow-md transition">
        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <span class="text-sm font-semibold text-stone-800">View Orders</span>
    </a>
    <a href="{{ url('/') }}" target="_blank" class="flex items-center gap-3 p-4 bg-white border border-stone-200 rounded-2xl hover:shadow-md transition">
        <svg class="w-6 h-6 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        <span class="text-sm font-semibold text-stone-800">View Store</span>
    </a>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
            <h2 class="font-bold text-stone-900">Recent Orders</h2>
            <a href="{{ url('/admin/orders') }}" class="text-xs font-semibold text-souq-600 hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-xs text-stone-500 uppercase">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold">Order</th>
                        <th class="text-left px-5 py-3 font-semibold hidden sm:table-cell">Customer</th>
                        <th class="text-left px-5 py-3 font-semibold">Total</th>
                        <th class="text-left px-5 py-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <template x-for="order in ESOUQ_ADMIN.orders.slice(0, 5)" :key="order.id">
                        <tr class="hover:bg-stone-50">
                            <td class="px-5 py-3 font-semibold text-stone-900" x-text="order.id"></td>
                            <td class="px-5 py-3 hidden sm:table-cell text-stone-600" x-text="order.customer"></td>
                            <td class="px-5 py-3 font-medium" x-text="ESOUQ_ADMIN.formatPrice(order.total)"></td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold capitalize" :class="ESOUQ_ADMIN.statusColors[order.status]" x-text="order.status"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-stone-200 p-5 shadow-sm">
            <h2 class="font-bold text-stone-900 mb-4 flex items-center gap-2">
                <span class="w-2 h-2 bg-accent-500 rounded-full"></span> Activity Log
            </h2>
            <div class="space-y-3 max-h-72 overflow-y-auto">
                <template x-for="entry in ESOUQ_ADMIN.activity" :key="entry.id">
                    <div class="flex gap-3 text-sm border-b border-stone-50 pb-3 last:border-0">
                        <div class="w-8 h-8 rounded-lg shrink-0 flex items-center justify-center bg-souq-100 text-souq-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-stone-800 leading-snug text-xs" x-text="entry.description"></p>
                            <p class="text-[10px] text-stone-400 mt-1"><span x-text="entry.user"></span> · <span x-text="entry.created_at_human"></span></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-stone-200 p-5 shadow-sm">
            <h2 class="font-bold text-stone-900 mb-4">Bestsellers</h2>
            <div class="space-y-3">
                <template x-for="(item, i) in ESOUQ_ADMIN.bestsellers" :key="i">
                    <div class="flex items-start gap-3 text-sm">
                        <span class="w-6 h-6 bg-souq-100 text-souq-700 rounded-lg flex items-center justify-center text-xs font-bold shrink-0" x-text="i + 1"></span>
                        <div>
                            <p class="font-medium text-stone-800 line-clamp-2 text-xs" x-text="item.name"></p>
                            <p class="text-[10px] text-stone-500"><span x-text="item.sold"></span> sold</p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
