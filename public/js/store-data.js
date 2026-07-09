window.ESOUQ_STORE = {
    categories: [],
    brands: [],
    products: [],
    delivery: { fee: 25 },
    banners: [],
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
