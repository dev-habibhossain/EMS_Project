# 01 — Project Overview

## Product vision

A production-quality **single-business ERP + POS** that understands how Bangladeshi tile and ceramic merchants actually buy, store, and sell goods: boxes, pieces, and square feet; factory batches and shades; multiple godowns; credit customers; delivery challans; and Bangla/English paperwork.

## Product mission

Give one tile business a trustworthy system of record for **stock that matches the warehouse floor** and **money that matches the customer ledger**, without forcing generic “SKU + qty” software onto a product that is sold in three units.

## Problem statement

Generic inventory/POS tools fail tile merchants because:

1. Stock is not a single integer. Merchants sell **boxes**, leftover **pieces**, and sometimes **SQFT**.
2. The same design exists in **many shades and batches**. Mixing shades on a floor is a customer complaint.
3. Goods move across **showroom + godowns**.
4. A large share of sales is **credit**. Due must be auditable, not a mutable number on the customer row.
5. Operations need **challan + invoice**, SMS to customers, and documents in Bangla or English.
6. Spreadsheets and paper ledgers drift. Disputes cannot be reconstructed.

## Target market

Tile and ceramic **retail and wholesale** businesses in Bangladesh: showroom + warehouse operators, dealers who buy from factories (RAk, Fresh, Mir, CBC, Greatwall, local factories, etc.), and shops that sell both full boxes and broken lots.

## Target customers (buyers of the software)

- Business owner who currently runs Excel + khata
- Growing dealer with 1–3 warehouses and 5–20 staff
- Portfolio / commercial demo for a full-stack engineer targeting ERP work

## Target users (operators)

Owner, Admin, Manager, Salesperson (POS), Warehouse staff, Accountant. See `04-user-roles-permissions.md`.

## Value proposition

- Sell in any unit; deduct stock in one canonical quantity (SQFT) without losing piece/box meaning.
- Track **product + batch + shade + quality + warehouse**.
- POS that is fast on a desktop at the counter.
- Customer and supplier **ledgers**, not only balances.
- Challans, returns, damaged stock, transfers.
- BN/EN UI and printable documents, BDT, local SMS providers.

## Main features (MVP-oriented)

Products and conversions, inventory ledger, multi-warehouse, POS/sales, purchases/receiving, customers/suppliers, payments, returns, damaged stock, challans, core reports, RBAC, settings, localization, optional SMS.

## Major modules

Authentication, Dashboard, Master data (brands/factories, sizes, units, shades, grades, batches), Products, Inventory, Warehouses & transfers, Purchases, POS & Sales, Customers & credit, Suppliers & payables, Payments, Returns, Damaged stock, Delivery challans, Reports, Notifications/SMS, Users & permissions, Settings, Localization.

## Bangladesh-specific considerations

- Currency **BDT (৳)**; grouping often `1,00,000` style in print (UI may use `100,000.00` internally).
- Bangla labels and invoices; English for many product SKUs and factory names.
- Cash + bKash/Nagad/bank as payment methods (record method; no forced PSP integration in MVP).
- SMS via local gateways (SSL Wireless, BulkSMSBD, etc.) behind an interface.
- Challan culture for truck delivery to sites.
- Shade/batch sensitivity is culturally and commercially real (mismatch = return).
- VAT/BIN: **assumption** — MVP records optional VAT fields but does not implement full NBR e-VAT filing (see open questions).

## Unique features vs generic POS

1. First-class **Box / Piece / SQFT** conversion with a single stock source of truth.
2. Stock identity includes **batch + shade + quality**.
3. Partial boxes and leftover pieces are first-class, not hacks.
4. Delivery challan as an operational document, not a clone of the invoice.
5. Ledger-based credit.

## Portfolio / resume value

Demonstrates domain modeling (units, lots), financial integrity (ledgers + transactions), Laravel modular architecture, Inertia UX, RBAC, and localization — stronger than a generic CRUD shop.

## Future commercial potential

After a stable single-business product: paid onboarding for dealers, then **optional** multi-tenant SaaS (`22-future-saas-roadmap.md`). MVP must not pay the SaaS tax early.
