# Flash‑Sale Checkout API (Laravel)

Small Laravel API for a flash‑sale scenario with limited stock. It exposes endpoints to check product availability, create short‑lived holds, turn holds into orders, and process idempotent payment webhooks, while guaranteeing no overselling.

## Problem overview
During a flash sale, many clients try to buy the same limited‑stock product at once.  

This API:
- Maintains a single source of truth for product stock.
- Uses database transactions and row‑level locks to prevent overselling.
- Reserves stock via short‑lived holds.
- Creates orders only from valid, unexpired, unused holds.
- Updates orders from a payment webhook that is idempotent and safe for out‑of‑order delivery.[file:65]

## Running the application
1. **Clone and install**

```
git clone https://github.com/mostafaamahmoudd/laravel-flash-sale-concurrency
cd laravel-flash-sale-concurrency
composer install
cp .env.example .env
php artisan key:generate
```

2. **Configure database**
   - Set `DB_CONNECTION`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` in `.env`.
   - Optionally configure `CACHE_DRIVER` (e.g. `file`, `database`, `redis`).

3. **Migrate and seed**

    `php artisan migrate --seed`

    The seeder creates a sample product with finite stock and price.

4. **Run the server**

    `php artisan serve`

    API base URL: `http://127.0.0.1:8000`.

## Main endpoints

- `GET /api/products/{id}`  
  Returns product fields and computed `available_stock`.

- `POST /api/holds`  
  Body: `{ "product_id": <int>, "qty": <int> }`  
  Creates a short‑lived hold: `{ "hold_id", "expires_at" }`.

- `POST /api/orders`  
  Body: `{ "hold_id": <int> }`  
  Creates a `pending_payment` order from a valid, unexpired, unused hold.

- `POST /api/payments/webhook`  
  Body: `{ "order_id": <int>, "idempotency_key": "<string>", "status": "success|failure" }`  
  Records a payment event and transitions the order to `paid` or `cancelled` in an idempotent way.

- `php artisan holds:expire`  
  Expires holds whose `expires_at <= now` and refreshes stock availability.