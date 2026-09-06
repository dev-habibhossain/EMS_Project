<?php

use App\Models\ActivityLog;
use App\Models\Batch;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\Factory as CeramicFactory;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\QualityGrade;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shade;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\WarehouseTransfer;

test('a user belongs to an assigned role and default warehouse', function () {
    $role = Role::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $user = User::factory()->create([
        'role_id' => $role->id,
        'default_warehouse_id' => $warehouse->id,
    ]);

    expect($user->role->is($role))->toBeTrue()
        ->and($user->defaultWarehouse->is($warehouse))->toBeTrue();
});

test('a role exposes the permissions attached on the role_permission pivot', function () {
    $role = Role::factory()->create();
    $permission = Permission::factory()->create([
        'slug' => 'pos.use',
        'module' => 'sales',
    ]);

    $role->permissions()->attach($permission);

    expect($role->permissions()->where('slug', 'pos.use')->exists())->toBeTrue()
        ->and($permission->roles->contains($role))->toBeTrue();
});

test('a product belongs to catalog masters and stores generated sqft per box', function () {
    $factory = CeramicFactory::factory()->create();
    $brand = Brand::factory()->create(['factory_id' => $factory->id]);
    $grade = QualityGrade::factory()->create();

    $product = Product::factory()->create([
        'brand_id' => $brand->id,
        'factory_id' => $factory->id,
        'default_quality_id' => $grade->id,
        'pieces_per_box' => 4,
        'sqft_per_piece' => '2.690000',
    ]);

    $product->refresh();

    expect($product->brand->is($brand))->toBeTrue()
        ->and($product->tileFactory->is($factory))->toBeTrue()
        ->and($product->defaultQuality->is($grade))->toBeTrue()
        ->and((string) $product->sqft_per_box)->toBe('10.760000');
});

test('product prices belong to a product and resolve the unit by code', function () {
    $product = Product::factory()->create();
    $unit = Unit::factory()->create(['code' => 'BOX']);

    $price = ProductPrice::factory()->create([
        'product_id' => $product->id,
        'unit_code' => $unit->code,
        'price' => '1200.00',
    ]);

    expect($price->product->is($product))->toBeTrue()
        ->and($price->unit->is($unit))->toBeTrue()
        ->and($product->prices->contains($price))->toBeTrue();
});

test('warehouse stock belongs to the lot identity and records movements', function () {
    $product = Product::factory()->create();
    $batch = Batch::factory()->for($product)->create();
    $shade = Shade::factory()->for($product)->create();
    $stock = WarehouseStock::factory()->create([
        'product_id' => $product->id,
        'batch_id' => $batch->id,
        'shade_id' => $shade->id,
        'qty_sqft' => '107.6000',
    ]);
    $movement = StockMovement::factory()->create([
        'warehouse_stock_id' => $stock->id,
        'product_id' => $stock->product_id,
        'warehouse_id' => $stock->warehouse_id,
        'batch_id' => $stock->batch_id,
        'shade_id' => $stock->shade_id,
        'quality_grade_id' => $stock->quality_grade_id,
        'condition' => $stock->condition,
    ]);

    expect($stock->product->is($product))->toBeTrue()
        ->and($stock->batch->is($batch))->toBeTrue()
        ->and($stock->shade->is($shade))->toBeTrue()
        ->and($stock->warehouse)->toBeInstanceOf(Warehouse::class)
        ->and($stock->movements->contains($movement))->toBeTrue()
        ->and($movement->warehouseStock->is($stock))->toBeTrue()
        ->and($movement->updated_at)->toBeNull();
});

test('a sale belongs to a customer and contains lot-backed items', function () {
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $sale = Sale::factory()->for($customer)->for($user, 'createdBy')->create();
    $item = SaleItem::factory()->for($sale)->create();

    expect($sale->customer->is($customer))->toBeTrue()
        ->and($sale->createdBy->is($user))->toBeTrue()
        ->and($sale->items->contains($item))->toBeTrue()
        ->and($item->sale->is($sale))->toBeTrue()
        ->and($item->product)->toBeInstanceOf(Product::class)
        ->and($item->unit)->toBeInstanceOf(Unit::class);
});

test('a warehouse transfer points at distinct source and destination warehouses', function () {
    $from = Warehouse::factory()->create(['code' => 'GD-1']);
    $to = Warehouse::factory()->create(['code' => 'SR-1']);

    $transfer = WarehouseTransfer::factory()->create([
        'from_warehouse_id' => $from->id,
        'to_warehouse_id' => $to->id,
    ]);

    expect($transfer->fromWarehouse->is($from))->toBeTrue()
        ->and($transfer->toWarehouse->is($to))->toBeTrue()
        ->and($from->outgoingTransfers->contains($transfer))->toBeTrue()
        ->and($to->incomingTransfers->contains($transfer))->toBeTrue();
});

test('an activity log morphs to its subject', function () {
    $product = Product::factory()->create();

    $log = ActivityLog::factory()->create([
        'subject_type' => $product->getMorphClass(),
        'subject_id' => $product->id,
        'action' => 'created',
    ]);

    expect($log->subject->is($product))->toBeTrue();
});
