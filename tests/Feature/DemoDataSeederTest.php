<?php

use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Models\WarehouseStock;
use Database\Seeders\DatabaseSeeder;

test('the database seeder inserts three dummy rows for each operational table', function () {
    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseCount('units', 3);
    $this->assertDatabaseCount('warehouses', 3);
    $this->assertDatabaseCount('factories', 3);
    $this->assertDatabaseCount('brands', 3);
    $this->assertDatabaseCount('tile_sizes', 3);
    $this->assertDatabaseCount('quality_grades', 3);
    $this->assertDatabaseCount('users', 3);
    $this->assertDatabaseCount('products', 3);
    $this->assertDatabaseCount('batches', 3);
    $this->assertDatabaseCount('shades', 3);
    $this->assertDatabaseCount('product_prices', 3);
    $this->assertDatabaseCount('warehouse_stocks', 3);
    $this->assertDatabaseCount('stock_movements', 3);
    $this->assertDatabaseCount('customers', 3);
    $this->assertDatabaseCount('suppliers', 3);
    $this->assertDatabaseCount('sales', 3);
    $this->assertDatabaseCount('sale_items', 3);
    $this->assertDatabaseCount('purchases', 3);
    $this->assertDatabaseCount('purchase_items', 3);
    $this->assertDatabaseCount('goods_receipts', 3);
    $this->assertDatabaseCount('goods_receipt_items', 3);
    $this->assertDatabaseCount('warehouse_transfers', 3);
    $this->assertDatabaseCount('warehouse_transfer_items', 3);
    $this->assertDatabaseCount('delivery_challans', 3);
    $this->assertDatabaseCount('delivery_challan_items', 3);
    $this->assertDatabaseCount('sales_returns', 3);
    $this->assertDatabaseCount('sales_return_items', 3);
    $this->assertDatabaseCount('purchase_returns', 3);
    $this->assertDatabaseCount('purchase_return_items', 3);
    $this->assertDatabaseCount('payments', 3);
    $this->assertDatabaseCount('payment_allocations', 3);
    $this->assertDatabaseCount('customer_ledger_entries', 3);
    $this->assertDatabaseCount('supplier_ledger_entries', 3);
    $this->assertDatabaseCount('expenses', 3);
    $this->assertDatabaseCount('settings', 3);
    $this->assertDatabaseCount('sms_logs', 3);
    $this->assertDatabaseCount('activity_logs', 3);

    $owner = User::query()->where('email', 'test@example.com')->first();

    expect($owner)->not->toBeNull()
        ->and($owner->role->slug)->toBe('owner')
        ->and(Role::query()->count())->toBe(6)
        ->and(Product::query()->count())->toBe(3)
        ->and(Sale::query()->count())->toBe(3)
        ->and(WarehouseStock::query()->count())->toBe(3);
});
