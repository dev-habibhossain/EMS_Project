---
paths:
  - routes/web.php
---

# Routes

## ERP screens use controllers in web.php
Do not use Route::inertia() for ERP modules. Each screen has a controller in app/Http/Controllers registered as a named GET in routes/web.php (products.index, sales.index, etc.). Index methods may render the shared ModuleIndex page until that module has live data; then swap to a dedicated Inertia page and query from the same controller.
