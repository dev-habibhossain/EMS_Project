# 19 — Security Architecture

## Authentication
Laravel session for web. Secure cookie, HTTPS in production, `SESSION_SAME_SITE=lax`. Passwords hashed. Optional Sanctum later.

## Authorization
Gates/Policies on every write. UI hide + server deny.

## RBAC
See `04`. Seed roles. Prevent privilege escalation: cannot assign role higher than self; only Owner assigns Owner.

## Session / CSRF / XSS / SQLi
Standard Laravel CSRF. Escape Vue text; `v-html` banned except sanitized print. Eloquent bindings.

## Validation
Form Requests; min/max decimals; unit allow-list.

## Rate limiting
Login, SMS, API.

## Audit
`activity_logs` for cancel, adjust, reverse, permission changes. Stock movements immutable.

## Uploads
Logo image mime + size cap; store outside public exec; random names.

## Sensitive data
SMS credentials encrypted cast. No PAN/card storage.

## Backups
Ops run `mysqldump` daily; document in deploy phase. Not an app feature.

## DB
Least-privilege app user. No DROP from app.

## Future SaaS isolation
Not in MVP. Later: `organization_id` on every table + global scopes. See `22`. Do not add nullable tenant_id now unless it costs nothing — **recommendation: do not add in MVP** to avoid every query filter bug.

## Permission enforcement checklist
Middleware `auth` + `verified` optional + `active` user. Policy per resource. POS checkout service re-checks `pos.use` and warehouse.
