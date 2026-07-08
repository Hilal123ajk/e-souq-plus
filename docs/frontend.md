# Storefront — Current Implementation

Store URL: `/` (e.g. `http://127.0.0.1:8000`)

Currency: **AED** (locale `en-AE`)

---

## Architecture Overview

The storefront is **database-driven**. On every page load, `AppServiceProvider` injects catalog data into the layout:

```javascript
window.ESOUQ_STORE.categories = [...];  // from DB
window.ESOUQ_STORE.products = [...];    // from DB
window.ESOUQ_STORE.brands = [...];      // from DB
window.ESOUQ_STORE.delivery = { fee: 25 };
```

**Service:** `App\Services\StoreCatalogService` transforms Eloquent models into JSON-friendly arrays for Alpine.js filtering, cart sync, and product lookups.

**Client scripts:**
- `public/js/store-data.js` — helpers (`formatPrice`, `getProduct`, `getCategory`, etc.)
- `public/js/store-app.js` — Alpine stores/components (cart, filters, checkout, hero slider)

Only **active** categories, brands, and products from the admin panel appear on the storefront.

---

## Pages & Routes

| Page | URL | Controller / View |
|------|-----|-------------------|
| Home | `/` | `Store\HomeController@index` → `home.blade.php` |
| All categories | `/categories` | `Store\HomeController@categories` → `categories/index.blade.php` |
| Category | `/categories/{slug}` | `Store\CategoryController@show` → `categories/show.blade.php` |
| Subcategory | `/categories/{parent}/{slug}` | `Store\CategoryController@showSubcategory` |
| All products | `/categories/all` | `Store\ProductController@index` → `products/index.blade.php` |
| Product detail | `/products/{slug}` | `Store\ProductController@show` → `products/show.blade.php` |
| Checkout | `/checkout` | `checkout.blade.php` |
| About | `/about-us` | `pages/about.blade.php` |
| Contact | `/contact-us` | `pages/contact.blade.php` |
| FAQs | `/faqs` | `pages/faqs.blade.php` |
| Shipping / Delivery | `/shipping-policy`, `/delivery-process` | `pages/shipping.blade.php` |
| Returns | `/returns-and-exchange` | `pages/returns.blade.php` |

---

## Home Page

**File:** `resources/views/home.blade.php`

### Hero carousel (bento grid, left side)
- Built from the **first 4 active root categories** in the database (ordered by title)
- Each slide uses the category title, description, image, and links to the category page
- Alpine.js `heroSlider()` handles auto-rotation

### Side promo cards (right side)
- **First card:** 5th active root category from DB (if exists)
- **Second card:** Links to all products
- Fallback when fewer than 5 categories: “All Categories” promo

### Browse categories grid
- All active root categories from DB
- Shows name, image (or letter placeholder if no image), product count
- Empty state when no categories exist

### Featured products
- Shuffled active products from DB (paginated, 8 per page)
- Server-rendered with add-to-cart buttons

### Promo strip
- Dynamic text listing category names from DB
- Shown only when categories exist

### New arrivals
- Latest 5 active products by `created_at`

---

## Header & Navigation

**File:** `resources/views/components/header.blade.php`

- Logo, search bar, cart button with item count
- **Category pills nav (desktop):** All Categories + every active root category + Hot Deals
- **Active state:** Highlights the current page (category, all categories index, or hot deals)
- **Mobile drawer:** Home, All Categories, All Products, all categories, support links
- Categories rendered server-side from `$storeCatalogCategories` (not hardcoded)

---

## Category Pages

**File:** `resources/views/categories/show.blade.php`

- Category hero with breadcrumb, title, product count
- Subcategory horizontal nav (when subcategories exist)
- Client-side product filtering via `productFilters()` Alpine component
- Sort: featured, price low/high, rating, discount
- **Empty state:** Contextual message when no products in category, with links to browse all products or other categories

---

## Product Listing

**File:** `resources/views/products/index.blade.php`

- Filters: search, category, brand, price range
- Mobile filter drawer
- Sort options
- **Empty states:** Contextual messages for brand/category/search filters

---

## Product Detail

**File:** `resources/views/products/show.blade.php`

- Loaded by slug via `ESOUQ_STORE.getProduct(slug)`
- Image gallery / variant thumbnails
- Quantity selector, Add to Cart, Buy Now (COD)
- Related products from same category
- “Product not found” state for invalid slugs

---

## Cart & Checkout

### Cart drawer
**File:** `resources/views/components/cart-drawer.blade.php`

- Alpine store `cart` in `store-app.js`
- Persisted in `localStorage` (`esouq_cart`)
- Syncs product data from `ESOUQ_STORE.products` on load
- Delivery fee from `ESOUQ_STORE.delivery.fee` (config: AED 25)

### Checkout
**File:** `resources/views/checkout.blade.php`

**Form fields:**
- First name, last name (required)
- Email (required)
- Phone (required, 9–15 digits)
- Address (required)
- City (text input, required)
- Country (text input, required, defaults to “United Arab Emirates”)
- Order notes (optional)
- Payment: Cash on Delivery only

**Validation:** Client-side in `checkoutForm()` Alpine component

**Current behavior:** Order placement is **simulated** — generates a client-side order number (e.g. `ESP-...`), clears cart, shows success screen. **No order is saved to the database.**

---

## Dynamic Catalog Rules

| Data | Source | Visibility rule |
|------|--------|-----------------|
| Root categories | `categories` where `parent_id IS NULL` | `is_active = true` |
| Subcategories | `categories` with `parent_id` | `is_active = true` |
| Brands (filters/nav) | `brands` | `is_active = true` AND has at least one active product |
| Products | `products` | `is_active = true` |
| Category product count | Includes products in subcategories | Descendant category IDs |
| Featured flag | `products.is_featured` | Used in sort/filter |

Changing categories, brands, or products in admin updates the storefront on the next page refresh.

---

## Empty States

Implemented across the storefront:

| Location | When shown |
|----------|------------|
| Home — categories | No active root categories |
| Home — featured products | No active products |
| Home — new arrivals | No recent products |
| Category page | No products in selected category/subcategory |
| Products index | No products match filters |
| Categories index | No categories, brands, or trending products |
| Product detail | Invalid product slug |
| Checkout | Empty cart |

---

## Static Assets

Local images under `public/images/`:

```
public/images/
├── banners/       # Hero carousel fallbacks (optional; DB images preferred)
├── categories/    # Category tile fallbacks (optional)
└── promo/         # Promo strip background
```

Category and product images uploaded via admin are served from `/storage/...`.

---

## Theme & UX

- **Colors:** Purple (`souq-*`) primary, orange (`accent-*`) highlights
- **Font:** Plus Jakarta Sans
- **Framework:** Tailwind CSS via CDN
- **Interactivity:** Alpine.js 3.x
- **Mobile:** Back navigation component, responsive grids, mobile filter drawer
- **Trust badges:** Quality, COD, fast delivery, easy returns on home page

---

## Not Yet on Storefront

- User accounts / login
- Real order submission to backend
- Order tracking
- Wishlist
- Product reviews/ratings (fields exist as placeholders in catalog JSON but are always 0)
- Newsletter backend (subscribe toast is client-side only)
- Server-side search (search uses client-side filter on loaded products)
