---
paths:
  - 'database/seeders/**'
---

# Seeders

## Two shop roles admin and sales shop
Single showroom uses two roles only: Admin (slug admin, the owner) and Sales shop (slug sales_shop). Seed mandatory plus recommended grants from docs/04. Admin gets the full shop including users/settings; Sales shop gets POS, sales, customers, inventory view, and recommended challans/returns/ledger. Hide nav items the role cannot use. DemoDataSeeder inserts three dummy rows per operational table in FK order. Login admin is test@example.com / password.
