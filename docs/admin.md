# Admin Panel — Current Implementation

Admin URL prefix: `/admin`

## Authentication

### Flow

1. User submits email + password at `/admin/login`
2. Credentials are validated against the `users` table (`role` must be `admin` or `manager`)
3. A 6-digit OTP is queued via `SendAdminLoginOtp` job and emailed through Mailtrap (or configured mail driver)
4. User enters OTP at `/admin/login/verify`
5. On success, `admin_otp_verified_at` is set on the user and a session is created
6. Subsequent requests pass through `AdminMiddleware` until OTP verification expires

### Key Files

| File | Purpose |
|------|---------|
| `app/Http/Controllers/Admin/AuthController.php` | Login, OTP verify, resend, logout |
| `app/Services/AdminOtpService.php` | OTP generation and validation |
| `app/Jobs/SendAdminLoginOtp.php` | Queued email dispatch |
| `app/Mail/AdminLoginOtpMail.php` | OTP email template |
| `app/Http/Middleware/AdminMiddleware.php` | Protects admin routes |
| `resources/views/admin/login.blade.php` | Login form |
| `resources/views/admin/verify-otp.blade.php` | OTP entry form |

### Requirements

- Set `QUEUE_CONNECTION=database` in `.env`
- Run `php artisan queue:work database` so OTP emails are sent
- Configure mail credentials (e.g. Mailtrap) in `.env`

---

## Dashboard

**Route:** `GET /admin` → `admin.dashboard`

Static dashboard view with summary placeholders. No live stats wired to the database yet.

---

## Catalog Management (Fully Functional)

All catalog entities are stored in MySQL and served to the storefront via `StoreCatalogService`.

### Brands

**Route:** `/admin/brands`

| Action | Method | Route name |
|--------|--------|------------|
| List / manage | GET | `admin.brands` |
| Create | POST | `admin.brands.store` |
| Update | PUT | `admin.brands.update` |
| Delete | DELETE | `admin.brands.destroy` |

**Fields:** title, slug (auto), description, image, meta keywords, is_active

**Controller:** `App\Http\Controllers\Admin\BrandController`

---

### Categories (Root)

**Route:** `/admin/categories`

| Action | Method | Route name |
|--------|--------|------------|
| List / manage | GET | `admin.categories` |
| Create | POST | `admin.categories.store` |
| Update | PUT | `admin.categories.update` |
| Soft delete | DELETE | `admin.categories.destroy` |
| Restore | POST | `admin.categories.restore` |
| Force delete | DELETE | `admin.categories.force-destroy` |

**Fields:** title, slug (auto), description, image, meta keywords, is_active

**Features:**
- Drawer-based create/edit UI (Alpine.js)
- Soft deletes with trash/restore
- Image upload to `storage/app/public`

**Controller:** `App\Http\Controllers\Admin\CategoryController`

---

### Sub-Categories

**Route:** `/admin/sub-categories`

Same CRUD pattern as categories, but each record has a `parent_id` linking to a root category.

**Controller:** `App\Http\Controllers\Admin\SubCategoryController`

Subcategories appear on the storefront as a horizontal nav bar on the parent category page.

---

### Products

**Route:** `/admin/products`

| Action | Method | Route name |
|--------|--------|------------|
| List / manage | GET | `admin.products` |
| Create | POST | `admin.products.store` |
| Update | PUT | `admin.products.update` |
| Soft delete | DELETE | `admin.products.destroy` |
| Restore | POST | `admin.products.restore` |
| Force delete | DELETE | `admin.products.force-destroy` |

**Fields:**
- name, slug (auto), description, SKU
- category_id (subcategory or root), brand_id
- price, cost_price, stock_quantity
- main image, gallery images (`product_images` table)
- is_active, is_featured, has_variants
- meta keywords

**Features:**
- Filter by category, brand, status
- Variant/color support via `has_variants` + multiple `product_images`
- Soft deletes with trash/restore
- Form validation via dedicated Request classes

**Controller:** `App\Http\Controllers\Admin\ProductController`

---

## Admin UI

- **Layout:** `resources/views/layouts/admin.blade.php`
- **Sidebar:** `resources/views/admin/partials/sidebar.blade.php`
- **Theme:** Purple/souq accent with orange highlights (stone-900 sidebar base)
- **Patterns:** Alpine.js drawers for create/edit, confirm dialogs for delete
- **Static mock data removed:** Admin no longer loads `store-data.js`; all catalog data comes from the database

---

## Orders & Customers (UI Only)

| Page | Route | Status |
|------|-------|--------|
| Orders | `/admin/orders` | Static placeholder view |
| Customers | `/admin/customers` | Static placeholder view |

No backend models, migrations, or controllers exist for orders or customers yet.

---

## Database Schema (Catalog)

### `categories`
- `parent_id` (nullable — null = root category)
- `title`, `slug`, `description`, `image_url`, `meta_keywords`, `is_active`
- Soft deletes

### `brands`
- `title`, `slug`, `description`, `image_url`, `meta_keywords`, `is_active`

### `products`
- `category_id`, `brand_id`, `name`, `slug`, `description`, `sku`
- `price`, `cost_price`, `stock_quantity`, `image_url`
- `is_active`, `is_featured`, `has_variants`, `meta_keywords`
- Soft deletes

### `product_images`
- `product_id`, `image_url`, `label` (used for variants/gallery)

### `users` (admin auth extensions)
- `role` (`admin` | `manager`)
- `admin_otp_verified_at`

---

## Seeders

| Seeder | Purpose |
|--------|---------|
| `UserSeeder` | Creates admin and manager accounts |
| `StoreCategorySeeder` | Optional: seeds sample root categories with images (Carpets, Artificial Jewelry, Perfumes, Traditional Things, Stones and Rings, Mobile Accessories) |

Run individually:
```bash
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=StoreCategorySeeder
```

---

## Image Uploads

- Uploads stored under `storage/app/public/`
- Served via `/storage/...` after `php artisan storage:link`
- Models use `HasPublicStorageImage` trait for public URL resolution

**If images do not load:** run `php artisan storage:link` on every new machine/deploy. Uploaded files are not in git — only paths in the database. On WAMP, set `APP_URL` to your full site URL (e.g. `http://localhost/e-souq-plus/public`).

---

## Activity Logging

`App\Services\ActivityLogService` exists as a stub (no-op). No `activity_logs` table yet. Admin controllers call it but nothing is persisted.
