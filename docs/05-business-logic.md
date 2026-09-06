# 05 — Business Logic Specification

Format per rule: Rule → Reason → Example → Database impact → Edge cases.

## Product creation

**Rule:** A product cannot be saved without `pieces_per_box` and `sqft_per_piece` > 0. SKU unique.  
**Reason:** Conversion is undefined otherwise.  
**Example:** SKU RAK-60-WHT, 4 pcs/box, 2.69 sqft/pc.  
**DB:** `products` insert; optional prices table.  
**Edges:** Changing factors later does not rewrite posted lines (snapshots). Warn if stock exists.

## Product variations vs lots

**Rule:** Color/design that is a different SKU = different product. Shade/batch of same SKU = stock dimensions, not new product.  
**Reason:** Avoid SKU explosion.  
**Example:** “Carrara 600 White” one product; shade A1 vs A2 two stock rows.  
**DB:** no `product_variants` required if shade/batch model used. Optional `product_id` only.  
**Edges:** If merchant sells a “B grade SKU” as a separate catalog price permanently, they may create a second product *or* use quality grade on one product. **Recommend quality grade.**

## Tile size

**Rule:** Size master can suggest `sqft_per_piece` but product value wins.  
**Example:** 600×600 mm → 3.875 sqft theoretically; merchant may use factory 3.88. Use merchant figure.

## Purchase

**Rule:** Purchase is a financial + logistics document. Stock increases only on **receive**.  
**Reason:** Ordered ≠ in godown.  
**Example:** PO 100 box; receive 80 now, 20 later.  
**DB:** `purchases`, `purchase_items`, `goods_receipts`, `goods_receipt_items`, movements IN, supplier ledger credit (payable).  
**Edges:** Price change after receive: do not change stock qty; accountant adjustment on payable.

## Receiving

**Rule:** Receipt line must specify warehouse + lot dims + qty + unit.  
**Edges:** Over-receive vs ordered: allow with Manager permission. Damaged split.

## Sales / POS

**Rule:** Checkout is atomic: stock + sale + payments + ledger.  
**Edges:** Walk-in + due forbidden (must be named customer). Credit limit breach blocked unless override permission.

## Payments

**Rule:** Customer payment inserts ledger credit and `payments` row. Optional allocations to sales.  
**Example:** Due 60,000; pay 20,000; remaining 40,000.  
**Edges:** Overpay stored as customer advance (credit balance). Reverse payment posts opposite ledger and marks original `reversed_at`.

## Credit / due

See `14-customer-credit-due.md`. Due is not a standalone mutable column as source of truth.

## Returns

**Rule:** Sales return qty cannot exceed remaining returnable qty on the original line (original − already returned).  
**DB:** `sales_returns`, items, movements IN or DAMAGE, ledger credit note.  
**Edges:** Cash refund vs balance credit — user choice.

## Damaged products

**Rule:** Move sellable → damaged via movement pair; POS cannot sell damaged.  
**Edges:** Sell “broken tile commercial” only if quality/condition is explicitly sellable-broken product policy.

## Stock adjustments

**Rule:** Permission + reason ≥ 5 chars.  
**Edges:** Adjustment that would go negative blocked if setting off.

## Warehouse transfers

See `13`. Stock out source on dispatch; in dest on receive. In-transit warehouse virtual row or movement type.

## Delivery / challans

See `15`. **Dispatch** is the inventory event if the sale did not already deduct (recommended sale deducts at invoice; challan is then documentary). If delivery-separate-from-invoice businesses need “invoice now, stock later,” use setting `stock_deduction_mode = invoice | dispatch` (open question; default **invoice**).

## Invoice cancellation

**Rule:** Allowed same-day or with Owner permission; not if return exists; not if challan dispatched unless reversed. Restores stock and reverses ledger (payment rows remain and may become advances or also reverse).  
**Reason:** Audit.  
**Edges:** Partial payment cancel → refund workflow or leave as advance.

## Invoice editing

**Rule:** Posted invoices are immutable. Corrections via return + new sale, or cancel + recreate. Draft POS carts are editable.

## Supplier payments

Mirror of customer: debit payable ledger.

## Customer payments

See above. POS can take split tender.

## Expenses (optional MVP Could Have)

If included: simple `expenses` table, no full GL. **Recommendation:** Could Have, not Must Have.
