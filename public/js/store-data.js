window.ESOUQ_STORE = {
    site: {
        name: 'E-Souq Plus',
        tagline: 'Your Marketplace for Everything',
        phone: '+971 50 123 4567',
        whatsapp: '+971 50 123 4567',
        email: 'hello@e-souq-plus.com',
    },

    delivery: { fee: 25 },

    banners: [],

    categories: [],

    brands: [],

    products: [],
};

ESOUQ_STORE.getProduct = function (slug) {
    return this.products.find(p => p.slug === slug);
};

ESOUQ_STORE.getCategory = function (slug) {
    for (const cat of this.categories) {
        if (cat.slug === slug) return { ...cat, isRoot: true, parent: null };
        const sub = (cat.subcategories || []).find(s => s.slug === slug);
        if (sub) return { ...sub, isRoot: false, parent: cat, name: sub.name };
    }
    return null;
};

ESOUQ_STORE.getProductsByCategory = function (slug, includeChildren = false) {
    const cat = this.getCategory(slug);
    if (!cat) return [];

    if (cat.isRoot && includeChildren) {
        const childSlugs = (cat.subcategories || []).map(s => s.slug);
        return this.products.filter(p => p.category === slug || p.parentCategory === slug || childSlugs.includes(p.category));
    }

    return this.products.filter(p => p.category === slug || (includeChildren && p.parentCategory === slug));
};

ESOUQ_STORE.formatPrice = function (amount) {
    return 'AED ' + Number(amount ?? 0).toLocaleString('en-AE');
};

ESOUQ_STORE.goBack = function (fallbackUrl) {
    const fallback = fallbackUrl || '/';
    try {
        const referrer = document.referrer;
        if (referrer && new URL(referrer).origin === window.location.origin) {
            window.history.back();
            return;
        }
    } catch (_) {}
    window.location.href = fallback;
};

ESOUQ_STORE.getFeaturedProducts = function (limit = 8) {
    return this.products.filter(p => p.featured).slice(0, limit);
};

ESOUQ_STORE.getNewArrivals = function (limit = 5) {
    return this.products.slice(0, limit);
};
