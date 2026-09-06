# 04 — User Roles and Permissions

## Role decision

This product is a **single showroom / shop**. There are two roles. Permissions are the control plane; roles are bundles.

| Role | Slug | Why it exists |
|---|---|---|
| Admin | `admin` | The shop owner. Runs the whole shop: stock, buy, sell, money, users, settings. |
| Sales shop | `sales_shop` | Counter staff. POS, customers, and own sales. No purchase cost, no stock adjust, no users, no settings. |

**Assumption A-ROLE-1:** One role per user. Extra permissions can be attached later via `role_permission`.

Do not implement hierarchical role inheritance. Assign explicit permission sets.

## Mandatory vs recommended

Each role is seeded with **mandatory** work (the shop cannot run without it) plus **recommended** work (typical showroom extras). Both sets are granted in the seeder so a default shop is usable. Recommended items can be removed later from a role without deleting the permission catalog.

### Admin — mandatory

Dashboard, POS, products CRUD, master data, inventory view/adjust, warehouses, transfers, sales (including cancel), purchases (create/receive/return), customers (including credit override and ledger), suppliers, customer and supplier payments (including reverse), sales and purchase returns, challans, sales/stock/finance reports, users, settings.

### Admin — recommended

Unlimited discount, manage roles, send SMS, view audit log.

### Sales shop — mandatory

Dashboard, POS, view sales, view products, view inventory (sellable at the counter), view/manage customers, collect customer payments.

### Sales shop — recommended

Customer ledger, view/create challans, sales returns, sales reports.

## Permission catalog (slug examples)

`dashboard.view`  
`products.view|create|update|delete`  
`masterdata.manage`  
`inventory.view` `inventory.adjust`  
`warehouses.view|manage` `transfers.create|dispatch|receive`  
`pos.use` `sales.view` `sales.cancel` `sales.discount.unlimited`  
`purchases.view|create|receive|return`  
`customers.view|manage` `customers.ledger` `customers.credit.override`  
`suppliers.view|manage` `suppliers.ledger`  
`payments.customer` `payments.supplier` `payments.reverse`  
`returns.sales` `returns.purchase`  
`challans.view|create|dispatch`  
`reports.sales` `reports.stock` `reports.finance`  
`users.manage` `roles.manage`  
`settings.manage`  
`sms.send`  
`audit.view`

## Role × module matrix

| Module | Admin | Sales shop |
|---|---|---|
| Dashboard | All | Today + own sales |
| POS | ✓ | ✓ |
| Products | CRUD | View |
| Master data | ✓ | ✗ |
| Inventory view | ✓ | Sellable |
| Stock adjust | ✓ | ✗ |
| Warehouses | ✓ | Default WH only |
| Transfers | ✓ | ✗ |
| Purchases | ✓ | ✗ |
| Sales docs | ✓ | Own + POS |
| Cancel sale | ✓ | ✗ |
| Customers | ✓ | ✓ |
| Credit override | ✓ | ✗ |
| Suppliers | ✓ | ✗ |
| Payments | ✓ | Collect on POS |
| Reverse payment | ✓ | ✗ |
| Returns | ✓ | Create request |
| Challans | ✓ | Create |
| Reports stock | ✓ | ✗ |
| Reports finance | ✓ | ✗ |
| Reports sales | ✓ | Own |
| Users | ✓ | ✗ |
| Settings | ✓ | Own profile only |
| SMS | ✓ | Auto only |
| Audit | ✓ | ✗ |

## Access rules

- Walk-in cash sales do not require customer create permission beyond selecting Walk-in.
- Cost price hidden from Sales shop.
- Delete of master data blocked if stock or document references exist (soft hide).
- Admin-only: change `allow_negative_stock`, manage users.

## UI enforcement

Hide buttons and nav the user cannot use. Still enforce Policies on the server. Never trust the sidebar.
