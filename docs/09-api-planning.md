# 09 — API Planning

Inertia is primary. APIs are for: POS typeahead, SMS webhooks, future mobile, integrations.

## Versioning

Prefix `/api/v1/`. Breaking changes increment version. Web Inertia routes stay unversioned under `/`.

## Cross-cutting

- Auth: session (same origin) or Sanctum token for future mobile.
- Permission: same policies.
- Pagination: `page`, `per_page` max 100.
- Filter/sort: allow-listed query params.
- Errors:

```json
{ "error": { "code": "STOCK_INSUFFICIENT", "message": "...", "details": {} } }
```

- Rate limit: login 5/min; SMS send 30/min; public 60/min.
- Idempotency: `Idempotency-Key` header on payment create and checkout.

## Endpoint catalog (selected)

### Auth
- `POST /api/v1/auth/login` — mobile future; web uses session.
- `POST /api/v1/auth/logout`

### Products / inventory
- `GET /api/v1/products/search?q=&warehouse_id=` — POS, permission `pos.use`
- `GET /api/v1/stocks?product_id=&warehouse_id=`

### Sales
- `POST /api/v1/pos/checkout`  
  Purpose: atomic checkout if POS uses XHR instead of Inertia.  
  Auth + `pos.use`.  
  Body: customer_id, warehouse_id, items[], payments[], discount, idempotency.  
  201 `{ sale_id, number, totals }`  
  422 stock/validation  
  409 idempotent replay returns original.

### Customers
- `GET /api/v1/customers/search?q=`
- `GET /api/v1/customers/{id}/ledger`

### Purchases / transfers / challans
Standard REST list/show/create/status-transition. Status transitions are explicit:
- `POST /api/v1/transfers/{id}/dispatch`
- `POST /api/v1/transfers/{id}/receive`

### Reports
Prefer Inertia download or queued `POST /api/v1/reports/{type}/export`.

### SMS
- `POST /api/v1/webhooks/sms/{provider}` — signature verify, no user auth.

## When not to build an API

CRUD pages that are already Inertia forms do not need duplicate REST in MVP.
