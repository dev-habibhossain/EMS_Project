# 04 — User Roles and Permissions

## Role decision

Do not freeze the six job titles as sacred. MVP ships **six roles** mapped to real shops. Permissions are the real control plane; roles are bundles.

| Role | Why it exists |
|---|---|
| Owner | Full access including settings, users, destructive audit actions. Distinct from Admin so the owner can demote an admin. |
| Admin | Day-to-day configuration and users except Owner-only settings (company delete — N/A). |
| Manager | Operational supervisor: sales, purchases, stock, reports; no user management. |
| Salesperson | POS + customer view + own sales list; no purchase cost, no adjust, no cancel without extra perm. |
| Warehouse | Receive, transfer, adjust (optional), challan dispatch; limited prices. |
| Accountant | Payments, ledgers, reports, returns money side; no raw stock adjust unless granted. |

**Assumption A-ROLE-1:** One role per user in MVP. Extra permissions can be attached later via a permission pivot.

## Hierarchy (conceptual, not inheritance in code)

Owner > Admin > Manager > (Salesperson ∥ Warehouse ∥ Accountant)

Do not implement hierarchical role inheritance. Assign explicit permission sets.

## Permission catalog (slug examples)

`dashboard.view`  
`products.view|create|update|delete`  
`masterdata.manage`  
`inventory.view` `inventory.adjust`  
`warehouses.view|manage` `transfers.create|dispatch|receive`  
`pos.use` `sales.view` `sales.cancel` `sales.discount.unlimited`  
`purchases.view|create|receive|return`  
`customers.view|manage` `customers.ledger` `customers.credit.override`  
`suppliers.view|manage` `suppliers.ledger`  
`payments.customer` `payments.supplier` `payments.reverse`  
`returns.sales` `returns.purchase`  
`challans.view|create|dispatch`  
`reports.sales` `reports.stock` `reports.finance`  
`users.manage` `roles.manage`  
`settings.manage`  
`sms.send`  
`audit.view`

## Role × module matrix

| Module | Owner | Admin | Manager | Sales | Warehouse | Accountant |
|---|---|---|---|---|---|---|
| Dashboard | All | All | All | Limited | Limited | Finance KPIs |
| POS | ✓ | ✓ | ✓ | ✓ | ✗ | ✗ |
| Products | CRUD | CRUD | CRUD | View | View | View |
| Master data | ✓ | ✓ | ✓ | View | View | View |
| Inventory view | ✓ | ✓ | ✓ | View sellable | ✓ | ✓ |
| Stock adjust | ✓ | ✓ | ✓ | ✗ | ✓ | ✗ |
| Warehouses | ✓ | ✓ | ✓ | Default WH only | Assigned | View |
| Transfers | ✓ | ✓ | ✓ | ✗ | ✓ | View |
| Purchases | ✓ | ✓ | ✓ | ✗ | Receive only | View + pay |
| Sales docs | ✓ | ✓ | ✓ | Own + POS | View pick list | ✓ |
| Cancel sale | ✓ | ✓ | ✓ | ✗ | ✗ | ✓ |
| Customers | ✓ | ✓ | ✓ | ✓ | Limited | ✓ |
| Credit override | ✓ | ✓ | ✓ | ✗ | ✗ | ✓ |
| Suppliers | ✓ | ✓ | ✓ | ✗ | View | ✓ |
| Payments | ✓ | ✓ | ✓ | Collect on POS | ✗ | ✓ |
| Reverse payment | ✓ | ✓ | ✗ | ✗ | ✗ | ✓ |
| Returns | ✓ | ✓ | ✓ | Create request | Restock | Money |
| Challans | ✓ | ✓ | ✓ | Create | Dispatch | View |
| Reports stock | ✓ | ✓ | ✓ | Limited | ✓ | ✓ |
| Reports finance | ✓ | ✓ | ✓ | ✗ | ✗ | ✓ |
| Users | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| Settings | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| SMS | ✓ | ✓ | ✓ | Auto only | ✗ | Manual reminder |

## Access rules

- Walk-in cash sales do not require customer create permission beyond selecting Walk-in.
- Cost price hidden from Salesperson.
- Delete of master data blocked if stock or document references exist (soft hide).
- Owner-only: change `allow_negative_stock`, manage Owner users.

## UI enforcement

Hide buttons the user cannot use. Still enforce Policies on the server. Never trust the sidebar.
