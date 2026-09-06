# 06 — Inventory Business Rules (Tile Domain)

This document is the source of truth for stock mathematics. Implementation must not invent a second model.

## 1. Recommended inventory identity

**Stock is not “a product.”**  
**Stock is a lot-location row:**

`product_id + batch_id + shade_id + quality_grade_id + warehouse_id`

### Why not product-only?

Two boxes of the “same” 600×600 design can be unmixable on site if shade B2 vs B4. Factories ship batches. Quality A vs B has different price. Godown vs showroom is a different pile.

### Why not more dimensions?

Factory and brand belong on the **product** (or product–factory). Size belongs on product. Adding them to the stock key duplicates data and explodes POS picking.

### Practical rule

- Product = sellable catalog item (design + size + brand + default factors).
- Lot dims = batch, shade, quality.
- Location dim = warehouse.
- If a shop does not use shades, they still select a **default shade** “N/A” so the key stays stable.

## 2. Units

System units (fixed in MVP):

| Code | Meaning |
|---|---|
| SQFT | Square feet — **canonical stock unit** |
| PCS | Piece (one tile) |
| BOX | Factory carton |

### Factors on the product (required)

- `pieces_per_box` — integer ≥ 1 (typical 4, 5, 6, 8…)
- `sqft_per_piece` — decimal > 0 (e.g. 2.69 for ~16"×16" class; exact from size)

Derived:

\[
\text{sqft\_per\_box} = \text{pieces\_per\_box} \times \text{sqft\_per\_piece}
\]

Example:

- 1 BOX = 4 PCS
- 1 PCS = 2.69 SQFT
- 1 BOX = 10.76 SQFT

Stock example: 10 BOX = 40 PCS = 107.60 SQFT

## 3. Base unit decision

**Recommendation (A-INV-1): store `qty_sqft` as the only authoritative on-hand quantity.**

Reasons:

1. SQFT is the finest commercial unit the merchant already uses.
2. Box and piece are integer groupings of SQFT; converting up is division.
3. Selling 53.80 SQFT does not require inventing fractional boxes in storage.
4. One column avoids three-quantity drift.

**Display** always shows three measures computed from `qty_sqft`:

\[
\text{pcs} = \frac{\text{qty\_sqft}}{\text{sqft\_per\_piece}}
\]
\[
\text{box} = \frac{\text{pcs}}{\text{pieces\_per\_box}}
\]

Optional denormalized `qty_pieces` is **not** source of truth. If used for query speed, it must be written in the same transaction from SQFT.

### Alternative rejected

Storing only integer pieces fails SQFT-only sales and tiles with non-terminating area. Storing three independent quantities guarantees drift.

## 4. Conversion

All user quantities convert to SQFT **before** stock math.

| User unit | To SQFT |
|---|---|
| SQFT | `qty` |
| PCS | `qty * sqft_per_piece` |
| BOX | `qty * pieces_per_box * sqft_per_piece` |

Reverse (display or invoicing in user unit): divide.

Conversion must use the **product factors at transaction time**, snapshotted on the document line (`pieces_per_box`, `sqft_per_piece`) so later master-data edits do not rewrite history.

## 5. Decimal precision and rounding

| Kind | Storage | Rule |
|---|---|---|
| Money | DECIMAL(16,2) | Round half-up to 2 at line total |
| SQFT stock | DECIMAL(16,4) | Compute exact; store 4 dp |
| SQFT display | 2–4 dp | Setting |
| Pieces | May be non-integer internally | Policy below |
| Boxes | May be fractional internally | Policy below |

**Piece integrity policy (A-INV-2):**  
Default **allow fractional boxes**, **require piece qty to map to whole pieces** when selling in PCS. Selling in SQFT may produce a remainder that is not a whole piece (e.g. offcut commercial sales). When that happens, stock still drops by that SQFT.

**Rounding rule:** never round SQFT *before* subtracting during a multi-line conversion of the same lot in one invoice. Sum line SQFT then subtract once per lot per checkout, or subtract line-by-line at 4 dp consistently. Tests must lock this.

Worked example:

- Customer buys 5 BOX + 20 PCS + 53.80 SQFT  
- Product 4 pcs/box, 2.69 sqft/pc  
- SQFT = 5*10.76 + 20*2.69 + 53.80 = 53.80 + 53.80 + 53.80 = **161.40 SQFT**  
- Deduct 161.40 from that lot’s `qty_sqft`.

## 6. Partial boxes

On-hand 2.5 boxes is legal if someone sold 2 pieces from a box (2/4 = 0.5 box). POS should show:

`2 BOX + 2 PCS` **or** `2.5 BOX` — prefer mixed display: whole boxes + leftover pieces.

Helper:

```
whole_boxes = floor(pcs / pieces_per_box)
leftover_pcs = pcs - whole_boxes * pieces_per_box
```

where `pcs = qty_sqft / sqft_per_piece` (warn if pcs not near-integer beyond epsilon 0.0001).

## 7. Selling units

Each line has `unit_code` and `qty_input`. Price is **per that unit** unless staff switches.

If price list missing for a unit, derive from another unit via SQFT so that:

\[
P_{box} \approx P_{pc} \times pieces\_per\_box \approx P_{sqft} \times sqft\_per\_box
\]

Staff may override price with permission.

## 8. Stock reservation

MVP: validate at add-to-cart and **re-validate + lock row** at checkout (`SELECT … FOR UPDATE` on `warehouse_stocks`).

Should Have: `stock_reservations` for held carts / undelivered invoiced goods if invoice does not immediately deduct (not recommended). **Recommended:** invoice checkout deducts immediately.

## 9. Negative stock

Setting `allow_negative_stock` default **false**.  
If true, movement still writes; on-hand may go negative; report flags it. Owner-only setting.

## 10. Damaged / broken

Do **not** keep damaged in the same sellable `qty_sqft` without a flag.

**Recommendation A-INV-3:** same stock key plus `condition` enum `sellable | damaged`  
or quality grade “DAMAGED” plus policy that POS filters `sellable` only.

Preferred: `condition` column on `warehouse_stocks` so quality “A” damaged is not confused with grade B.

Movements: `DAMAGE`, `REPAIR` (rare), `SALE_RETURN_DAMAGED`.

Broken tiles received from factory: receive split lines.

## 11. Returned stock

Sales return of sellable goods: `qty_sqft` up on original lot key if lot still exists; else user picks destination lot/warehouse.

Do not “average” returned shade into another shade.

## 12. Adjustments

Types: `COUNT`, `LOSS`, `FOUND`, `OPENING`, `DAMAGE`.  
Require reason. Write movement. Never update `qty_sqft` without movement.

## 13. Stock ledger (movements)

Every change:

| Field | Notes |
|---|---|
| id | PK |
| warehouse_stock_id or decomposed FKs | |
| direction | in/out |
| qty_sqft | signed or direction + abs |
| unit_input, qty_input | audit |
| document_type, document_id | sale, purchase_receive, transfer, … |
| occurred_at, user_id | |
| unit factors snapshot | |

On-hand = SUM(movements) optionally cached on `warehouse_stocks.qty_sqft`. **Cache must update in the same transaction.** Nightly reconcile job recommended.

## 14. Batch, shade, quality, warehouse

- Batch: factory lot code; required if product `requires_batch` (default true).
- Shade: required if `requires_shade` (default true).
- Quality: default A.
- Warehouse: always required.

POS: if only one lot has stock, auto-select; if many, force picker (show available box/pcs).

## 15. Consistency protocol

1. Begin transaction.  
2. Lock stock rows for all cart lots.  
3. Recompute availability.  
4. Insert sale + lines (with snapshots).  
5. Insert movements.  
6. Update cached `qty_sqft`.  
7. Insert ledger lines.  
8. Commit.  
9. Queue SMS.

Failure anywhere → full rollback.

## 16. More examples

**Ex A — leftover pieces**  
Start: 1 BOX = 4 PCS = 10.76 SQFT. Sell 1 PCS. Left: 8.07 SQFT = 3 PCS = 0.75 BOX.

**Ex B — SQFT sale**  
Sell 5.38 SQFT (= 2 PCS exactly). Deduct 5.38.

**Ex C — inexact SQFT**  
Sell 6.00 SQFT. pcs = 6/2.69 ≈ 2.2305. Allowed under SQFT policy. Display warning: “Not a whole piece.”

**Ex D — two warehouses**  
Showroom 2 BOX, Godown 50 BOX. POS on showroom cannot sell 3 BOX unless transfer or negative allowed.

**Ex E — concurrent**  
Both cashiers sell last 1 BOX. Second checkout fails with STALE_STOCK.

**Ex F — return**  
Return 1 PCS to sellable: +2.69 SQFT same lot.

**Ex G — transfer**  
Godown −10.76, in-transit +10.76, then godown in-transit −10.76, showroom +10.76. Company sellable unchanged after receive; during transit source already reduced.
