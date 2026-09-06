# 12 — Purchase Workflow

```mermaid
flowchart TD
  S[Supplier] --> P[Purchase draft]
  P --> L[Lines: product qty unit price]
  L --> O[Ordered]
  O --> R[Receive]
  R --> B[Assign batch shade quality warehouse]
  B --> I[Stock IN sellable and/or damaged]
  I --> Y[Supplier ledger payable]
```

## Receiving

- Partial receive allowed; `qty_received` accumulates.
- Status: draft → ordered → partial → received.
- Direct receive without PO: allowed for small shops (creates purchase implied or `goods_receipt` standalone). **Assumption A-PUR-1:** standalone GR creates a purchase document automatically so payables stay consistent.

## Damaged receiving

Split qty on UI: 100 box good + 2 box damaged → two movement conditions.

## Purchase return

Select received lots, qty not exceeding remaining (received − already returned), stock OUT, supplier ledger debit (reduce payable) or create receivable if already paid.

## Corrections

Wrong batch posted: do not edit movement. Adjustment OUT wrong lot + IN correct lot with reason `receive_correction`, linked documents.
