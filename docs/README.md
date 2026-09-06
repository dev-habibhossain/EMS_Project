# Tile & Ceramic ERP + POS — Documentation Package

Single-business ERP + POS for tile and ceramic retail/wholesale in Bangladesh.

**Stack (fixed):** Laravel + Inertia.js + Vue.js + MySQL + Tailwind CSS + Vite  
**MVP model:** One company. Not SaaS. Multi-tenant SaaS is a later roadmap only.

## Purpose of this package

This repository is the **blueprint before code**. It defines product scope, requirements, inventory mathematics, database design, architecture, workflows, APIs, security, testing, and delivery order.

Do not treat any single file as complete in isolation. Cross-check inventory rules, database design, and workflows together.

## Recommended reading order

1. `01-project-overview.md` — why the product exists
2. `02-prd.md` — product requirements and journeys
3. `03-project-requirements.md` — numbered FR/NFR
4. `06-inventory-business-rules.md` — **read before coding inventory**
5. `05-business-logic.md`
6. `07-database-design.md`
7. `08-system-architecture.md`
8. `04-user-roles-permissions.md`
9. Workflows: `11`–`15`
10. `09-api-planning.md`, `16`–`19`
11. `20-development-roadmap.md`, `21-testing-strategy.md`
12. `22-future-saas-roadmap.md` (do not implement in MVP)
13. `23-open-questions.md`
14. `24-final-system-blueprint.md`
15. `FINAL-REVIEW.md`

## Source of truth

| Concern | Source of truth |
|---|---|
| Product scope / MVP | `02-prd.md`, `25` section in PRD + roadmap |
| Inventory math | `06-inventory-business-rules.md` |
| Schema | `07-database-design.md` |
| Roles | `04-user-roles-permissions.md` |
| Architecture | `08-system-architecture.md` |
| Credit/due | `14-customer-credit-due.md` |
| Assumptions | `23-open-questions.md` |
| Executive summary | `24-final-system-blueprint.md` |

## MVP development order

Foundation → Auth/RBAC → Master data → Inventory/units → Warehouses → Purchases → POS/Sales → Customers/ledgers → Payments/returns → Challans → Reports → Localization → SMS → Hardening/tests → Deploy.

See `20-development-roadmap.md`.

## What this package is not

- Application source code
- Laravel migrations or Vue components
- A multi-tenant SaaS design for v1
