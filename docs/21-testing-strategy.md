# 21 — Testing Strategy

## Layers

| Layer | Tool | Focus |
|---|---|---|
| Unit | PHPUnit | ConversionService, rounding, ledger math |
| Feature | PHPUnit + RefreshDatabase | checkout, receive, transfer, cancel |
| Integration | Feature + queue fake | SMS job |
| API | Feature HTTP | /api/v1/pos/checkout |
| Browser | Laravel Dusk or Playwright | POS happy path |
| Permission | Feature actingAs each role | matrix |
| Security | Feature | CSRF, forbidden 403 |

## Critical conversion cases

- BOX→PCS, PCS→SQFT, BOX→SQFT, SQFT→BOX remainder
- 5 BOX + 20 PCS + 53.80 SQFT = 161.40 SQFT with 4 pcs, 2.69
- Partial box leftover display
- Decimal 16,4 no float
- Factors snapshotted if product later changes

## Domain cases

- Return restores same batch/shade
- Transfer in-transit not sellable
- Concurrent two checkouts last box — one 422
- Credit sale walk-in rejected
- Payment reverse
- Due = sum ledger
- Cancel sale restores stock and ledger
- Negative stock setting on/off
- Damaged not in POS search
- Purchase partial receive
- Over-receive permission

## Concurrency test approach

Use `Database::transaction` + two processes or `lockForUpdate` assertion with manual thread simulation; at minimum serialize two sequential checkouts where second sees updated qty.
