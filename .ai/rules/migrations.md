---
paths:
  - 'database/migrations/**'
---

# Migrations

## ERP schema follows docs/07
The relational schema is defined in docs/07-database-design.md (inventory identity in docs/06). Do not add product_variants. Stock grain is product+batch+shade+quality+warehouse+condition with unique key warehouse_stocks_lot_unique. Money is DECIMAL(16,2); SQFT is DECIMAL(16,4); products.sqft_per_box is a stored generated column (pieces_per_box * sqft_per_piece). unit_code columns FK to units.code, not units.id. Master data uses soft deletes; posted documents use status=cancelled. stock_movements and ledger tables are append-only (created_at only). The RBAC pivot table is role_permission. users.role_id and default_warehouse_id stay nullable until assigned.
