@extends('layouts.admin')

@section('title', 'Customers')
@section('page_title', 'Customers')
@section('page_subtitle', 'Customers from checkout orders — grouped by phone')

@section('content')
<div x-data="{ search: '' }">
    <div class="bg-white rounded-2xl border border-stone-200 p-4 mb-6 shadow-sm">
        <input type="search" x-model="search" placeholder="Search by name or phone..." class="w-full sm:w-80 px-4 py-2.5 border border-stone-200 rounded-xl text-sm focus:ring-2 focus:ring-souq-500">
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-xs text-stone-500 uppercase">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold">Customer</th>
                        <th class="text-left px-5 py-3 font-semibold hidden md:table-cell">Phone</th>
                        <th class="text-left px-5 py-3 font-semibold hidden lg:table-cell">City</th>
                        <th class="text-left px-5 py-3 font-semibold">Orders</th>
                        <th class="text-left px-5 py-3 font-semibold">Total Spent</th>
                        <th class="text-left px-5 py-3 font-semibold hidden sm:table-cell">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <template x-for="customer in ESOUQ_ADMIN.customers.filter(c => !search || c.name.toLowerCase().includes(search.toLowerCase()) || c.phone.includes(search))" :key="customer.id">
                        <tr class="hover:bg-stone-50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-gradient-to-br from-souq-600 to-souq-800 text-accent-300 rounded-full flex items-center justify-center font-bold text-sm shrink-0" x-text="customer.name.charAt(0)"></div>
                                    <div>
                                        <p class="font-semibold text-stone-900" x-text="customer.name"></p>
                                        <p class="text-xs text-stone-400 md:hidden" x-text="customer.phone"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 hidden md:table-cell text-stone-600" x-text="customer.phone"></td>
                            <td class="px-5 py-3 hidden lg:table-cell text-stone-600" x-text="customer.city"></td>
                            <td class="px-5 py-3 font-medium" x-text="customer.orders"></td>
                            <td class="px-5 py-3 font-semibold text-souq-700" x-text="ESOUQ_ADMIN.formatPrice(customer.spent)"></td>
                            <td class="px-5 py-3 hidden sm:table-cell text-xs text-stone-500" x-text="ESOUQ_ADMIN.formatDate(customer.joined)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
