---
paths:
  - 'app/Models/**'
---

# Models

## Do not name a relationship factory()
App\Models\Factory maps to the factories table. Never name a relationship factory() — it shadows HasFactory::factory() and breaks Model::factory(). Use tileFactory() with foreign key factory_id. The RBAC pivot is role_permission (not permission_role). unit_code relations use belongsTo(Unit::class, 'unit_code', 'code'). StockMovement and ledger models are append-only (UPDATED_AT = null).
