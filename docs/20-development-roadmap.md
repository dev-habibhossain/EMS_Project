# 20 — Development Roadmap

Order follows dependencies: factors before stock before POS.

## Phase 1 — Foundation
Features: Laravel + Vue + Inertia + Tailwind + Vite + auth scaffold + layout.  
DB: users.  
Done: login page, empty dashboard.

## Phase 2 — Auth & roles
Permissions seed, policies, user admin.  
Done: salesperson cannot open settings.

## Phase 3 — Products & masters
Factories, brands, sizes, units, shades, grades, products, prices.  
Done: create product with conversion preview.

## Phase 4 — Inventory
warehouse_stocks, movements, ConversionService, opening stock, adjust.  
Tests: unit conversion suite.  
Done: on-hand displays 3 units.

## Phase 5 — Warehouses
Transfers + in-transit.  
Done: transfer receive changes two warehouses.

## Phase 6 — Purchases
Purchases, GR, supplier ledger.  
Done: receive increases stock and payable.

## Phase 7 — POS & sales
POS UI, checkout TX, print.  
Done: SC-1 conversion sale.

## Phase 8 — Customers & credit
Limits, statements.  
Done: due matches ledger.

## Phase 9 — Payments & returns
Reversal, sales/purchase returns.  
Done: return restores lot.

## Phase 10 — Challans
Documentary challan + print.

## Phase 11 — Reports
Core set + CSV.

## Phase 12 — BN/EN
Lang files, invoice language.

## Phase 13 — SMS
Driver + queue + logs.

## Phase 14 — Testing & hardening
Concurrent stock test, permission matrix tests, backup runbook.

## Phase 15 — Deployment
Env, queue worker, scheduler, HTTPS, seed Owner.

Each phase: feature tests before calling done. No SaaS work in these phases.
