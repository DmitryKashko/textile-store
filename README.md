# Textile Store API

Backend of an online fabric store: catalog with filters and an order flow with stock reservation and wholesale pricing.

**Stack:** PHP 8.4, Laravel 12, MySQL 8, Redis (cache + queues), Sanctum, Docker.

## Features

- **Catalog** — categories and products (material, color, width, price per metre, stock). Filtering by category, material, color and price range, sorting, pagination.
- **Caching** — product listings are cached in Redis. Instead of deleting keys one by one, the cache uses a version key: any product change bumps the version (`ProductObserver`, after commit), old entries expire by TTL.
- **Orders** — an order is placed in one transaction: product rows are locked with `SELECT ... FOR UPDATE` in a stable order (no deadlocks between concurrent orders), stock is checked and decremented, prices are taken from the database, product name and price are stored as a snapshot.
- **Wholesale pricing** — 5% discount from 50 m of one fabric, 10% from 100 m (`PriceCalculator`).
- **Background jobs** — `OrderPlaced` is dispatched only after the transaction commits; the confirmation email is sent by a queued listener with retries and backoff.
- **Auth** — token authentication with Sanctum, rate limiting for login and order placement, a policy so users see only their own orders.

## Structure

```
app/
├── Data/                 readonly DTOs (ProductFilter, PlaceOrderData, OrderLine)
├── Enums/                Material, OrderStatus
├── Events/ Listeners/    OrderPlaced -> SendOrderConfirmation (queued)
├── Exceptions/           business errors rendered as 422 and not reported
├── Http/
│   ├── Controllers/Api/  thin controllers
│   ├── Requests/         validation + mapping to DTOs
│   └── Resources/        API responses
├── Observers/            cache invalidation
├── Policies/
├── Repositories/
│   ├── Contracts/        interfaces used by services
│   └── Eloquent/         implementations, bound in AppServiceProvider
└── Services/
    ├── Catalog/          CatalogService, CatalogCache
    └── Orders/           OrderService, PriceCalculator
```

Request flow: `Controller -> FormRequest (DTO) -> Service -> Repository`.

Money is stored as integers in minor units (kopecks), quantities in whole metres.

## Running

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

The API is available at `http://localhost:8080/api` (set `APP_PORT` to use another port).
Seeded user: `demo@example.com` / `password`.

## Tests

```bash
docker compose exec app php artisan test
```

Tests run on SQLite in memory and cover catalog filters and caching, order placement (stock, discounts, snapshot, rollback on insufficient stock), validation, auth, policies and the queued listener.

## API

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/auth/register` | — | register, returns a token |
| POST | `/api/auth/login` | — | returns a token |
| POST | `/api/auth/logout` | token | revoke current token |
| GET | `/api/categories` | — | list categories |
| GET | `/api/products` | — | `category_id`, `material`, `color`, `price_from`, `price_to`, `sort` (`newest`, `price_asc`, `price_desc`), `per_page`, `page` |
| GET | `/api/products/{slug}` | — | product details |
| GET | `/api/orders` | token | current user's orders |
| GET | `/api/orders/{id}` | token | order details |
| POST | `/api/orders` | token | place an order |

Example:

```bash
curl -X POST http://localhost:8080/api/orders \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"items": [{"product_id": 1, "quantity": 60}, {"product_id": 2, "quantity": 3}]}'
```

If there is not enough stock, the whole order is rejected with `422`:

```json
{
  "message": "Not enough stock.",
  "errors": { "items": [{ "product_id": 1, "requested": 1000, "available": 292 }] }
}
```
