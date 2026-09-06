---
paths:
  - 'database/seeders/**'
---

# Seeders

## Seed six real roles then three dummy rows
RBAC is seeded from docs/04, not dummy roles: Owner, Admin, Manager, Salesperson, Warehouse, Accountant plus the permission catalog including roles.manage. Owner is the only role granted roles.manage. DemoDataSeeder then inserts three dummy rows per operational table in FK order (units/roles first, documents last). Login owner is test@example.com / password.
