# 03 — Software Requirements Specification

IDs are stable. Prefix: `FR-<AREA>-nnn` / `NFR-<AREA>-nnn`.

## 1. Functional requirements

### Authentication & users

| ID | Requirement |
|---|---|
| FR-AUTH-001 | Users authenticate with email or username + password. |
| FR-AUTH-002 | Session-based auth for Inertia web app (Laravel session + CSRF). |
| FR-AUTH-003 | Optional remember-me; idle timeout configurable. |
| FR-AUTH-004 | Password reset via email if mail configured; else admin reset. |
| FR-AUTH-005 | Failed login rate limiting. |
| FR-AUTH-006 | Owner can create/deactivate users. Soft-delete or `is_active`. |
| FR-AUTH-007 | Users have exactly one primary role in MVP (spatie-style permissions still used). |
| FR-USER-001 | Profile: name, phone, language preference. |

### Authorization

| ID | Requirement |
|---|---|
| FR-RBAC-001 | Permission-gated routes, policies, and UI actions. |
| FR-RBAC-002 | Warehouse-scoped users may be limited to assigned warehouses (Should Have). MVP may start global with this flag in settings. |

### Dashboard

| ID | Requirement |
|---|---|
| FR-DASH-001 | Today sales, today collections, open due total, low-stock count. |
| FR-DASH-002 | Simple charts: 7/30 day sales. |
| FR-DASH-003 | Alerts: low stock, pending transfer receives. |

### Products & master data

| ID | Requirement |
|---|---|
| FR-PRD-001 | CRUD products with SKU, names EN/BN, brand, factory, size, default quality. |
| FR-PRD-002 | Product stores `pieces_per_box` and `sqft_per_piece` (> 0). |
| FR-PRD-003 | System computes `sqft_per_box`. |
| FR-PRD-004 | Product status active/inactive; inactive hidden from POS search. |
| FR-PRD-005 | Optional barcode/SKU search keys. |
| FR-BRD-001 | Brands CRUD. |
| FR-FAC-001 | Factories CRUD (may equal brand or differ). |
| FR-SIZ-001 | Tile sizes as master (e.g. 600x600, 300x300) with optional default sqft/piece override. |
| FR-UNT-001 | Units: BOX, PCS, SQFT as system units; custom units not required in MVP. |
| FR-BAT-001 | Batches belong to a product (or product+factory) with code/date. |
| FR-SHD-001 | Shades: global or per product code (e.g. A1, B3). |
| FR-QTYG-001 | Quality grades: A, B, Commercial, Broken, etc. |
| FR-PRD-010 | Selling prices: default price per BOX, per PCS, per SQFT (at least one required). |

### Inventory

| ID | Requirement |
|---|---|
| FR-INV-001 | Stock held per product + batch + shade + quality + warehouse. |
| FR-INV-002 | Canonical qty is `qty_sqft` DECIMAL. |
| FR-INV-003 | Display qty in box/pcs/sqft derived from factors. |
| FR-INV-004 | All stock changes write `stock_movements` (immutable). |
| FR-INV-005 | Available = on_hand − reserved (reservations Should Have; MVP may reserve only open POS carts in memory). |
| FR-INV-006 | Negative stock controlled by setting `allow_negative_stock` default false. |
| FR-INV-007 | Damaged stock is a separate bucket or quality/grade + `stock_status` — see inventory rules. |
| FR-INV-008 | Stock adjustment requires reason + permission. |
| FR-INV-009 | Opening stock entry as movement type OPENING. |

### Warehouses

| ID | Requirement |
|---|---|
| FR-WH-001 | Multiple warehouses/godowns/showrooms. |
| FR-WH-002 | One default sales warehouse per POS station/user. |
| FR-WH-003 | Transfers: draft → dispatched → received / cancelled. |
| FR-WH-004 | In-transit qty must not be sellable in source or dest. |
| FR-WH-005 | Adjustments warehouse-scoped. |

### POS & sales

| ID | Requirement |
|---|---|
| FR-POS-001 | Dedicated POS screen, keyboard-friendly search. |
| FR-POS-002 | Cart lines: variant lot, unit, qty, unit price, line discount, line SQFT, line total. |
| FR-POS-003 | Stock validated at add and at checkout. |
| FR-POS-004 | Payments: cash, bank, MFS, credit (due), split tender. |
| FR-POS-005 | Invoice number sequential per series. |
| FR-POS-006 | Print invoice (browser print / PDF). |
| FR-POS-007 | Hold cart optional (Could Have). |
| FR-SALE-001 | Back-office sale list, filter, detail. |
| FR-SALE-002 | Cancel posted sale: permission + reason; reversing movements + ledger. |
| FR-SALE-003 | No silent edit of posted financials. |
| FR-SALE-004 | Document-level and line discounts. |
| FR-SALE-005 | Prices cannot be below min price unless permitted. |

### Purchases

| ID | Requirement |
|---|---|
| FR-PUR-001 | Purchase documents with supplier, warehouse, lines. |
| FR-PUR-002 | Lines include lot fields and unit/qty/price. |
| FR-PUR-003 | Receive may be partial. |
| FR-PUR-004 | Unreceived remainder remains open. |
| FR-PUR-005 | Purchase return document. |
| FR-PUR-006 | Damaged-on-receive path. |

### Parties & money

| ID | Requirement |
|---|---|
| FR-CUS-001 | Customers: name, phone unique-ish, address, credit limit optional. |
| FR-CUS-002 | Walk-in customer system record. |
| FR-CUS-003 | Customer ledger + statement. |
| FR-CUS-004 | Opening balance as ledger line. |
| FR-SUP-001 | Suppliers master + ledger. |
| FR-PAY-001 | Customer receipts allocate to customer; optional invoice allocation. |
| FR-PAY-002 | Supplier payments. |
| FR-PAY-003 | Payment reversal with permission. |
| FR-PAY-004 | Methods configurable. |

### Returns, challan, reports, SMS, i18n, settings

| ID | Requirement |
|---|---|
| FR-RET-001 | Sales return against sale (preferred) or independent with reason. |
| FR-RET-002 | Restock sellable or damaged. |
| FR-CHL-001 | Delivery challan with lines, vehicle, driver, status. |
| FR-CHL-002 | Dispatch posts stock out if not already invoiced-out (see challan doc). |
| FR-RPT-001 | Sales, purchase, stock, due, payable, movements reports. |
| FR-RPT-002 | Date range + warehouse + brand filters. |
| FR-RPT-003 | CSV export for listed reports. |
| FR-SMS-001 | Provider interface; queue send; log. |
| FR-SMS-002 | Events: sale, payment, due reminder (manual trigger MVP). |
| FR-I18N-001 | EN/BN UI via Laravel lang + Vue. |
| FR-I18N-002 | Invoice language selectable. |
| FR-SET-001 | Company name, address, phones, logo, currency BDT, negative stock flag, invoice prefix. |
| FR-AUD-001 | created_by, updated_by, timestamps on documents. |
| FR-AUD-002 | Activity log for cancel/adjust/reverse. |

## 2. Non-functional

| ID | Requirement |
|---|---|
| NFR-PER-001 | POS search p95 < 300ms locally on modest VPS for 10k products. |
| NFR-PER-002 | Checkout transaction typically < 1s excluding print/SMS. |
| NFR-SCA-001 | Design for 50k sale rows/year and 20k stock rows without rewrite. |
| NFR-AVA-001 | Single instance acceptable; queue worker for SMS. |
| NFR-USE-001 | Desktop-first; usable at 1280px; POS large tap targets. |
| NFR-USE-002 | Bangla font support (Noto Sans Bengali or similar). |
| NFR-I18N-001 | Dates: display DD-MM-YYYY; store UTC/app timezone Asia/Dhaka. |
| NFR-I18N-002 | Money DECIMAL(16,2); inventory DECIMAL(16,4) SQFT. |
| NFR-SEC-001 | See `19-security-permissions.md`. |
| NFR-REL-001 | Financial and stock writes in DB transactions. |
| NFR-MAI-001 | Env-based config; no secrets in repo. |

## 3. Security requirements

| ID | Requirement |
|---|---|
| SEC-001 | Hashed passwords (bcrypt/argon). |
| SEC-002 | CSRF on Inertia/web. |
| SEC-003 | Mass-assignment protection; Form Requests. |
| SEC-004 | Policies on every mutating document. |
| SEC-005 | SQL via Eloquent/bindings only. |
| SEC-006 | Upload allow-list (logo). |
| SEC-007 | Audit log retained. |
| SEC-008 | Role cannot self-escalate. |

## 4. Localization requirements

| ID | Requirement |
|---|---|
| LOC-001 | Locale switch persisted on user. |
| LOC-002 | Number format configurable; default 2 decimal money, 2–4 SQFT display. |
| LOC-003 | Currency symbol ৳. |
