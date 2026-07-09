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

                    {{-- Customer card --}}
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4 md:p-5">
                        <h3 class="text-xs font-bold text-stone-500 uppercase tracking-wide mb-3">Customer Details</h3>
                        <dl class="grid grid-cols-1 gap-2.5 text-sm">
                            <div class="flex justify-between gap-4">
                                <dt class="text-stone-500 shrink-0">Name</dt>
                                <dd class="font-semibold text-stone-900 text-right" x-text="selectedOrder.customer"></dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-stone-500 shrink-0">Email</dt>
                                <dd class="text-stone-800 text-right break-all" x-text="selectedOrder.email"></dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-stone-500 shrink-0">Phone</dt>
                                <dd class="text-stone-800 text-right" x-text="selectedOrder.phone"></dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-stone-500 shrink-0">Address</dt>
                                <dd class="text-stone-800 text-right max-w-[65%]" x-text="selectedOrder.address"></dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-stone-500 shrink-0">City</dt>
                                <dd class="text-stone-800 text-right" x-text="selectedOrder.city"></dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-stone-500 shrink-0">Country</dt>
                                <dd class="text-stone-800 text-right" x-text="selectedOrder.country"></dd>
                            </div>
                        </dl>
                        <div x-show="selectedOrder.notes" class="mt-4 pt-4 border-t border-stone-200">
                            <p class="text-xs font-bold text-stone-500 uppercase mb-1">Notes</p>
                            <p class="text-sm text-stone-600" x-text="selectedOrder.notes"></p>
                        </div>
                    </div>

                    {{-- Order items --}}
                    <div>
                        <h3 class="text-xs font-bold text-stone-500 uppercase tracking-wide mb-3">Order Items</h3>
                        <div class="space-y-3">
                            <template x-for="(item, i) in selectedOrder.lineItems" :key="i">
                                <div class="flex items-center gap-3 p-3 rounded-2xl border border-stone-200 bg-white shadow-sm">
                                    <div class="w-16 h-16 rounded-xl overflow-hidden bg-stone-100 border border-stone-200 shrink-0">
                                        <img :src="item.image || ''" :alt="item.name" class="w-full h-full object-cover" x-show="item.image">
                                        <div x-show="!item.image" class="w-full h-full flex items-center justify-center text-stone-300">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-stone-900 line-clamp-2" x-text="item.name"></p>
                                        <p x-show="item.variantLabel" class="text-xs text-stone-500 mt-0.5" x-text="item.variantLabel"></p>
                                        <p class="text-xs text-stone-500 mt-1">
                                            <span x-text="ESOUQ_ADMIN.formatPrice(item.price)"></span> each
                                        </p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-xs text-stone-500">Qty <span class="font-semibold text-stone-700" x-text="item.qty"></span></p>
                                        <p class="text-sm font-bold text-souq-700 mt-1" x-text="ESOUQ_ADMIN.formatPrice(item.lineTotal ?? (item.price * item.qty))"></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Payment --}}
                    <div x-show="selectedOrder.paymentMethod === 'stripe'" class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
                        <h3 class="text-xs font-bold text-stone-500 uppercase tracking-wide mb-3">Payment</h3>
                        <dl class="grid grid-cols-1 gap-2.5 text-sm mb-4">
                            <div class="flex justify-between gap-4">
                                <dt class="text-stone-500 shrink-0">Method</dt>
                                <dd class="font-semibold text-stone-900 text-right capitalize" x-text="selectedOrder.paymentMethod === 'stripe' ? 'Card (Stripe)' : selectedOrder.paymentMethod"></dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-stone-500 shrink-0">Status</dt>
                                <dd class="font-semibold text-right capitalize" :class="selectedOrder.paymentStatus === 'paid' ? 'text-emerald-700' : (selectedOrder.paymentStatus === 'failed' ? 'text-red-600' : 'text-amber-700')" x-text="selectedOrder.paymentStatus || 'unpaid'"></dd>
                            </div>
                            <div x-show="selectedOrder.paidAt" class="flex justify-between gap-4">
                                <dt class="text-stone-500 shrink-0">Paid at</dt>
                                <dd class="text-stone-800 text-right" x-text="ESOUQ_ADMIN.formatDate(selectedOrder.paidAt)"></dd>
                            </div>
                            <div x-show="selectedOrder.stripePaymentIntentId" class="flex justify-between gap-4">
                                <dt class="text-stone-500 shrink-0">Payment intent</dt>
                                <dd class="text-xs text-stone-600 text-right break-all font-mono" x-text="selectedOrder.stripePaymentIntentId"></dd>
                            </div>
                        </dl>

                        <div x-show="selectedOrder.paymentEvents && selectedOrder.paymentEvents.length > 0">
                            <p class="text-xs font-bold text-stone-500 uppercase tracking-wide mb-2">Transaction history</p>
                            <div class="space-y-2">
                                <template x-for="(event, i) in selectedOrder.paymentEvents" :key="i">
                                    <div class="rounded-xl border border-stone-100 bg-stone-50 px-3 py-2.5 text-xs">
                                        <div class="flex justify-between gap-2 mb-1">
                                            <span class="font-semibold text-stone-800 capitalize" x-text="event.eventType.replaceAll('_', ' ')"></span>
                                            <span class="text-stone-400 shrink-0" x-text="ESOUQ_ADMIN.formatDate(event.createdAt)"></span>
                                        </div>
                                        <p x-show="event.status" class="text-stone-600">Status: <span class="font-medium" x-text="event.status"></span></p>
                                        <p x-show="event.amount != null" class="text-stone-600">Amount: <span class="font-medium" x-text="event.amount + ' ' + (event.currency || '').toUpperCase()"></span></p>
                                        <p x-show="event.sessionId" class="text-stone-500 break-all font-mono mt-1" x-text="'Session: ' + event.sessionId"></p>
                                        <p x-show="event.paymentIntentId" class="text-stone-500 break-all font-mono" x-text="'Intent: ' + event.paymentIntentId"></p>
                                        <p x-show="event.failureMessage" class="text-red-600 mt-1" x-text="event.failureMessage"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Totals --}}
                    <div class="rounded-2xl border border-stone-200 bg-white p-4 space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-stone-500">Subtotal</span><span class="font-medium" x-text="ESOUQ_ADMIN.formatPrice(selectedOrder.subtotal)"></span></div>
                        <div class="flex justify-between"><span class="text-stone-500">Delivery</span><span class="font-medium" x-text="ESOUQ_ADMIN.formatPrice(selectedOrder.deliveryFee)"></span></div>
                        <div class="flex justify-between text-base pt-2 border-t border-stone-200">
                            <span class="font-bold text-stone-900">Total</span>
                            <span class="font-extrabold text-souq-700" x-text="ESOUQ_ADMIN.formatPrice(selectedOrder.total)"></span>
                        </div>
                    </div>

                    <button @click="openStatus(selectedOrder)" class="w-full py-3 bg-souq-600 text-white rounded-xl font-semibold text-sm hover:bg-souq-700 transition">Update Status</button>
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
