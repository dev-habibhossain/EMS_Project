# 14 — Customer Credit / Due and Supplier Payables

## Example

Invoice ৳100,000; paid ৳40,000; due ৳60,000. Later pay ৳20,000; remaining ৳40,000.

Ledger (receivable increases on debit):

| Date | Desc | Debit | Credit | Balance |
|---|---|---|---|---|
| D1 | Opening | 0 | 0 | 0 |
| D1 | Sale INV-1 | 100000 | | 100000 |
| D1 | Payment | | 40000 | 60000 |
| D8 | Payment | | 20000 | 40000 |

## Ledger vs stored due

**Recommendation A-LED-1: ledger is source of truth.**  
`customers.cached_balance` is a performance cache updated in the same transaction. UI may show cache; statement always from lines. Nightly job flags drift.

Why not only a due column? Cancels, returns, opening balances, advances, and reversals will desynchronize a single mutable field with no history.

## Entry types

| Event | Debit | Credit |
|---|---|---|
| Opening receivable | amount | |
| Opening advance | | amount |
| Sale posted | grand_total | |
| Payment | | amount |
| Credit note / return | | amount |
| Cancel sale | | original grand_total |
| Payment reverse | amount | |

## Advance / overpayment

Balance may go credit (negative receivable). Next sale consumes it if `auto_apply_advance` (Should Have; MVP manual is OK).

## Credit limit

If `cached_balance + new_due > credit_limit` block unless override permission.

## Supplier payable

Purchase receive: credit payable `line totals received`. Payment: debit payable. Mirror cache on suppliers.

## Statements

Filter date; running balance; print BN/EN; CSV.

## Payment allocation

Should Have. MVP may leave unallocated (FIFO implied in reports only). Open question in `23`.
