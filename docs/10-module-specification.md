# 10 — Module Specification

Modules added vs original list: **Goods receipts** (split from purchase), **Activity log**, **Payment methods**.  
Removed as standalone: multi-company.  
**Expenses** deferred to Could Have.

## Authentication
Login, logout, password change, session. Locale cookie/user.

## Dashboard
KPIs + links to exceptions (low stock, pending receives).

## Products
List, create, edit factors/prices, deactivate. Show on-hand aggregated.

## Brands / Factories / Tile sizes / Units / Batches / Shades / Quality
Simple masters. Units system-seeded, not user-deleted.

## Inventory
Stock browser by warehouse and lot. Movement history drawer. Adjust modal.

## Warehouses & transfers
CRUD warehouses. Transfer pipeline.

## POS
Full-screen layout, search, cart, pay modal, print.

## Sales
Grid of posted sales, detail, cancel, return launch, print.

## Purchases
Order + receive UI.

## Customers / Suppliers
Master + statement tab.

## Credit / Payables
Not separate apps; tabs on party + finance reports.

## Payments
Receipts and supplier payments journals.

## Returns
Wizards from original document.

## Damaged stock
Filter stock condition=damaged; convert actions.

## Delivery challans
Create from sale or blank; print; dispatch.

## Reports
See `16`.

## Notifications / SMS
Settings + log viewer.

## Users & permissions
Admin UI for users and role bundles (permission checkboxes).

## Settings
Company profile, flags, invoice prefixes, SMS credentials encrypted.

## Localization
Switcher in header.

### Decision log
- Keep factories and brands separate: same factory multiple brands possible.
- Batches per product not global: codes repeat across SKUs.
