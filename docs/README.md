# E-Souq Plus — Project Documentation

E-Souq Plus is a Laravel-based online marketplace for the UAE (currency: **AED**). The project has a customer-facing storefront and a protected admin panel for catalog management.

## Documentation Index

| Document | Description |
|----------|-------------|
| [Admin Panel](./admin.md) | Authentication, catalog CRUD, UI, and backend services |
| [Storefront](./frontend.md) | Customer-facing pages, cart, checkout, and dynamic catalog |

## Tech Stack

- **Backend:** Laravel (PHP)
- **Database:** MySQL
- **Storefront UI:** Blade templates, Tailwind CSS (CDN), Alpine.js
- **Admin UI:** Blade templates, Tailwind CSS (CDN), Alpine.js, drawer-based forms
- **File storage:** Laravel `public` disk (`storage/app/public` → `/storage/...`)
- **Queue:** Database driver (for admin OTP emails)

## Project Structure (Key Paths)

```
e-souq-plus/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/          # Admin CRUD + auth
│   │   └── Store/          # Storefront page controllers
│   ├── Models/             # Brand, Category, Product, ProductImage, User
│   ├── Services/           # StoreCatalogService, AdminOtpService, OrderService
│   ├── Support/            # DeliveryPolicy
│   └── Jobs/               # SendAdminLoginOtp
├── config/esouq.php        # Delivery fee, OTP settings
├── database/migrations/    # Catalog + admin auth tables
├── database/seeders/       # UserSeeder
├── public/
│   ├── js/store-data.js    # Client helpers (formatPrice, getProduct, etc.)
│   ├── js/store-app.js     # Cart, filters, checkout, hero slider
│   └── images/             # Local banner/category/promo assets
├── resources/views/
│   ├── admin/              # Admin panel views
│   ├── categories/         # Store category pages
│   ├── products/           # Store product pages
│   ├── pages/              # About, contact, FAQs, shipping
│   └── components/         # Header, footer, cart drawer, etc.
└── routes/web.php          # Store + admin routes
```

## Initial Setup

```bash
composer install
cp .env.example .env   # configure DB, mail, queue
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan db:seed --class=UserSeeder
php artisan queue:work database                   # required for OTP emails
php artisan serve
```

### Seeded Admin Accounts

Set credentials in `.env` before running `php artisan db:seed --class=UserSeeder`:

| Variable | Purpose |
|----------|---------|
| `ADMIN_SEED_EMAIL` | Admin login email |
| `ADMIN_SEED_PASSWORD` | Admin login password |
| `MANAGER_SEED_EMAIL` | Manager login email |
| `MANAGER_SEED_PASSWORD` | Manager login password |

Do **not** commit real passwords to git. Use strong unique values locally and in production.

Admin login: `/admin/login`

## Configuration

| Setting | File | Default |
|---------|------|---------|
| Standard delivery fee (AED) | `config/esouq.php` → `standard_delivery_fee` | 25 |
| OTP validity window | `config/esouq.php` → `admin_otp_valid_days` | 7 days |
| OTP code expiry | `config/esouq.php` → `admin_otp_expiry_minutes` | 15 min |
| Queue driver | `.env` → `QUEUE_CONNECTION` | Should be `database` for OTP |

## What Is Complete vs Pending

### Done

- Full admin catalog CRUD (brands, categories, subcategories, products)
- Admin login with email OTP (Mailtrap-compatible)
- DB-backed storefront catalog (categories, brands, products, subcategories)
- Home page hero carousel and category sections driven by admin data
- Product listing, filtering, category pages with empty states
- Client-side cart (localStorage) and checkout form UI
- Static content pages (about, contact, FAQs, shipping, returns)

### Not Yet Implemented

- **Customers:** Admin customers page is UI-only
- **Dashboard analytics:** Stats/recent orders not wired to the database
- **Customer accounts:** No user registration/login on the storefront
- **Payment gateways:** Cash on Delivery only

## Related Docs

- [Admin Panel](./admin.md)
- [Storefront](./frontend.md)
