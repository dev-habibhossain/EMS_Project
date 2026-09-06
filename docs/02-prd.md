# 02 — Product Requirements Document

## Product vision

A single-company tile ERP + POS that keeps **physical stock** and **financial due** consistent while supporting Bangladesh tile trading practices.

## Problem statement

See `01-project-overview.md`. Core failure of generic software: inventory unit mismatch and lot (batch/shade) blindness.

## Business goals

- BG-1 Reduce stock mismatch vs physical count.
- BG-2 Enable counter sales in under 60 seconds for a known SKU.
- BG-3 Make customer due reconstructable from a ledger.
- BG-4 Support 1 showroom + multiple godowns.
- BG-5 Produce invoice + challan staff can print and hand to a driver.
- BG-6 Be deployable as one Laravel app for one business.

## User goals

- Owner: see cash, due, stock value, low stock.
- Sales: sell mixed units without calculator errors.
- Warehouse: receive, transfer, adjust, dispatch with batch/shade.
- Accountant: payments, statements, period reports.
- Admin: users, settings, units, master data.

## Target users and personas

| Persona | Context | Needs |
|---|---|---|
| Reza, Owner | 2 godowns, 8 staff | Trust numbers; prevent theft/leakage via audit |
| Nila, Counter sales | High evening traffic | Fast search, unit picker, credit + cash |
| Karim, Godown | Loads trucks | Challan, transfer receive, damaged tiles |
| Farhana, Accounts | Khata + Excel today | Ledgers, payment allocation, exports |

## User journeys (summary)

1. **Cash POS:** search tile → pick shade/batch → unit + qty → pay cash → print invoice → optional SMS.
2. **Credit POS:** same → partial pay → due posted to ledger.
3. **Purchase receive:** PO/direct purchase → lines with batch/shade/qty → receive to warehouse → payable.
4. **Transfer:** request/dispatch from A → receive at B → stock moves only on confirmed states (see warehouse workflow).
5. **Return:** select original sale → qty/unit → restock or damage → money adjustment.
6. **Challan:** from sale or standalone dispatch list → print → mark dispatched.

## Functional scope (MVP)

In: master data, inventory math, warehouses, POS/sales, purchases, ledgers, payments, returns, damaged, challans (basic), reports (core set), RBAC, BN/EN, SMS hook.

Out of MVP: multi-company, e-commerce, factory production MRP, full accounting GL/trial balance, NBR VAT return filing, mobile native app, hardware fiscal printer drivers (generic print is in).

## Non-functional scope

Desktop-first POS, MySQL transactions, <2s typical page, audit fields, backups via ops (documented), no 99.99 SLA claim for MVP.

## Major workflows

Documented in `11`–`15`.

## Success criteria

- SC-1 Selling 5 boxes + 3 pieces + 10 SQFT of one variant deducts the exact SQFT and never drifts after 100 randomized conversions in tests.
- SC-2 Two cashiers cannot both sell the last box (optimistic lock / row lock).
- SC-3 Customer statement = sum of immutable ledger lines.
- SC-4 Transfer cannot create stock from nothing.
- SC-5 Owner can restrict POS user from stock adjust and sale delete.

## MVP scope

### Must have

Auth, roles, products with conversion factors, stock by warehouse+lot dimensions, POS cash/credit/partial, purchases + receive, customer/supplier ledgers, payments, basic returns, stock adjustment (permissioned), warehouse transfer, invoices print, dashboard KPIs, BN/EN toggle, audit columns.

### Should have

Challans, damaged stock location, SMS on sale/payment, low-stock report, sale edit only in restricted window or via credit-note pattern (see assumptions), export CSV.

### Could have

Advanced profit by batch, dead-stock aging, salesperson commission, barcode/QR, multi-price lists.

### Not now

SaaS billing, multi-tenant, production/BOM, full VAT engine, mobile app, marketplace.

## Future scope

SaaS (`22`), mobile warehouse app, payment gateway, serial/QR per box, delivery route optimization.

## Risks

- Unit rounding drift.
- Over-modeling lots so POS is slow.
- Scope creep into full accounting.
- Concurrent stock.
- Unclear VAT.

## Constraints

Fixed stack. Single DB. No separate SPA. No React/Next.

## Assumptions (authoritative list also in `23`)

- A-INV-1 Canonical stock quantity is **SQFT**.
- A-LOT-1 Stock row = product + batch + shade + quality + warehouse.
- A-NEG-1 Negative stock **off** by default.
- A-CHL-1 Stock leaves warehouse on **dispatch**, not on challan draft.
- A-LED-1 Balances are **derived** from ledger lines (cached balance allowed if reconciled).
- A-VAT-1 VAT optional line/header fields only.
- A-SALE-EDIT-1 Posted sales are not silently edited; use return / payment reversal / admin cancel with reason.

## Open questions

See `23-open-questions.md`.

## Feature specification pattern

For major features below: Purpose, User, Input, Process, Output, Business rules, Edge cases.

### Feature: Unit-aware POS line

- **Purpose:** Sell tiles in box, piece, or SQFT.
- **User:** Sales, Manager, Owner.
- **Input:** Variant identity, unit, qty, price, discount.
- **Process:** Convert qty → SQFT; lock stock row; validate available; add cart; on pay, write sale + stock movement + ledger.
- **Output:** Invoice, stock down, optional due.
- **Rules:** Conversion from product factors; cannot mix undefined units; price may be per selling unit.
- **Edges:** Partial box; qty that does not map to integer pieces when policy requires integer pieces; insufficient leftover pieces.

### Feature: Lot-aware stock

- **Purpose:** Do not mix shade/batch unless user explicitly sells another lot.
- **User:** Warehouse, Sales.
- **Input:** Batch/shade/quality (quality default “A” if unused).
- **Process:** Resolve `warehouse_stocks` key; refuse if missing when negative stock off.
- **Output:** Correct lot deduction.
- **Edges:** Sale without shade when multiple shades exist — require selection.

### Feature: Customer credit

- **Purpose:** Track due.
- **User:** Sales, Accountant.
- **Input:** Invoice totals, payments.
- **Process:** Ledger: debit sale, credit payment.
- **Output:** Statement.
- **Edges:** Overpay → advance (credit balance); allocate payment to invoice optionally in MVP (see `14`).

### Feature: Purchase receive

- **Purpose:** Increase sellable stock and payable.
- **Edges:** Partial receive; damaged on receive split to damaged bucket; wrong batch correction via adjustment + audit.

### Feature: Warehouse transfer

- **Purpose:** Move lots between warehouses without changing company totals except in-transit treatment.
- **Edges:** Dispatch without receive (in-transit); cancel; partial receive.

### Feature: Sales return

- **Purpose:** Restore stock and adjust money.
- **Edges:** Return to damaged; return different warehouse than sale warehouse (allowed with permission); partial line return.
