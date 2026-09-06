# 23 — Open Questions and Assumptions

| ID | Question | Why it matters | Options | Recommended | Impact |
|---|---|---|---|---|---|
| Q1 | Canonical stock unit? | Drift | SQFT vs PCS vs triple qty | **SQFT** (A-INV-1) | Schema + all conversions |
| Q2 | Stock grain? | POS complexity | Product only vs lot+WH | **product+batch+shade+quality+WH+condition** | Unique key |
| Q3 | Negative stock? | Oversell | Always / never / setting | **Setting default off** | Checkout |
| Q4 | When does stock leave for delivery? | Double deduct | Invoice vs dispatch vs delivered | **Invoice; challan documentary** | Challan module |
| Q5 | Edit posted invoice? | Audit | Edit / cancel+new / credit note | **Immutable + return/cancel** | Sales UI |
| Q6 | Payment allocation to invoices? | Statements | None / FIFO / manual | **Manual optional later; cache balance now** | payments tables |
| Q7 | VAT/BIN full engine? | Legal | Full / optional fields / none | **Optional fields only** | Reports |
| Q8 | Fractional pieces when selling SQFT? | Floor tiles | Ban / allow with warn | **Allow with warn** | POS |
| Q9 | In-transit modeling? | Transfers | Virtual WH vs condition | **Virtual warehouse** | WH seed |
| Q10 | Costing method for profit? | Reports | Last cost / avg / snapshot | **Snapshot cost on sale line** | sale_items |
| Q11 | Warehouse-scoped users? | Multi-godown | Global / scoped | **Global MVP; pivot later** | RBAC |
| Q12 | Hold carts / reservation table? | Abandoned POS | Memory / DB reserve | **No table MVP; lock at checkout** | Inventory |
| Q13 | Standalone GR without PO? | Small shops | Require PO / allow | **Allow and auto purchase** | Purchases |
| Q14 | SMS vendor? | Ops | One vendor / interface | **Interface + log driver** | SMS |
| Q15 | Number grouping? | Print | Lakh / international | **Helper; default lakh on BN print** | i18n |
| Q16 | One role vs many? | Users | Single / multiple | **Single role MVP** | users.role_id |

All recommendations are **assumptions** until the business owner overrides them. Changing Q1 or Q2 after coding is expensive.
