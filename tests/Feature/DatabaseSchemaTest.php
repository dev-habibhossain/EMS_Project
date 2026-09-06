<?php

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('erp tables exist after migrate', function (string $table) {
    expect(Schema::hasTable($table))->toBeTrue();
})->with([
    'roles',
    'permissions',
    'role_permission',
    'factories',
    'brands',
    'tile_sizes',
    'units',
    'quality_grades',
    'warehouses',
    'products',
    'product_prices',
    'shades',
    'batches',
    'warehouse_stocks',
    'stock_movements',
    'customers',
    'suppliers',
    'purchases',
    'purchase_items',
    'goods_receipts',
    'goods_receipt_items',
    'sales',
    'sale_items',
    'payments',
    'payment_allocations',
    'customer_ledger_entries',
    'supplier_ledger_entries',
    'warehouse_transfers',
    'warehouse_transfer_items',
    'delivery_challans',
    'delivery_challan_items',
    'sales_returns',
    'sales_return_items',
    'purchase_returns',
    'purchase_return_items',
    'settings',
    'sms_logs',
    'activity_logs',
    'expenses',
]);

test('users keep erp role warehouse and soft delete columns', function () {
    expect(Schema::hasColumns('users', [
        'username',
        'phone',
        'role_id',
        'locale',
        'default_warehouse_id',
        'is_active',
        'deleted_at',
    ]))->toBeTrue();
});

test('users can be created before a role is assigned', function () {
    $user = User::factory()->create();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'locale' => 'en',
        'is_active' => true,
        'role_id' => null,
    ]);
});

test('stock movements and ledgers are append-only without updated_at', function () {
    expect(Schema::hasColumn('stock_movements', 'created_at'))->toBeTrue();
    expect(Schema::hasColumn('stock_movements', 'updated_at'))->toBeFalse();
    expect(Schema::hasColumn('customer_ledger_entries', 'created_at'))->toBeTrue();
    expect(Schema::hasColumn('customer_ledger_entries', 'updated_at'))->toBeFalse();
    expect(Schema::hasColumn('supplier_ledger_entries', 'created_at'))->toBeTrue();
    expect(Schema::hasColumn('supplier_ledger_entries', 'updated_at'))->toBeFalse();
});

test('warehouse stock rejects a second row with the same lot identity', function () {
    $lot = createLotIdentity();

    $row = [
        ...$lot,
        'condition' => 'sellable',
        'qty_sqft' => 10.7600,
        'created_at' => now(),
        'updated_at' => now(),
    ];

    DB::table('warehouse_stocks')->insert($row);

    expect(fn () => DB::table('warehouse_stocks')->insert($row))
        ->toThrow(QueryException::class);
});

test('sales reject a duplicate document number', function () {
    $user = User::factory()->create();
    $customerId = DB::table('customers')->insertGetId([
        'code' => 'C-001',
        'name' => 'Walk-in',
        'phone' => '01700000000',
        'is_walk_in' => true,
        'is_active' => true,
        'cached_balance' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $warehouseId = DB::table('warehouses')->insertGetId([
        'code' => 'SR-1',
        'name' => 'Showroom',
        'type' => 'showroom',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sale = [
        'number' => 'INV-1001',
        'customer_id' => $customerId,
        'warehouse_id' => $warehouseId,
        'status' => 'posted',
        'source' => 'pos',
        'sale_at' => now(),
        'subtotal' => 1000,
        'discount_total' => 0,
        'tax_total' => 0,
        'grand_total' => 1000,
        'paid_total' => 1000,
        'due_total' => 0,
        'created_by' => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ];

    DB::table('sales')->insert($sale);

    expect(fn () => DB::table('sales')->insert($sale))
        ->toThrow(QueryException::class);
});

/**
 * @return array{product_id: int, batch_id: int, shade_id: int, quality_grade_id: int, warehouse_id: int}
 */
function createLotIdentity(): array
{
    $productId = DB::table('products')->insertGetId([
        'sku' => 'RAK-60-WHT',
        'name' => 'Carrara 600 White',
        'pieces_per_box' => 4,
        'sqft_per_piece' => 2.690000,
        'requires_batch' => true,
        'requires_shade' => true,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [
        'product_id' => $productId,
        'batch_id' => DB::table('batches')->insertGetId([
            'product_id' => $productId,
            'code' => 'B-2026-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]),
        'shade_id' => DB::table('shades')->insertGetId([
            'product_id' => $productId,
            'code' => 'A1',
            'name' => 'A1',
            'created_at' => now(),
            'updated_at' => now(),
        ]),
        'quality_grade_id' => DB::table('quality_grades')->insertGetId([
            'code' => 'A',
            'name' => 'Grade A',
            'is_sellable' => true,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]),
        'warehouse_id' => DB::table('warehouses')->insertGetId([
            'code' => 'GD-1',
            'name' => 'Godown 1',
            'type' => 'godown',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]),
    ];
}
