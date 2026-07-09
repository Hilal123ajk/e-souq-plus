@extends('layouts.app')

@section('title', 'Checkout — E-Souq Plus')

@section('content')
<x-mobile-back-nav :fallback="route('store.home')" />

<section class="max-w-7xl mx-auto px-4 py-6 md:py-10"
         x-data="checkoutForm()"
         data-order-success="{{ e(session('order_success.order_number') ?? '') }}"
         data-payment-cancelled="{{ request()->boolean('payment_cancelled') ? '1' : '0' }}"
         data-checkout-error="{{ e(session('checkout_error') ?? '') }}">
    <div x-show="placed" x-cloak class="max-w-lg mx-auto text-center py-16">
        <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1 class="text-2xl font-extrabold text-stone-900 mb-2">Order confirmed!</h1>
        <p class="text-stone-500 text-sm mb-2">Thank you! We'll contact you shortly to confirm delivery.</p>
        <p class="text-sm font-semibold text-souq-800 mb-6" x-show="orderNumber">Order: <span x-text="orderNumber"></span></p>
        <a href="{{ route('store.home') }}" class="inline-flex px-8 py-3 bg-souq-600 text-white font-bold rounded-full hover:bg-souq-700 transition">Continue Shopping</a>
    </div>

    <div x-show="!placed && $store.cart.items.length === 0" x-cloak class="text-center py-16 bg-white rounded-3xl border border-stone-200">
        <svg class="w-20 h-20 text-stone-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        <h2 class="text-lg font-semibold text-stone-700 mb-2">Nothing to checkout</h2>
        <p class="text-sm text-stone-500 mb-6">Add products to your cart first.</p>
        <a href="{{ route('store.home') }}" class="inline-flex px-8 py-3 bg-souq-600 text-white font-bold rounded-full hover:bg-souq-700 transition">Start Shopping</a>
    </div>

    <div x-show="!placed && $store.cart.items.length > 0" x-cloak>
        <h1 class="text-2xl md:text-3xl font-extrabold text-stone-900 mb-6">Checkout</h1>
        <div class="grid lg:grid-cols-5 gap-8">
            <div class="lg:col-span-3 space-y-6">
                <div class="bg-white rounded-3xl border border-stone-200 p-5 md:p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-stone-900 mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 bg-souq-600 text-white text-sm font-bold rounded-full flex items-center justify-center">1</span>
                        Delivery Details
                    </h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-stone-600 block mb-1">First Name <span class="text-red-500">*</span></label>
                            <input type="text" x-model="firstName" maxlength="100" class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500" :class="fieldErrors.firstName ? 'border-red-400 bg-red-50' : 'border-stone-200'">
                            <p x-show="fieldErrors.firstName" x-text="fieldErrors.firstName" class="text-xs text-red-600 mt-1"></p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-stone-600 block mb-1">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" x-model="lastName" maxlength="100" class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500" :class="fieldErrors.lastName ? 'border-red-400 bg-red-50' : 'border-stone-200'">
                            <p x-show="fieldErrors.lastName" x-text="fieldErrors.lastName" class="text-xs text-red-600 mt-1"></p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-stone-600 block mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" x-model="email" placeholder="you@example.com" class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500" :class="fieldErrors.email ? 'border-red-400 bg-red-50' : 'border-stone-200'">
                            <p x-show="fieldErrors.email" x-text="fieldErrors.email" class="text-xs text-red-600 mt-1"></p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-stone-600 block mb-1">Phone <span class="text-red-500">*</span></label>
                            <input type="tel" x-model="phone" placeholder="0501234567" class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500" :class="fieldErrors.phone ? 'border-red-400 bg-red-50' : 'border-stone-200'">
                            <p x-show="fieldErrors.phone" x-text="fieldErrors.phone" class="text-xs text-red-600 mt-1"></p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-stone-600 block mb-1">Address <span class="text-red-500">*</span></label>
                            <input type="text" x-model="address" placeholder="Street, building, area" class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500" :class="fieldErrors.address ? 'border-red-400 bg-red-50' : 'border-stone-200'">
                            <p x-show="fieldErrors.address" x-text="fieldErrors.address" class="text-xs text-red-600 mt-1"></p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-stone-600 block mb-1">City <span class="text-red-500">*</span></label>
                            <input type="text" x-model="city" placeholder="Dubai" class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500" :class="fieldErrors.city ? 'border-red-400 bg-red-50' : 'border-stone-200'">
                            <p x-show="fieldErrors.city" x-text="fieldErrors.city" class="text-xs text-red-600 mt-1"></p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-stone-600 block mb-1">Country <span class="text-red-500">*</span></label>
                            <input type="text" x-model="country" placeholder="United Arab Emirates" class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500" :class="fieldErrors.country ? 'border-red-400 bg-red-50' : 'border-stone-200'">
                            <p x-show="fieldErrors.country" x-text="fieldErrors.country" class="text-xs text-red-600 mt-1"></p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-stone-600 block mb-1">Order Notes <span class="text-stone-400">(optional)</span></label>
                            <textarea x-model="notes" rows="2" maxlength="1000" class="w-full px-3 py-2.5 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-souq-500 resize-none"></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-stone-200 p-5 md:p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-stone-900 mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 bg-souq-600 text-white text-sm font-bold rounded-full flex items-center justify-center">2</span>
                        Payment
                    </h2>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-4 border-2 rounded-2xl cursor-pointer transition"
                               :class="payment === 'stripe' ? 'border-souq-500 bg-souq-50' : 'border-stone-200 hover:border-stone-300'">
                            <input type="radio" x-model="payment" value="stripe" class="accent-souq-600">
                            <div class="flex-1">
                                <p class="font-semibold text-stone-900 text-sm">Pay securely with card</p>
                                <p class="text-xs text-stone-500">Redirected to Stripe for secure checkout</p>
                            </div>
                            <svg class="w-8 h-5 shrink-0" viewBox="0 0 60 25" aria-hidden="true"><text x="0" y="18" font-size="14" font-weight="700" fill="#635bff">stripe</text></svg>
                        </label>
                        <label class="flex items-center gap-3 p-4 border-2 rounded-2xl cursor-pointer transition"
                               :class="payment === 'cod' ? 'border-souq-500 bg-souq-50' : 'border-stone-200 hover:border-stone-300'">
                            <input type="radio" x-model="payment" value="cod" class="accent-souq-600">
                            <div>
                                <p class="font-semibold text-stone-900 text-sm">Cash on Delivery</p>
                                <p class="text-xs text-stone-500">Pay when your order arrives</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl border border-stone-200 p-5 md:p-6 sticky top-36 shadow-sm">
                    <h2 class="text-lg font-bold text-stone-900 mb-4">Order Summary</h2>
                    <div class="space-y-3 max-h-64 overflow-y-auto mb-4">
                        <template x-for="item in $store.cart.displayItems" :key="item.lineKey">
                            <div class="flex gap-3">
                                <div class="w-14 h-14 rounded-xl overflow-hidden bg-stone-50 shrink-0 border border-stone-100">
                                    <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-stone-800 line-clamp-2" x-text="item.name"></p>
                                    <p class="text-xs text-stone-500">Qty: <span x-text="item.quantity"></span></p>
                                </div>
                                <p class="text-sm font-bold text-souq-700 shrink-0" x-text="ESOUQ_STORE.formatPrice($store.cart.unitPrice(item) * item.quantity)"></p>
                            </div>
                        </template>
                    </div>
                    <hr class="border-stone-200 mb-4">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-stone-500">Subtotal</span><span x-text="ESOUQ_STORE.formatPrice($store.cart.total)"></span></div>
                        <div class="flex justify-between"><span class="text-stone-500">Delivery</span><span x-text="ESOUQ_STORE.formatPrice($store.cart.deliveryFee)"></span></div>
                        <hr class="border-stone-200">
                        <div class="flex justify-between text-base pt-1">
                            <span class="font-bold">Total</span>
                            <span class="font-extrabold text-souq-800" x-text="ESOUQ_STORE.formatPrice($store.cart.grandTotal)"></span>
                        </div>
                    </div>
                    <button type="button" @click="requestPlaceOrder()" :disabled="submitting"
                            class="w-full mt-5 py-3.5 bg-gradient-to-r from-souq-600 to-souq-700 hover:from-souq-700 hover:to-souq-800 disabled:from-stone-300 disabled:to-stone-300 text-white font-bold rounded-full transition">
                        <span x-show="!submitting" x-text="payment === 'stripe' ? 'Pay securely' : 'Place order'"></span>
                        <span x-show="submitting" x-text="payment === 'stripe' ? 'Redirecting to Stripe…' : 'Placing order…'"></span>
                    </button>
                    <p x-show="error" x-text="error" class="mt-3 text-sm text-red-600 text-center"></p>
                </div>
            </div>
        </div>
    </div>

    <div x-show="confirmOpen" x-cloak @keydown.escape.window="confirmOpen = false" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
        <div @click="confirmOpen = false" class="absolute inset-0 bg-stone-900/50 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg max-h-[90vh] flex flex-col overflow-hidden">
            <div class="px-6 pt-6 pb-4 border-b border-stone-100">
                <h3 class="text-lg font-bold text-stone-900">Confirm your order</h3>
                <p class="text-sm text-stone-500 mt-1">Review your items before placing the order.</p>
            </div>
            <div class="flex-1 overflow-y-auto px-6 py-4 space-y-3">
                <template x-for="item in $store.cart.displayItems" :key="item.lineKey">
                    <div class="flex gap-3 p-3 bg-stone-50 rounded-2xl border border-stone-100">
                        <div class="w-16 h-16 rounded-xl overflow-hidden bg-white border border-stone-200 shrink-0">
                            <img :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-stone-800 line-clamp-2" x-text="item.name"></p>
                            <p class="text-xs text-stone-500 mt-1">
                                <span x-text="ESOUQ_STORE.formatPrice($store.cart.unitPrice(item))"></span>
                                <span> × </span>
                                <span x-text="item.quantity"></span>
                            </p>
                        </div>
                        <p class="text-sm font-bold text-souq-700 shrink-0 self-center" x-text="ESOUQ_STORE.formatPrice($store.cart.unitPrice(item) * item.quantity)"></p>
                    </div>
                </template>
            </div>
            <div class="px-6 py-4 border-t border-stone-100 bg-stone-50 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-stone-500">Subtotal</span><span class="font-medium" x-text="ESOUQ_STORE.formatPrice($store.cart.total)"></span></div>
                <div class="flex justify-between"><span class="text-stone-500">Delivery</span><span class="font-medium" x-text="ESOUQ_STORE.formatPrice($store.cart.deliveryFee)"></span></div>
                <div class="flex justify-between text-base pt-1 border-t border-stone-200">
                    <span class="font-bold text-stone-900">Total</span>
                    <span class="font-extrabold text-souq-800" x-text="ESOUQ_STORE.formatPrice($store.cart.grandTotal)"></span>
                </div>
            </div>
            <div class="px-6 pb-6 pt-2 flex gap-3">
                <button @click="confirmOpen = false" class="flex-1 py-3 border border-stone-200 rounded-full text-sm font-semibold hover:bg-stone-50 transition">Go Back</button>
                <button @click="confirmPlaceOrder()" :disabled="submitting" class="flex-1 py-3 bg-souq-600 hover:bg-souq-700 disabled:bg-stone-300 text-white rounded-full text-sm font-bold transition"
                        x-text="payment === 'stripe' ? 'Continue to payment' : 'Confirm order'"></button>
            </div>
        </div>
    </div>
</section>
@endsection
