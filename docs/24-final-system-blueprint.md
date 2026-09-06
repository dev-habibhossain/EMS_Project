# 24 — Final System Blueprint

## Product
Single-business Tile & Ceramic ERP + POS for Bangladesh. Laravel + Inertia + Vue + MySQL + Tailwind + Vite. Not SaaS.

## Users and roles
Owner, Admin, Manager, Salesperson, Warehouse, Accountant. Permission slugs; server policies.

## Modules
Auth, dashboard, masters, products, inventory, warehouses/transfers, purchases/GR, POS/sales, parties, ledgers, payments, returns, damaged, challans, reports, SMS, settings, i18n, audit.

## Core workflows
POS checkout atomic; purchase receive posts stock+payable; transfer dispatch/receive; returns restock or damage; challan documentary under default mode.

## Inventory model
Canonical **qty_sqft**. Identity **product + batch + shade + quality + warehouse + condition**. Factors `pieces_per_box`, `sqft_per_piece`. Line snapshots. Movements immutable. Cache on stock row.

## Database
Relational MySQL, FKs, DECIMAL money 2 / SQFT 4, ledgers, unique stock key, activity log. ERD in `07`.

## Architecture
Thin controllers, Form Requests, services with transactions, policies, jobs for SMS. Vue pages via Inertia. Pinia only if POS cart needs it.

## API
Secondary. Search + checkout + webhooks + future mobile. Version `/api/v1`.

## Security
Session CSRF, hashed passwords, RBAC, audit, no tenant filters in MVP.

## Localization
EN/BN, BDT, Asia/Dhaka, Bengali-safe print fonts.

## SMS
Driver interface, queue, logs, opt-in.

## Testing
Conversion golden cases, concurrent checkout, ledger identity, permission matrix.

## MVP
Must: masters, inventory math, WH, POS cash/credit, purchase receive, ledgers, payments, basic returns, adjust, transfer, print, RBAC, BN/EN.  
Should: challan, SMS, CSV, damaged UI polish.  
Not now: SaaS, full VAT, mobile, GL.

## Future SaaS
Organizations later; do not implement now.

## Roadmap
15 phases in `20`. Inventory tests before POS.

## Edge-case behavior (summary)

| Case | Behavior |
|---|---|
| Partial box | Allowed; display boxes+leftover pcs |
| Piece sale | Integer pcs preferred |
| SQFT sale | Allowed; may warn non-integer pcs |
| Decimal qty | 4 dp SQFT |
| Different sizes/factors | Per product |
| Lots | Never auto-mix shades |
| Damaged/broken | Separate condition |
| Shortage | Block if negative off |
| Concurrent | Row lock |
| Return | Same lot; money credit/refund |
| Cancel invoice | Reverse stock+ledger |
| Payment reverse | Opposite ledger |
| Overpay | Advance |
| Wrong receive | Correction adjust + audit |
