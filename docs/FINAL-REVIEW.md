# FINAL-REVIEW — Consistency Check

Reviewed: PRD → FR → business logic → inventory → DB → architecture → API → permissions → workflows → tests.

## Checks

| Question | Result |
|---|---|
| Does DB support every business rule? | Yes: stock unique key, movements, ledgers, snapshots, receipts, transfers, challans. |
| Box/Piece/SQFT? | Yes: factors on products + qty_sqft + unit on lines. |
| Partial boxes? | Yes: fractional display from SQFT. |
| Batch/shade/quality? | Yes: required dims on stock. |
| Multiple warehouses? | Yes + virtual in-transit. |
| Returns restore correct stock? | Yes if lot preserved; user picks dest otherwise. |
| Credit auditable? | Immutable ledger + cache. |
| Payments reversible? | reversed_at + opposite ledger. |
| Concurrent sales? | FOR UPDATE on warehouse_stocks. |
| Permissions sufficient? | Matrix covers cancel, adjust, POS, finance. |
| Laravel fit? | Yes. |
| Vue+Inertia UI? | Yes; POS as Inertia page. |
| MySQL fit? | DECIMAL + unique + transactions. |
| APIs only where useful? | Yes. |
| MVP too big? | Contained by Must/Should/Not now. Expenses/VAT/SaaS out. |
| SaaS kept out? | Yes; `22` separate; no tenant_id in `07`. |

## Contradictions found and resolved

1. **Challan vs invoice double stock-out** — Resolved: default deduct on invoice; challan documentary (`15`, Q4).  
2. **product_variants vs lots** — Resolved: no variants table in MVP (`07`).  
3. **Due column vs ledger** — Resolved: ledger SoT, cache allowed (`14`).  
4. **Sale edit** — Resolved: immutable posted sales (`05`, `11`).  
5. **Triple quantity storage** — Rejected in `06`.

## Residual risks

- Merchants who insist on integer-only pieces and refuse SQFT sales need a setting later (`require_whole_pieces` for all units).  
- Cost snapshot needs purchase history; first sales may have null cost (margin N/A).  
- Warehouse user scoping deferred.

## Sign-off

Documentation package is internally consistent for implementation kickoff. Code must not deviate from `06` and `07` without updating this review.
