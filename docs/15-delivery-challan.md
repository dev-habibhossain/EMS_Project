# 15 — Delivery Challan

## Fields

number, customer, destination address, warehouse, sale_id optional, driver, vehicle, notes, status, items (lot + unit + qty), created_by, dispatched_at.

Print: company header, party, item table with batch/shade, signature lines (receiver, driver).

## When to deduct stock

Options:

1. On challan create  
2. On dispatch  
3. On delivery confirm  
4. On invoice (independent of challan)

**Recommendation A-CHL-2 (combined with A-CHL-1):**

- **Default `stock_deduction_mode = invoice`:** POS/sale already deducted. Challan is a **delivery document** only. Dispatch does not deduct again. Prevents double-out.
- If a business delivers first and invoices later (less common at counter), they set `stock_deduction_mode = dispatch`. Then invoice must not deduct again; it only references already-moved stock.

MVP implements **invoice deduction + documentary challan** to keep POS simple. Dispatch-mode is a flagged extension.

Do not deduct on mere draft create. Do not wait for “delivered” (drivers often fail to update the app).

## Status

draft → dispatched → delivered (optional) / cancelled.

Cancel draft: no stock effect. Cancel dispatched under invoice-mode: no stock effect. Under dispatch-mode: reverse movements.

## Relation to sales

Create “Challan from sale” copies remaining undelivered qty (track `qty_challaned_sqft` on sale items — Should Have). MVP may allow challan without that control but print warning.
