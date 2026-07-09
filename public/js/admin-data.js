window.ESOUQ_ADMIN = {
    orders: [],
    customers: [],

    statusColors: {
        pending: 'bg-amber-100 text-amber-800',
        processing: 'bg-blue-100 text-blue-800',
        shipped: 'bg-indigo-100 text-indigo-800',
        delivered: 'bg-emerald-100 text-emerald-800',
        cancelled: 'bg-red-100 text-red-800',
    },

    formatPrice(amount) {
        return 'AED ' + Number(amount ?? 0).toLocaleString('en-AE');
    },

    formatDate(dateStr) {
        return new Date(dateStr).toLocaleDateString('en-AE', { day: 'numeric', month: 'short', year: 'numeric' });
    },
};
