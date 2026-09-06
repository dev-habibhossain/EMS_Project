# Frontend Screen Inventory

Implementation-ready page list for the Tile & Ceramic ERP + POS.  
No Vue/Laravel code. No extra pages beyond documented MVP modules.

**Legend — presentation**

| Type | Meaning |
|---|---|
| Page | Inertia page under AppLayout (or PosLayout / GuestLayout) |
| Modal | Overlay on current page |
| Drawer | Right sheet |
| Tab | Section of a parent page |
| Print | Browser print / dedicated print layout |

**Roles:** O Owner, A Admin, M Manager, S Sales, W Warehouse, C Accountant.  
Hide nav if no view permission.

Shared states: loading skeleton, empty, no-results, validation error, server error, 403, success toast.

EN/BN: chrome from lang files; `name` / `name_bn` where stored. Dark/light: token surfaces.

Desktop = primary. Mobile = list + horizontal-scroll tables; POS is not a phone app.

---

## A. Route map

```
/login
/dashboard

/pos

/products
/products/create
/products/{id}
/products/{id}/edit

/brands
/factories
/tile-sizes
/shades
/quality-grades
/batches

/inventory
/inventory/damaged

/warehouses
/warehouses/{id}

/transfers
/transfers/create
/transfers/{id}

/sales
/sales/{id}

/purchases
/purchases/create
/purchases/{id}

/challans
/challans/create
/challans/{id}

/returns/sales
/returns/sales/create
/returns/sales/{id}
/returns/purchases
/returns/purchases/create
/returns/purchases/{id}

/customers
/customers/create
/customers/{id}
/customers/{id}/edit
/customers/{id}/ledger

/suppliers
/suppliers/create
/suppliers/{id}
/suppliers/{id}/edit
/suppliers/{id}/ledger

/payments
/payments/{id}

/reports
/reports/sales-daily
/reports/sales-monthly
/reports/purchases
/reports/stock
/reports/stock-movements
/reports/low-stock
/reports/customer-due
/reports/supplier-payable
/reports/payments
/reports/product-sales
/reports/brand-sales
/reports/salesperson-sales
/reports/returns

/users
/users/create
/users/{id}/edit

/settings
/settings/sms
/activity

/print/invoices/{id}
/print/challans/{id}
/print/ledgers/customers/{id}
/print/ledgers/suppliers/{id}
```

Masters (brands, factories, sizes, shades, grades, batches) = **list pages + create/edit modals**.  
No Units page (system-seeded).

---

## B. Screen count

| Kind | Count |
|---|---|
| Full pages (including report variants and print layouts) | **~62** |
| Report pages | 13 |
| Print layouts | 4 |
| Modal / drawer overlays (reused) | **~22** |
| Tabs (not extra routes) | **~18** |

If reports share one page with `?type=`, full pages drop to ~50.

---

## C. Critical screens

1. POS (`/pos`)  
2. Inventory (`/inventory`)  
3. Product create/edit  
4. Stock movements drawer  
5. Transfer detail  
6. Purchase detail/receive  
7. Sale detail  
8. Customer ledger  
9. Supplier ledger  
10. Reports  
11. Damaged record modal  
12. Challan print  

---

## D. Navigation map

```
Login → Dashboard
         ├─ POS → Print invoice → New sale / Exit
         ├─ Products → Detail → Stock tab
         ├─ Inventory → Adjust / Damage / Product
         ├─ Warehouses → Create transfer → Transfer detail
         ├─ Purchases → Create → Receive → Supplier ledger
         ├─ Sales → Detail → Return / Challan / Cancel
         ├─ Customers → Profile → Ledger / Payment / POS
         ├─ Suppliers → Profile → Ledger / Payment
         ├─ Payments
         ├─ Challans → Print
         ├─ Returns
         └─ Reports → typed report → CSV/Print
Settings / Users / Activity (O/A)
```

---

## E. Permission-aware screens and actions

| Screen / action | Unless |
|---|---|
| POS | `pos.use` (not W, C) |
| Product mutate | products.create/update/delete |
| Cost columns | not Sales |
| Inventory adjust | `inventory.adjust` (not S, C) |
| Transfer dispatch/receive | transfer permissions |
| Purchase create | purchases.create (W receive only) |
| Sale cancel | `sales.cancel` |
| Unlimited discount | `sales.discount.unlimited` |
| Credit override | `customers.credit.override` |
| Payment reverse | `payments.reverse` (O, C) |
| Finance reports | `reports.finance` (not S, W) |
| Users / settings | users.manage / settings.manage |
| Negative stock flag | Owner only |
| SMS resend | `sms.send` |
| Activity | `audit.view` |

UI hides; server still 403s.

---

# Inventory by module

---

## 1. Authentication

### Login
- `/login` — Page (GuestLayout)  
- Roles: guest  
- Form: login, password, remember  
- Desktop: centered 360px. Mobile: stacked.  

No register. No SaaS onboarding.

---

## 2. Dashboard

### Overview
- `/dashboard` — Page  
- Roles: `dashboard.view` (widgets differ by role)  
- Metric strip, attention list, recent tables  
- Filters: warehouse, today/date  

---

## 3. POS & sales

### POS
- `/pos` — Page, PosLayout, full viewport  
- Roles: O A M S  
- Search, results, lot sheet, UnitQtyInput, cart, customer combobox, tender  
- Actions: add line, discount, split pay, checkout, print, SMS  
- States: insufficient stock, walk-in+due blocked, credit limit  
- Desktop: 3 panes. Tablet: 2 panes + tender sheet.  

### Sales list
- `/sales` — Page  
- Roles: O A M C; S own+POS; W limited  
- Filters: date, WH, customer, user, status, due-only  
- Columns: number, datetime, customer, WH, total, paid, due, status, user  

### Sale detail
- `/sales/{id}` — Page  
- Tabs: Lines, Payments, Challans, Returns  
- Actions: Print, Challan from sale, Return, Cancel (modal + reason)  
- No edit of posted body  

### Cancel sale
- Modal on sale detail  

### Invoice print
- `/print/invoices/{id}` — Print; EN/BN independent of UI  

---

## 4. Products & masters

### Product list
- `/products` — Page — O A M CRUD; S W C view  
- Filters: q, brand, factory, size, active  
- Columns: SKU, name, size, brand, pcs/box, sqft/pc, price/box, on-hand triple, status  

### Product create / edit
- `/products/create`, `/products/{id}/edit` — Page  
- Sections: identity, conversion+preview, prices, flags  

### Product detail
- `/products/{id}` — Page  
- Tabs: Overview, Stock by lot, Prices, Movements, Documents  

### Brands / Factories / Tile sizes / Shades / Quality grades / Batches
- `/brands` `/factories` `/tile-sizes` `/shades` `/quality-grades` `/batches` — Pages  
- List + **Create/Edit modal**  
- Batches filtered by product  
- Mutate: masterdata.manage  

No Units page.

---

## 5. Inventory

### Stock overview
- `/inventory` — Page  
- Filters: WH, product, batch, shade, grade, condition, low stock  
- Columns: product, size, WH, batch, shade, grade, condition, BOX, PCS, SQFT  
- Drawer: stock movements for that lot  

### Damaged stock
- `/inventory/damaged` — Page  
- Record damage modal  

### Adjust stock
- Modal — lot, unit, qty, in/out or count, reason, preview  
- Roles: inventory.adjust  

### Opening stock
- Same adjust modal, type OPENING — not a separate page  

---

## 6. Warehouses & transfers

### Warehouse list
- `/warehouses` — Page  
- Columns: code, name, type, sellable SQFT, lots, active  
- Create/edit: **Modal**  

### Warehouse detail
- `/warehouses/{id}` — Page  
- Tabs: Stock, Transfers, Adjustments  

### Transfer list
- `/transfers` — Page  
- Filters: status, from, to, date  

### Transfer create
- `/transfers/create` — Page  
- Header + line editor  

### Transfer detail
- `/transfers/{id}` — Page  
- Dispatch, Receive (modal or inline), Cancel  

---

## 7. Purchases

### Purchase list
- `/purchases` — Page — not Sales  
- Columns: number, date, supplier, status, total, received %  

### Purchase create
- `/purchases/create` — Page  

### Purchase detail + receive
- `/purchases/{id}` — Page  
- Tabs: Lines, Receipts, Returns, Payments  
- Receive modal: qty, batch, shade, quality, damaged split  
- Quick receive from list uses same family  

---

## 8. Customers & credit

### Customer list
- `/customers` — Page  
- Columns: name, phone, due, limit, last sale  

### Customer create / edit
- `/customers/create`, `/customers/{id}/edit` — Page  
- POS quick create: **Modal**  

### Customer profile
- `/customers/{id}` — Page  
- Tabs: Profile, Sales, Payments, Ledger, Returns  
- Actions: payment modal, open POS  

### Customer ledger
- `/customers/{id}/ledger` — Page  
- Columns: date, particulars, ref, debit, credit, balance  
- Print: `/print/ledgers/customers/{id}`  

### Receive customer payment
- Modal from profile, sale, or payments  

---

## 9. Suppliers & payables

- `/suppliers` list  
- `/suppliers/create` `/suppliers/{id}` `/suppliers/{id}/edit`  
- `/suppliers/{id}/ledger` + print  
- Payment modal  
- No Sales nav  

---

## 10. Payments

### Payment journal
- `/payments` — Page  
- Filters: party type, method, date, reversed  

### Payment detail
- `/payments/{id}` — Page or Drawer  
- Reverse modal if permitted  

No allocation screen in MVP.

---

## 11. Returns

### Sales returns
- `/returns/sales` list  
- `/returns/sales/create?sale_id=` wizard  
- `/returns/sales/{id}` detail  

### Purchase returns
- `/returns/purchases` + create + `{id}`  

---

## 12. Delivery challans

### List
- `/challans` — Page  

### Create / edit
- `/challans/create`, `/challans/{id}` — Page  
- Prefill `?sale_id=`  
- Dispatch, Mark delivered, Cancel, Print  

### Print
- `/print/challans/{id}`  

---

## 13. Reports

### Hub
- `/reports` — Page  

### Typed pages

| Route | Perm |
|---|---|
| `/reports/sales-daily` | reports.sales |
| `/reports/sales-monthly` | reports.sales |
| `/reports/purchases` | purchases/finance |
| `/reports/stock` | reports.stock |
| `/reports/stock-movements` | reports.stock |
| `/reports/low-stock` | reports.stock |
| `/reports/customer-due` | reports.finance |
| `/reports/supplier-payable` | reports.finance |
| `/reports/payments` | reports.finance |
| `/reports/product-sales` | reports.sales |
| `/reports/brand-sales` | reports.sales |
| `/reports/salesperson-sales` | reports.sales |
| `/reports/returns` | sales or finance |

Each: filters + summary + table + CSV + print.  
No dead-stock or profit page in MVP.

---

## 14. SMS / notifications

- `/settings/sms` — credentials + log table  
- Due reminder: **modal** on customer  
- In-app notifications: header dropdown, not a page  

---

## 15. Users, settings, audit

### Users
- `/users`, `/users/create`, `/users/{id}/edit` — O A  
- Fields: name, email, phone, role, default WH, locale, active  
- No permission-matrix page in MVP (Should Have)  

### Settings
- `/settings` — tabs: Company, Documents, Inventory flags, Locale, POS defaults  
- Owner-only: negative stock  

### Activity
- `/activity` — audit.view  

Language / theme: header controls, not pages.

---

## 16. Shared overlays (not routes)

| Overlay | Type |
|---|---|
| Lot picker | Drawer |
| Confirm destructive | Modal |
| Command search | Modal |
| Payment capture | Modal / POS panel |
| Receive goods | Modal |
| Receive transfer | Modal |
| Adjust / damage / opening | Modal |
| Quick customer | Modal |
| Movement history | Drawer |

---

# Cross-check

| Module | Screens | Gap |
|---|---|---|
| Auth | Login | Forgot-password only if mail exists; else admin reset |
| Dashboard | Overview | OK |
| Products + masters | list/detail/modals | OK |
| Units | none | seeded |
| Inventory | overview, drawer, adjust | OK |
| Warehouses / transfers | list, detail, pipeline | OK |
| POS / sales | POS, list, detail, print | OK |
| Purchases | list, create, receive | OK |
| Customers / credit | list, profile, ledger | OK |
| Suppliers | mirror | OK |
| Payments | journal, reverse | OK |
| Returns | 3+3 pages | OK |
| Damaged | page + modal | OK |
| Challans | list, editor, print | OK |
| Reports | hub + 13 | profit/dead-stock deferred |
| SMS | settings + modal | OK |
| Users | CRUD | no matrix UI |
| Settings / audit | pages | OK |
| Expenses / SaaS | none | correct |

**Do not build:** hold cart, payment allocation, multi-company, register, tenant admin, 3D warehouse, product gallery, Units CRUD, profit dashboard.

This list is the checklist for Inertia page files. One page file per **Page** row; modals are components on those pages.
