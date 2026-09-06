# 22 — Future SaaS Roadmap (Not MVP)

MVP = one database, one company, no tenant_id.

## Later introduction path

1. Add `organizations` (tenant) table.  
2. Add `organization_id` to every business table.  
3. Global Eloquent scopes.  
4. Organization admin vs platform super-admin.  
5. Billing: plans, limits (warehouses, users, SMS), trials — **separate billing schema**.  
6. Isolation: start with shared DB + tenant_id; consider DB-per-tenant only for large customers.  
7. File storage prefixed by org.  
8. Queue jobs always carry org id.  
9. No cross-tenant reports.

## What MVP must not do

Tenant middleware, plan gates, onboarding wizard, stripe/bKash subscriptions, “switch company.”

## What MVP may do to stay compatible

Keep settings in a table (not hardcoded). Keep roles data-driven. Avoid singleton assumptions beyond `settings`. That is enough.
