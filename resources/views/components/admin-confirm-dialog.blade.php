<div x-data x-show="$store.adminConfirm.open" x-cloak @keydown.escape.window="$store.adminConfirm.cancel()"
     class="fixed inset-0 z-[100] flex items-center justify-center p-4" role="dialog">
    <div x-show="$store.adminConfirm.open" x-transition.opacity @click="$store.adminConfirm.cancel()" class="absolute inset-0 bg-souq-950/60 backdrop-blur-sm"></div>
    <div x-show="$store.adminConfirm.open" x-transition class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 border border-stone-100">
        <div class="w-12 h-12 rounded-full flex items-center justify-center mb-4" :class="$store.adminConfirm.tone === 'danger' ? 'bg-red-50' : 'bg-souq-50'">
            <svg class="w-6 h-6" :class="$store.adminConfirm.tone === 'danger' ? 'text-red-600' : 'text-souq-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h3 class="text-lg font-bold text-stone-900 mb-2" x-text="$store.adminConfirm.title"></h3>
        <p class="text-sm text-stone-500 mb-6" x-text="$store.adminConfirm.message"></p>
        <div class="flex gap-3">
            <button @click="$store.adminConfirm.cancel()" class="flex-1 py-3 border border-stone-200 rounded-xl text-sm font-semibold text-stone-700 hover:bg-stone-50" x-text="$store.adminConfirm.cancelLabel"></button>
            <button @click="$store.adminConfirm.confirm()" class="flex-1 py-3 rounded-xl text-sm font-bold text-white" :class="$store.adminConfirm.tone === 'danger' ? 'bg-red-600 hover:bg-red-700' : 'bg-souq-600 hover:bg-souq-700'" x-text="$store.adminConfirm.confirmLabel"></button>
        </div>
    </div>
</div>
