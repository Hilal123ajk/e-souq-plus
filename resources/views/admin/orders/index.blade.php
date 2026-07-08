@extends('layouts.admin')

@section('title', 'Orders')
@section('page_title', 'Orders')
@section('page_subtitle', 'Track and manage customer orders')

@section('content')
<div x-data="adminOrders()" @click.outside="closeMenu()" @keydown.escape.window="closeMenu()">
    <div class="bg-white rounded-2xl border border-stone-200 p-4 mb-6 flex flex-col sm:flex-row gap-3 shadow-sm">
        <input type="search" x-model="search" placeholder="Search order ID or customer..." class="flex-1 px-4 py-2.5 border border-stone-200 rounded-xl text-sm focus:ring-2 focus:ring-souq-500">
        <select x-model="statusFilter" class="px-4 py-2.5 border border-stone-200 rounded-xl text-sm bg-white">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="shipped">Shipped</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <div x-show="!hasOrders" class="bg-white rounded-2xl border border-stone-200 p-12 text-center shadow-sm">
        <svg class="w-16 h-16 text-stone-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <h3 class="text-lg font-semibold text-stone-700 mb-1">No orders yet</h3>
        <p class="text-sm text-stone-500">Orders placed from checkout will appear here.</p>
    </div>

    <div x-show="hasOrders && orders.length === 0" class="bg-white rounded-2xl border border-stone-200 p-12 text-center shadow-sm">
        <h3 class="text-lg font-semibold text-stone-700 mb-1">No matching orders</h3>
        <p class="text-sm text-stone-500">Try adjusting your search or status filter.</p>
    </div>

    <div x-show="orders.length > 0" class="bg-white rounded-2xl border border-stone-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 text-xs text-stone-500 uppercase">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold">Order ID</th>
                        <th class="text-left px-5 py-3 font-semibold">Customer</th>
                        <th class="text-left px-5 py-3 font-semibold hidden md:table-cell">City</th>
                        <th class="text-left px-5 py-3 font-semibold hidden sm:table-cell">Items</th>
                        <th class="text-left px-5 py-3 font-semibold">Total</th>
                        <th class="text-left px-5 py-3 font-semibold">Status</th>
                        <th class="text-left px-5 py-3 font-semibold hidden lg:table-cell">Date</th>
                        <th class="text-right px-5 py-3 w-12"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <template x-for="order in orders" :key="order.id">
                        <tr class="hover:bg-stone-50">
                            <td class="px-5 py-3"><button @click="openDetail(order)" class="font-semibold text-souq-700 hover:underline" x-text="order.id"></button></td>
                            <td class="px-5 py-3"><p class="font-medium" x-text="order.customer"></p><p class="text-xs text-stone-400" x-text="order.phone"></p></td>
                            <td class="px-5 py-3 hidden md:table-cell text-stone-600" x-text="order.city"></td>
                            <td class="px-5 py-3 hidden sm:table-cell" x-text="order.items"></td>
                            <td class="px-5 py-3 font-semibold" x-text="ESOUQ_ADMIN.formatPrice(order.total)"></td>
                            <td class="px-5 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold capitalize" :class="ESOUQ_ADMIN.statusColors[order.status]" x-text="order.status"></span></td>
                            <td class="px-5 py-3 hidden lg:table-cell text-xs text-stone-500" x-text="ESOUQ_ADMIN.formatDate(order.createdAt)"></td>
                            <td class="px-5 py-3 text-right">
                                <button @click.stop="toggleMenu(order.id, $event)" class="p-2 text-stone-400 hover:bg-stone-100 rounded-lg"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg></button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="menuOpenId && menuOrder" x-cloak class="fixed w-48 bg-white border border-stone-200 rounded-xl shadow-lg py-1 z-[100]" :style="`top: ${menuTop}px; right: ${menuRight}px`">
        <button @click="openDetail(menuOrder)" class="w-full px-4 py-2.5 text-sm text-stone-700 hover:bg-stone-50 text-left">View Detail</button>
        <button @click="openStatus(menuOrder)" class="w-full px-4 py-2.5 text-sm text-stone-700 hover:bg-stone-50 text-left">Update Status</button>
    </div>

    {{-- Detail drawer --}}
    <div x-show="detailDrawerOpen" x-cloak class="fixed inset-0 z-[60]">
        <div @click="closeAllDrawers()" class="absolute inset-0 bg-stone-900/50"></div>
        <div class="absolute right-0 top-0 bottom-0 w-full max-w-lg bg-white shadow-2xl flex flex-col">
            <div class="px-5 py-4 border-b flex justify-between items-center">
                <div><h2 class="font-bold">Order Detail</h2><p class="text-xs text-stone-500" x-text="selectedOrder?.id"></p></div>
                <button @click="closeAllDrawers()" class="p-2 text-stone-400">✕</button>
            </div>
            <template x-if="selectedOrder">
                <div class="flex-1 overflow-y-auto p-5 space-y-5">
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold capitalize" :class="ESOUQ_ADMIN.statusColors[selectedOrder.status]" x-text="selectedOrder.status"></span>
                    <div>
                        <h3 class="text-xs font-bold text-stone-500 uppercase mb-2">Customer</h3>
                        <dl class="space-y-1 text-sm">
                            <div class="flex justify-between"><dt class="text-stone-500">Name</dt><dd class="font-medium" x-text="selectedOrder.customer"></dd></div>
                            <div class="flex justify-between"><dt class="text-stone-500">Email</dt><dd x-text="selectedOrder.email"></dd></div>
                            <div class="flex justify-between"><dt class="text-stone-500">Phone</dt><dd x-text="selectedOrder.phone"></dd></div>
                            <div class="flex justify-between"><dt class="text-stone-500">Address</dt><dd class="text-right max-w-[60%]" x-text="selectedOrder.address"></dd></div>
                            <div class="flex justify-between"><dt class="text-stone-500">City</dt><dd x-text="selectedOrder.city"></dd></div>
                            <div class="flex justify-between"><dt class="text-stone-500">Country</dt><dd x-text="selectedOrder.country"></dd></div>
                        </dl>
                    </div>
                    <div x-show="selectedOrder.notes">
                        <h3 class="text-xs font-bold text-stone-500 uppercase mb-2">Notes</h3>
                        <p class="text-sm text-stone-600" x-text="selectedOrder.notes"></p>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-stone-500 uppercase mb-2">Items</h3>
                        <template x-for="(item, i) in selectedOrder.lineItems" :key="i">
                            <div class="flex justify-between py-2 border-b border-stone-100 text-sm">
                                <span x-text="item.name + ' × ' + item.qty"></span>
                                <span class="font-semibold" x-text="ESOUQ_ADMIN.formatPrice(item.price * item.qty)"></span>
                            </div>
                        </template>
                    </div>
                    <div class="flex justify-between font-bold text-lg pt-2 border-t">
                        <span>Total</span><span class="text-souq-700" x-text="ESOUQ_ADMIN.formatPrice(selectedOrder.total)"></span>
                    </div>
                    <div class="text-xs text-stone-500 space-y-1">
                        <div class="flex justify-between"><span>Subtotal</span><span x-text="ESOUQ_ADMIN.formatPrice(selectedOrder.subtotal)"></span></div>
                        <div class="flex justify-between"><span>Delivery</span><span x-text="ESOUQ_ADMIN.formatPrice(selectedOrder.deliveryFee)"></span></div>
                    </div>
                    <button @click="openStatus(selectedOrder)" class="w-full py-3 bg-souq-600 text-white rounded-xl font-semibold text-sm">Update Status</button>
                </div>
            </template>
        </div>
    </div>

    {{-- Status drawer --}}
    <div x-show="statusDrawerOpen" x-cloak class="fixed inset-0 z-[60]">
        <div @click="closeDrawer('statusDrawerOpen')" class="absolute inset-0 bg-stone-900/50"></div>
        <div class="absolute right-0 top-0 bottom-0 w-full max-w-sm bg-white shadow-2xl flex flex-col">
            <div class="px-5 py-4 border-b"><h2 class="font-bold">Update Status</h2></div>
            <div class="p-5 space-y-4 flex-1">
                <select x-model="statusForm.status" class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm">
                    <option value="pending">Pending</option><option value="processing">Processing</option>
                    <option value="shipped">Shipped</option><option value="delivered">Delivered</option><option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="p-5 border-t flex gap-3">
                <button @click="closeDrawer('statusDrawerOpen')" class="flex-1 py-3 border rounded-xl text-sm font-semibold">Cancel</button>
                <button @click="submitStatus()" class="flex-1 py-3 bg-souq-600 text-white rounded-xl text-sm font-bold">Update</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.ESOUQ_ADMIN.orders = @json($orders);
</script>
@endpush
