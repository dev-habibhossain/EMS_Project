# 16 — Reports and Analytics

| Report | Filters | Notes |
|---|---|---|
| Daily sales | date, warehouse, user | totals + counts |
| Monthly sales | month, warehouse | |
| Purchases | date, supplier | |
| Gross margin | date | revenue − purchase cost using moving avg **or** last cost. **Assumption A-RPT-1:** MVP uses last purchase unit cost snapshot on product or sale line cost snapshot at sale time (better). Snapshot `unit_cost_sqft` on sale_items at checkout. |
| Stock on hand | warehouse, brand, product | box/pcs/sqft |
| Low stock | threshold per product optional | |
| Dead stock | no movement N days | Could Have |
| Customer due | as-of date | from ledger |
| Supplier payable | as-of | |
| Payments | method, date | |
| Product / brand / factory sales | date | qty_sqft + money |
| Salesperson | created_by | |
| Batch/shade stock | | |
| Stock movements | type, date, warehouse | |
| Returns | date | |

Export CSV Must Have; XLSX Should Have. PDF print via browser.

Permissions: finance reports vs stock reports split.

Sorting: date desc default; allow-listed columns only.
