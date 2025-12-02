# Flash‑Sale Checkout API – Dev Branch

This branch tracks the in‑progress implementation for the flash‑sale checkout task. It contains the core features plus work‑in‑progress experiments and potentially breaking changes.

## Architecture overview

- **Models**: `Product`, `Hold`, `Order`, `Payment` (+ `*Relations` traits for associations).
- **Services**:
    - `StockService`: central place to compute and cache `available_stock` per product.
- **Console**:
    - `ExpireHoldsCommand`: finds `active` holds with `expires_at <= now`, marks them `expired`, and clears any cached availability.
- **HTTP layer**:
    - `ProductController`: read‑only product detail with `available_stock`.
    - `HoldController`: validates requests and delegates hold creation logic into a transaction with row‑level locks.
    - `OrderController`: creates `pending_payment` orders from valid holds and marks holds as `used`.
    - `PaymentController`: implements the idempotent payment webhook using a `Payment` model keyed by `idempotency_key`.

## Local setup (dev)

1. **Install dependencies**

```
composer install
cp .env.example .env
php artisan key:generate
```

2. **Configure dev database and cache**

   - Point `DB_*` env vars to your dev DB.
   - Optionally choose a non‑default `CACHE_DRIVER` (e.g. `redis`) to test cache invalidation.

3. **Migrate and seed**

    `php artisan migrate --seed`

4. **Run the API**

    `php artisan serve --host=127.0.0.1 --port=8000`
