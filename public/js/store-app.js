document.addEventListener('alpine:init', () => {
    Alpine.store('cart', {
        items: [],
        drawerOpen: false,

        init() {
            this.items = this.loadItems();
            this.syncFromCatalog();
        },

        loadItems() {
            try {
                return JSON.parse(localStorage.getItem('esouq_cart') || '[]')
                    .filter(item => item && typeof item.id === 'number')
                    .map(item => ({
                        lineKey: item.lineKey ?? `${item.id}-${item.variantImageId ?? 'default'}`,
                        id: item.id,
                        quantity: Math.min(99, Math.max(1, parseInt(item.quantity, 10) || 1)),
                        variantImageId: item.variantImageId ?? null,
                        variantLabel: item.variantLabel ?? null,
                    }));
            } catch {
                return [];
            }
        },

        catalogProduct(item) {
            return window.ESOUQ_STORE?.products?.find(entry => entry.id === item.id) ?? null;
        },

        unitPrice(item) {
            const product = this.catalogProduct(item);
            return product ? Number(product.price) : 0;
        },

        displayItem(item) {
            const product = this.catalogProduct(item);
            if (!product) return null;

            const variant = item.variantImageId
                ? (product.gallery || []).find(entry => entry.id === item.variantImageId)
                : null;

            return {
                ...item,
                slug: product.slug,
                name: product.name,
                brand: product.brand,
                category: product.category ?? '',
                price: Number(product.price),
                image: variant?.url ?? product.image,
                variantLabel: variant?.label ?? item.variantLabel ?? 'Main',
                inStock: product.inStock,
            };
        },

        get displayItems() {
            return this.items.map(item => this.displayItem(item)).filter(Boolean);
        },

        get count() {
            return this.items.reduce((sum, item) => sum + item.quantity, 0);
        },

        get total() {
            return this.items.reduce((sum, item) => sum + this.unitPrice(item) * item.quantity, 0);
        },

        get deliveryFee() {
            if (this.total === 0) return 0;
            return Number(window.ESOUQ_STORE.delivery?.fee ?? 25);
        },

        get grandTotal() {
            return this.total + this.deliveryFee;
        },

        syncFromCatalog() {
            if (!window.ESOUQ_STORE?.products?.length) return;

            const synced = [];
            for (const item of this.items) {
                const product = this.catalogProduct(item);
                if (!product || !product.inStock) continue;
                synced.push({
                    lineKey: item.lineKey,
                    id: item.id,
                    quantity: Math.min(99, Math.max(1, parseInt(item.quantity, 10) || 1)),
                    variantImageId: item.variantImageId ?? null,
                    variantLabel: item.variantLabel ?? null,
                });
            }
            this.items = synced;
            this.save();
        },

        save() {
            localStorage.setItem('esouq_cart', JSON.stringify(
                this.items.map(({ lineKey, id, quantity, variantImageId, variantLabel }) => ({
                    lineKey, id, quantity, variantImageId, variantLabel,
                })),
            ));
        },

        openDrawer() {
            this.drawerOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        closeDrawer() {
            this.drawerOpen = false;
            document.body.classList.remove('overflow-hidden');
        },

        buildLineKey(productId, variantImageId = null) {
            return `${productId}-${variantImageId ?? 'default'}`;
        },

        add(product, quantity = 1, openDrawer = true, variant = null) {
            const resolvedVariant = variant ?? { id: null, url: product.image, label: 'Main' };
            const variantImageId = resolvedVariant.id ?? null;
            const variantLabel = resolvedVariant.label ?? null;
            const lineKey = this.buildLineKey(product.id, variantImageId);
            const existing = this.items.find(item => item.lineKey === lineKey);

            if (existing) {
                existing.quantity = Math.min(99, existing.quantity + quantity);
            } else {
                this.items.push({
                    lineKey, id: product.id,
                    quantity: Math.min(99, Math.max(1, quantity)),
                    variantImageId, variantLabel,
                });
            }

            this.save();
            this.showToast(`${product.name} added to cart`);
            if (openDrawer) this.openDrawer();
        },

        remove(lineKey) {
            this.items = this.items.filter(item => item.lineKey !== lineKey);
            this.save();
        },

        updateQuantity(lineKey, quantity) {
            const item = this.items.find(entry => entry.lineKey === lineKey);
            if (!item) return;
            if (quantity <= 0) {
                this.remove(lineKey);
            } else {
                item.quantity = Math.min(99, Math.max(1, parseInt(quantity, 10) || 1));
                this.save();
            }
        },

        clear() {
            this.items = [];
            this.save();
        },

        showToast(message) {
            window.dispatchEvent(new CustomEvent('esouq-toast', { detail: { message } }));
        },
    });

    Alpine.data('productFilters', () => ({
        search: '',
        category: '',
        rootCategory: '',
        includeChildProducts: false,
        brand: '',
        minPrice: 0,
        maxPrice: 150000,
        sort: 'featured',
        mobileFiltersOpen: false,

        get filteredProducts() {
            let products = [...window.ESOUQ_STORE.products];

            if (this.includeChildProducts && this.rootCategory) {
                products = products.filter(p =>
                    p.parentCategory === this.rootCategory || p.category === this.rootCategory
                );
            } else if (this.category) {
                products = products.filter(p => p.category === this.category);
            }
            if (this.brand) {
                products = products.filter(p => p.brand === this.brand);
            }
            if (this.search) {
                const q = this.search.toLowerCase();
                products = products.filter(p =>
                    p.name.toLowerCase().includes(q) || p.brand.toLowerCase().includes(q)
                );
            }
            products = products.filter(p => p.price >= this.minPrice && p.price <= this.maxPrice);

            switch (this.sort) {
                case 'price-low': products.sort((a, b) => a.price - b.price); break;
                case 'price-high': products.sort((a, b) => b.price - a.price); break;
                case 'rating': products.sort((a, b) => b.rating - a.rating); break;
                case 'discount': products.sort((a, b) => (b.discount || 0) - (a.discount || 0)); break;
                default: products.sort((a, b) => (b.featured ? 1 : 0) - (a.featured ? 1 : 0));
            }

            return products;
        },

        get emptyMessage() {
            if (this.brand && (this.category || this.rootCategory)) {
                return `No products found for ${this.brand} in this category.`;
            }
            if (this.brand) {
                return `No products found for ${this.brand}.`;
            }
            if (this.category || this.rootCategory) {
                return 'No products in this category yet.';
            }
            if (this.search) {
                return 'No products match your search.';
            }
            return 'No products match your filters.';
        },

        resetFilters() {
            this.search = '';
            this.category = '';
            this.rootCategory = '';
            this.includeChildProducts = false;
            this.brand = '';
            this.minPrice = 0;
            this.maxPrice = 150000;
            this.sort = 'featured';
        },
    }));

    Alpine.data('heroSlider', () => ({
        current: 0,
        banners: window.ESOUQ_STORE.banners?.length ? window.ESOUQ_STORE.banners : [],
        interval: null,

        init() {
            if (this.banners.length === 0) return;
            this.interval = setInterval(() => this.next(), 6000);
        },

        next() {
            if (this.banners.length === 0) return;
            this.current = (this.current + 1) % this.banners.length;
        },

        prev() {
            if (this.banners.length === 0) return;
            this.current = (this.current - 1 + this.banners.length) % this.banners.length;
        },

        goTo(index) {
            this.current = index;
        },
    }));

    Alpine.data('toast', () => ({
        visible: false,
        message: '',

        init() {
            window.addEventListener('esouq-toast', (e) => {
                this.message = e.detail.message;
                this.visible = true;
                setTimeout(() => { this.visible = false; }, 3000);
            });
        },
    }));

    Alpine.data('newsletterForm', () => ({
        email: '',
        loading: false,

        subscribe() {
            if (this.loading || !this.email) return;
            this.loading = true;
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('esouq-toast', {
                    detail: { message: 'Thanks for subscribing! You\'ll hear from us soon.' },
                }));
                this.email = '';
                this.loading = false;
            }, 600);
        },
    }));

    Alpine.data('checkoutForm', () => ({
        firstName: '',
        lastName: '',
        email: '',
        phone: '',
        address: '',
        city: localStorage.getItem('esouq_city') || '',
        country: localStorage.getItem('esouq_country') || 'United Arab Emirates',
        notes: '',
        payment: 'stripe',
        placed: false,
        orderNumber: '',
        submitting: false,
        confirmOpen: false,
        error: '',
        fieldErrors: {},

        init() {
            const orderSuccess = this.$el.dataset.orderSuccess;
            if (orderSuccess) {
                this.placed = true;
                this.orderNumber = orderSuccess;
                this.$store.cart.clear();
            }

            if (this.$el.dataset.paymentCancelled === '1') {
                this.error = 'Payment was cancelled. You can try again or choose Cash on Delivery.';
            }

            const checkoutError = this.$el.dataset.checkoutError;
            if (checkoutError) {
                this.error = checkoutError;
            }
        },

        sanitizeInput(value) {
            if (typeof value !== 'string') return '';
            return value.trim().replace(/[<>]/g, '');
        },

        normalizePhone(value) {
            return String(value || '').replace(/\D+/g, '');
        },

        validateForm() {
            this.fieldErrors = {};
            this.error = '';

            this.firstName = this.sanitizeInput(this.firstName);
            this.lastName = this.sanitizeInput(this.lastName);
            this.email = this.sanitizeInput(this.email);
            this.address = this.sanitizeInput(this.address);
            this.city = this.sanitizeInput(this.city);
            this.country = this.sanitizeInput(this.country);
            this.notes = this.sanitizeInput(this.notes);
            this.phone = this.normalizePhone(this.phone);

            if (this.firstName.length < 2) this.fieldErrors.firstName = 'First name is required (at least 2 characters).';
            if (this.lastName.length < 2) this.fieldErrors.lastName = 'Last name is required (at least 2 characters).';
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email)) this.fieldErrors.email = 'Enter a valid email address.';
            if (this.phone.length < 9 || this.phone.length > 15) this.fieldErrors.phone = 'Enter a valid phone number (9–15 digits).';
            if (this.address.length < 5) this.fieldErrors.address = 'Address is required (at least 5 characters).';
            if (this.city.length < 2) this.fieldErrors.city = 'City is required.';
            if (this.country.length < 2) this.fieldErrors.country = 'Country is required.';

            return Object.keys(this.fieldErrors).length === 0 && !this.error;
        },

        requestPlaceOrder() {
            if (this.$store.cart.items.length === 0 || this.submitting) return;
            if (!this.validateForm()) {
                this.error = this.error || 'Please fix the highlighted fields before placing your order.';
                return;
            }
            this.confirmOpen = true;
        },

        confirmPlaceOrder() {
            this.confirmOpen = false;
            this.placeOrder();
        },

        async placeOrder() {
            if (this.$store.cart.items.length === 0 || this.submitting) return;
            if (!this.validateForm()) return;

            this.submitting = true;
            this.error = '';

            localStorage.setItem('esouq_city', this.city);
            localStorage.setItem('esouq_country', this.country);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const payload = {
                first_name: this.firstName,
                last_name: this.lastName,
                email: this.email,
                phone: this.phone,
                address: this.address,
                city: this.city,
                country: this.country,
                notes: this.notes,
                items: this.$store.cart.items.map(item => ({
                    product_id: item.id,
                    quantity: item.quantity,
                    variant_image_id: item.variantImageId,
                    variant_label: item.variantLabel,
                })),
            };

            const endpoint = this.payment === 'stripe' ? '/checkout/stripe' : '/orders';
            const body = this.payment === 'cod'
                ? { ...payload, payment_method: 'cod' }
                : payload;

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(body),
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        const messages = Object.values(data.errors).flat();
                        this.error = messages[0] || 'Please check your order details and try again.';
                    } else if (response.status === 429) {
                        this.error = 'Too many order attempts. Please wait a minute and try again.';
                    } else {
                        this.error = data.message || 'Unable to place your order. Please try again.';
                    }
                    return;
                }

                if (this.payment === 'stripe' && data.checkout_url) {
                    window.location.href = data.checkout_url;
                    return;
                }

                this.orderNumber = data.order_number;
                this.placed = true;
                this.$store.cart.clear();
            } catch {
                this.error = 'Network error. Please check your connection and try again.';
            } finally {
                if (this.payment !== 'stripe') {
                    this.submitting = false;
                }
            }
        },
    }));
});
