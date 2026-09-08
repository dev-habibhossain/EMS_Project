<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Product;
use App\Models\QualityGrade;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shade;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PosController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $warehouses = Warehouse::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'code', 'name', 'name_bn', 'type']);

        $activeWarehouseId = (int) $request->input(
            'warehouse_id',
            $user?->default_warehouse_id ?: $warehouses->first()?->id
        );

        $walkInCustomer = Customer::firstOrCreate(
            ['is_walk_in' => true],
            [
                'code' => 'C-WALKIN',
                'name' => 'Walk-in Customer',
                'name_bn' => 'খুচরা ক্রেতা',
                'phone' => '01700000000',
                'credit_limit' => '0.00',
                'is_active' => true,
                'cached_balance' => '0.00',
            ]
        );

        $customers = Customer::query()
            ->where('is_active', true)
            ->orderByDesc('is_walk_in')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'name_bn', 'phone', 'credit_limit', 'is_walk_in', 'cached_balance'])
            ->map(fn (Customer $c) => [
                'id' => $c->id,
                'code' => $c->code,
                'name' => $c->name,
                'name_bn' => $c->name_bn,
                'phone' => $c->phone,
                'credit_limit' => (float) $c->credit_limit,
                'is_walk_in' => (bool) $c->is_walk_in,
                'cached_balance' => (float) $c->cached_balance,
            ]);

        $qualityGrades = QualityGrade::query()
            ->where('is_sellable', true)
            ->orderBy('sort_order')
            ->get(['id', 'code', 'name', 'name_bn', 'is_sellable'])
            ->map(fn (QualityGrade $q) => [
                'id' => $q->id,
                'code' => $q->code,
                'name' => $q->name,
                'name_bn' => $q->name_bn,
                'is_sellable' => (bool) $q->is_sellable,
            ]);

        $units = Unit::query()->pluck('code')->all();

        $products = Product::query()
            ->where('is_active', true)
            ->with([
                'brand:id,name',
                'tileSize:id,label',
                'batches:id,product_id,code,manufactured_on',
                'shades:id,product_id,code,name',
                'prices:id,product_id,unit_code,price',
                'warehouseStocks' => function ($query) use ($activeWarehouseId): void {
                    $query->where('warehouse_id', $activeWarehouseId)
                        ->where('condition', 'sellable')
                        ->with(['batch:id,code', 'shade:id,code', 'qualityGrade:id,code']);
                },
            ])
            ->get()
            ->map(function (Product $p): array {
                $piecesPerBox = (int) $p->pieces_per_box;
                $sqftPerPiece = (float) $p->sqft_per_piece;
                $sqftPerBox = (float) ($p->sqft_per_box ?: ($piecesPerBox * $sqftPerPiece));

                // Price mapping
                $boxPrice = null;
                $prices = [];
                foreach ($p->prices as $price) {
                    $prices[$price->unit_code] = (float) $price->price;
                    if ($price->unit_code === 'BOX') {
                        $boxPrice = (float) $price->price;
                    }
                }

                if ($boxPrice !== null) {
                    if (! isset($prices['PCS']) && $piecesPerBox > 0) {
                        $prices['PCS'] = round($boxPrice / $piecesPerBox, 2);
                    }
                    if (! isset($prices['SQFT']) && $sqftPerBox > 0) {
                        $prices['SQFT'] = round($boxPrice / $sqftPerBox, 2);
                    }
                }

                $lots = $p->warehouseStocks->map(fn (WarehouseStock $ws) => [
                    'id' => $ws->id,
                    'warehouse_id' => $ws->warehouse_id,
                    'batch_id' => $ws->batch_id,
                    'batch_code' => $ws->batch?->code,
                    'shade_id' => $ws->shade_id,
                    'shade_code' => $ws->shade?->code,
                    'quality_grade_id' => $ws->quality_grade_id,
                    'quality_grade_code' => $ws->qualityGrade?->code ?? 'A',
                    'condition' => $ws->condition,
                    'qty_sqft' => (float) $ws->qty_sqft,
                ]);

                $totalQtySqft = $lots->sum('qty_sqft');

                return [
                    'id' => $p->id,
                    'sku' => $p->sku,
                    'name' => $p->name,
                    'name_bn' => $p->name_bn,
                    'brand_name' => $p->brand?->name,
                    'tile_size_label' => $p->tileSize?->label,
                    'pieces_per_box' => $piecesPerBox,
                    'sqft_per_piece' => $sqftPerPiece,
                    'sqft_per_box' => $sqftPerBox,
                    'barcode' => $p->barcode,
                    'requires_batch' => (bool) $p->requires_batch,
                    'requires_shade' => (bool) $p->requires_shade,
                    'default_quality_id' => $p->default_quality_id,
                    'prices' => $prices,
                    'batches' => $p->batches->map(fn ($b) => [
                        'id' => $b->id,
                        'code' => $b->code,
                        'manufactured_on' => $b->manufactured_on?->format('Y-m-d'),
                    ])->all(),
                    'shades' => $p->shades->map(fn ($s) => [
                        'id' => $s->id,
                        'code' => $s->code,
                        'name' => $s->name,
                    ])->all(),
                    'lots' => $lots->values()->all(),
                    'total_qty_sqft' => (float) $totalQtySqft,
                ];
            });

        return Inertia::render('Pos', [
            'warehouses' => $warehouses->map(fn (Warehouse $w) => [
                'id' => $w->id,
                'code' => $w->code,
                'name' => $w->name,
                'name_bn' => $w->name_bn,
                'type' => $w->type,
            ]),
            'activeWarehouseId' => $activeWarehouseId,
            'customers' => $customers,
            'walkInCustomerId' => $walkInCustomer->id,
            'products' => $products,
            'qualityGrades' => $qualityGrades,
            'units' => $units,
        ]);
    }

    public function checkout(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'customer_id' => ['required', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.batch_id' => ['nullable', 'exists:batches,id'],
            'items.*.shade_id' => ['nullable', 'exists:shades,id'],
            'items.*.quality_grade_id' => ['required', 'exists:quality_grades,id'],
            'items.*.condition' => ['required', 'in:sellable,damaged'],
            'items.*.unit_code' => ['required', 'in:BOX,PCS,SQFT'],
            'items.*.qty_input' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_total' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,bank,mfs,other'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $user = $request->user();

        $subtotal = 0.0;
        $lineItems = [];

        foreach ($validated['items'] as $itemData) {
            $product = Product::findOrFail($itemData['product_id']);
            $qtyInput = (float) $itemData['qty_input'];
            $unitPrice = (float) $itemData['unit_price'];
            $discount = isset($itemData['discount_amount']) ? (float) $itemData['discount_amount'] : 0.0;
            $lineTotal = max(0.0, ($qtyInput * $unitPrice) - $discount);
            $subtotal += $lineTotal;

            $unitCode = strtoupper($itemData['unit_code']);
            $piecesPerBox = (int) $product->pieces_per_box;
            $sqftPerPiece = (float) $product->sqft_per_piece;
            $sqftPerBox = (float) ($product->sqft_per_box ?: ($piecesPerBox * $sqftPerPiece));

            if ($unitCode === 'BOX') {
                $qtySqft = $qtyInput * $sqftPerBox;
            } elseif ($unitCode === 'PCS') {
                $qtySqft = $qtyInput * $sqftPerPiece;
            } else {
                $qtySqft = $qtyInput;
            }

            $lineItems[] = [
                'product' => $product,
                'itemData' => $itemData,
                'qty_input' => $qtyInput,
                'qty_sqft' => round($qtySqft, 4),
                'unit_price' => $unitPrice,
                'discount_amount' => $discount,
                'line_total' => round($lineTotal, 2),
                'pieces_per_box_snapshot' => $piecesPerBox,
                'sqft_per_piece_snapshot' => $sqftPerPiece,
            ];
        }

        $discountTotal = isset($validated['discount_total']) ? (float) $validated['discount_total'] : 0.0;
        $grandTotal = max(0.0, round($subtotal - $discountTotal, 2));
        $paidTotal = min($grandTotal, (float) $validated['paid_amount']);
        $dueTotal = max(0.0, round($grandTotal - $paidTotal, 2));
        $changeAmount = max(0.0, round((float) $validated['paid_amount'] - $grandTotal, 2));

        if ($customer->is_walk_in && $dueTotal > 0.001) {
            throw ValidationException::withMessages([
                'paid_amount' => ['Walk-in customers cannot have unpaid balance (Due: ৳'.number_format($dueTotal, 2).'). Full payment is required, or select a named credit customer.'],
            ]);
        }

        $receipt = DB::transaction(function () use (
            $validated,
            $customer,
            $user,
            $subtotal,
            $discountTotal,
            $grandTotal,
            $paidTotal,
            $dueTotal,
            $changeAmount,
            $lineItems
        ): array {
            $todayStr = now()->format('Ymd');
            $saleCount = Sale::query()->whereDate('sale_at', today())->count() + 1;
            $saleNumber = 'INV-'.$todayStr.'-'.str_pad((string) $saleCount, 4, '0', STR_PAD_LEFT);

            // Ensure unique sale number
            while (Sale::query()->where('number', $saleNumber)->exists()) {
                $saleCount++;
                $saleNumber = 'INV-'.$todayStr.'-'.str_pad((string) $saleCount, 4, '0', STR_PAD_LEFT);
            }

            $sale = Sale::create([
                'number' => $saleNumber,
                'customer_id' => $customer->id,
                'warehouse_id' => $validated['warehouse_id'],
                'status' => 'posted',
                'source' => 'pos',
                'sale_at' => now(),
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => 0.00,
                'grand_total' => $grandTotal,
                'paid_total' => $paidTotal,
                'due_total' => $dueTotal,
                'created_by' => $user->id,
            ]);

            $receiptItems = [];

            foreach ($lineItems as $line) {
                $batchId = $line['itemData']['batch_id'] ?? null;
                if (! $batchId) {
                    $batch = $line['product']->batches()->first();
                    if (! $batch) {
                        $batch = Batch::firstOrCreate(
                            ['product_id' => $line['product']->id, 'code' => 'B-DEFAULT'],
                            ['manufactured_on' => now()]
                        );
                    }
                    $batchId = $batch->id;
                }

                $shadeId = $line['itemData']['shade_id'] ?? null;
                if (! $shadeId) {
                    $shade = $line['product']->shades()->first();
                    if (! $shade) {
                        $shade = Shade::firstOrCreate(
                            ['product_id' => $line['product']->id, 'code' => 'STD'],
                            ['name' => 'Standard Shade']
                        );
                    }
                    $shadeId = $shade->id;
                }

                $qualityGradeId = $line['itemData']['quality_grade_id'] ?? null;
                if (! $qualityGradeId) {
                    $qualityGradeId = $line['product']->default_quality_id ?: QualityGrade::first()?->id;
                }

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $line['product']->id,
                    'batch_id' => $batchId,
                    'shade_id' => $shadeId,
                    'quality_grade_id' => $qualityGradeId,
                    'condition' => $line['itemData']['condition'] ?? 'sellable',
                    'unit_code' => $line['itemData']['unit_code'],
                    'qty_input' => $line['qty_input'],
                    'qty_sqft' => $line['qty_sqft'],
                    'unit_price' => $line['unit_price'],
                    'discount_amount' => $line['discount_amount'],
                    'line_total' => $line['line_total'],
                    'unit_cost' => null,
                    'pieces_per_box_snapshot' => $line['pieces_per_box_snapshot'],
                    'sqft_per_piece_snapshot' => $line['sqft_per_piece_snapshot'],
                ]);

                $warehouseStock = WarehouseStock::firstOrCreate(
                    [
                        'product_id' => $line['product']->id,
                        'batch_id' => $batchId,
                        'shade_id' => $shadeId,
                        'quality_grade_id' => $qualityGradeId,
                        'warehouse_id' => $validated['warehouse_id'],
                        'condition' => $line['itemData']['condition'] ?? 'sellable',
                    ],
                    [
                        'qty_sqft' => 0,
                    ]
                );

                $warehouseStock->decrement('qty_sqft', $line['qty_sqft']);

                StockMovement::create([
                    'warehouse_stock_id' => $warehouseStock->id,
                    'product_id' => $line['product']->id,
                    'warehouse_id' => $validated['warehouse_id'],
                    'batch_id' => $batchId,
                    'shade_id' => $shadeId,
                    'quality_grade_id' => $qualityGradeId,
                    'condition' => $line['itemData']['condition'] ?? 'sellable',
                    'direction' => 'out',
                    'qty_sqft' => $line['qty_sqft'],
                    'qty_input' => $line['qty_input'],
                    'unit_code' => $line['itemData']['unit_code'],
                    'pieces_per_box_snapshot' => $line['pieces_per_box_snapshot'],
                    'sqft_per_piece_snapshot' => $line['sqft_per_piece_snapshot'],
                    'movement_type' => 'sale',
                    'document_type' => Sale::class,
                    'document_id' => $sale->id,
                    'note' => 'POS Sale '.$sale->number,
                    'occurred_at' => now(),
                    'created_by' => $user->id,
                ]);

                $receiptItems[] = [
                    'product_name' => $line['product']->name,
                    'sku' => $line['product']->sku,
                    'batch_code' => $line['itemData']['batch_code'] ?? null,
                    'shade_code' => $line['itemData']['shade_code'] ?? null,
                    'grade_code' => $line['itemData']['quality_grade_code'] ?? 'A',
                    'unit_code' => $line['itemData']['unit_code'],
                    'qty_input' => $line['qty_input'],
                    'qty_sqft' => $line['qty_sqft'],
                    'unit_price' => $line['unit_price'],
                    'discount_amount' => $line['discount_amount'],
                    'line_total' => $line['line_total'],
                ];
            }

            CustomerLedgerEntry::create([
                'customer_id' => $customer->id,
                'entry_at' => now(),
                'debit' => $sale->grand_total,
                'credit' => 0.00,
                'description' => 'Sale '.$sale->number,
                'document_type' => Sale::class,
                'document_id' => $sale->id,
                'created_by' => $user->id,
            ]);
            $customer->increment('cached_balance', $sale->grand_total);

            if ($paidTotal > 0.001) {
                $payCount = Payment::query()->whereDate('paid_at', today())->count() + 1;
                $paymentNumber = 'PAY-'.$todayStr.'-'.str_pad((string) $payCount, 4, '0', STR_PAD_LEFT);

                while (Payment::query()->where('number', $paymentNumber)->exists()) {
                    $payCount++;
                    $paymentNumber = 'PAY-'.$todayStr.'-'.str_pad((string) $payCount, 4, '0', STR_PAD_LEFT);
                }

                $payment = Payment::create([
                    'number' => $paymentNumber,
                    'party_type' => 'customer',
                    'customer_id' => $customer->id,
                    'method' => $validated['payment_method'],
                    'amount' => $paidTotal,
                    'direction' => 'in',
                    'paid_at' => now(),
                    'sale_id' => $sale->id,
                    'notes' => 'POS collection for '.$sale->number,
                    'created_by' => $user->id,
                ]);

                PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'document_type' => Sale::class,
                    'document_id' => $sale->id,
                    'amount' => $paidTotal,
                ]);

                CustomerLedgerEntry::create([
                    'customer_id' => $customer->id,
                    'entry_at' => now(),
                    'debit' => 0.00,
                    'credit' => $paidTotal,
                    'description' => 'Payment for '.$sale->number,
                    'document_type' => Payment::class,
                    'document_id' => $payment->id,
                    'created_by' => $user->id,
                ]);
                $customer->decrement('cached_balance', $paidTotal);
            }

            $warehouse = Warehouse::find($validated['warehouse_id']);

            return [
                'invoice_number' => $sale->number,
                'sale_at' => $sale->sale_at->format('Y-m-d h:i A'),
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone,
                'customer_code' => $customer->code,
                'is_walk_in' => (bool) $customer->is_walk_in,
                'warehouse_name' => $warehouse?->name ?? 'Showroom',
                'warehouse_code' => $warehouse?->code ?? 'SR-1',
                'cashier_name' => $user->name,
                'items' => $receiptItems,
                'subtotal' => (float) $subtotal,
                'discount_total' => (float) $discountTotal,
                'grand_total' => (float) $grandTotal,
                'paid_total' => (float) $paidTotal,
                'due_total' => (float) $dueTotal,
                'change_amount' => (float) $changeAmount,
                'payment_method' => strtoupper($validated['payment_method']),
            ];
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'receipt' => $receipt,
                'message' => 'Sale #'.$receipt['invoice_number'].' completed successfully',
            ]);
        }

        return back()
            ->with('receipt', $receipt)
            ->with('success', 'Sale #'.$receipt['invoice_number'].' completed successfully');
    }
}
