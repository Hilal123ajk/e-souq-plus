# E-Souq Plus

Laravel online marketplace with a customer storefront and admin panel.

## Setup

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan db:seed
```

## Documentation

See [docs/README.md](docs/README.md) for admin panel, storefront, and deployment notes.

## Tests

```bash
php artisan test
```
