document.addEventListener('alpine:init', () => {
    Alpine.store('adminUi', {
        sidebarOpen: false,
        toast: { visible: false, message: '', type: 'success' },
        scrollLockCount: 0,

        toggleSidebar() { this.sidebarOpen = !this.sidebarOpen; },

        notify(message, type = 'success') {
            this.toast = { visible: true, message, type };
            setTimeout(() => { this.toast.visible = false; }, 3000);
        },

        lockScroll() {
            this.scrollLockCount += 1;
            document.body.classList.add('overflow-hidden');
        },

        unlockScroll() {
            this.scrollLockCount = Math.max(0, this.scrollLockCount - 1);
            if (this.scrollLockCount === 0) document.body.classList.remove('overflow-hidden');
        },
    });

    Alpine.store('adminConfirm', {
        open: false, title: 'Are you sure?', message: '', confirmLabel: 'Confirm', cancelLabel: 'Cancel', tone: 'danger', onConfirm: null, form: null,

        ask(options = {}) {
            this.title = options.title ?? 'Are you sure?';
            this.message = options.message ?? '';
            this.confirmLabel = options.confirmLabel ?? 'Confirm';
            this.cancelLabel = options.cancelLabel ?? 'Cancel';
            this.tone = options.tone ?? 'danger';
            this.onConfirm = options.onConfirm ?? null;
            this.form = options.form ?? null;
            this.open = true;
            Alpine.store('adminUi').lockScroll();
        },

        cancel() {
            this.open = false;
            this.onConfirm = null;
            this.form = null;
            Alpine.store('adminUi').unlockScroll();
        },

        confirm() {
            const fn = this.onConfirm;
            const form = this.form;
            this.open = false;
            this.onConfirm = null;
            this.form = null;
            Alpine.store('adminUi').unlockScroll();
            if (form) form.submit();
            else if (typeof fn === 'function') fn();
        },
    });

    const drawerMixin = {
        openDrawer(flag) { this[flag] = true; Alpine.store('adminUi').lockScroll(); },
        closeDrawer(flag) {
            this[flag] = false;
            if (!this.hasOpenDrawer()) Alpine.store('adminUi').unlockScroll();
        },
    };

    Alpine.data('adminOrders', () => ({
        ...drawerMixin,
        search: '', statusFilter: '', menuOpenId: null, menuOrder: null, menuTop: 0, menuRight: 16,
        detailDrawerOpen: false, statusDrawerOpen: false, selectedOrder: null,
        statusForm: { status: '', message: '' },

        hasOpenDrawer() { return this.detailDrawerOpen || this.statusDrawerOpen; },
        closeAllDrawers() { this.detailDrawerOpen = false; this.statusDrawerOpen = false; this.closeMenu(); Alpine.store('adminUi').unlockScroll(); },
        closeMenu() { this.menuOpenId = null; this.menuOrder = null; },

        toggleMenu(id, event) {
            if (this.menuOpenId === id) { this.closeMenu(); return; }
            const rect = event.currentTarget.getBoundingClientRect();
            this.menuTop = rect.bottom + 4;
            this.menuRight = Math.max(16, window.innerWidth - rect.right);
            this.menuOpenId = id;
            this.menuOrder = ESOUQ_ADMIN.orders.find(o => o.id === id) ?? null;
        },

        get orders() {
            let list = [...ESOUQ_ADMIN.orders];
            if (this.search) {
                const q = this.search.toLowerCase();
                list = list.filter(o => o.id.toLowerCase().includes(q) || o.customer.toLowerCase().includes(q));
            }
            if (this.statusFilter) list = list.filter(o => o.status === this.statusFilter);
            return list;
        },

        openDetail(order) { this.closeMenu(); this.selectedOrder = order; this.statusDrawerOpen = false; this.openDrawer('detailDrawerOpen'); },
        openStatus(order) { this.closeMenu(); this.selectedOrder = order; this.statusForm = { status: order.status, message: '' }; this.detailDrawerOpen = false; this.openDrawer('statusDrawerOpen'); },

        submitStatus() {
            if (!this.selectedOrder) return;
            Alpine.store('adminUi').notify('Orders backend not connected yet');
            this.closeDrawer('statusDrawerOpen');
        },
    }));
});
