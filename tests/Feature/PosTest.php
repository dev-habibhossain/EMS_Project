<?php

use App\Models\Batch;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\QualityGrade;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shade;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Unit::firstOrCreate(['code' => 'BOX'], ['name' => 'Box', 'name_bn' => 'বাক্স', 'is_system' => true]);
    Unit::firstOrCreate(['code' => 'PCS'], ['name' => 'Piece', 'name_bn' => 'পিস', 'is_system' => true]);
    Unit::firstOrCreate(['code' => 'SQFT'], ['name' => 'Square foot', 'name_bn' => 'বর্গফুট', 'is_system' => true]);
});

test('guests cannot visit POS page', function () {
    $this->get(route('pos'))->assertRedirect(route('login'));
});

test('authenticated users can load POS with warehouses, customers, and products', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('pos'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Pos')
            ->has('warehouses')
            ->has('activeWarehouseId')
            ->has('customers')
            ->has('walkInCustomerId')
            ->has('products')
            ->has('qualityGrades')
            ->has('units'));
});

test('pos checkout succeeds for walk-in customer with full payment and updates inventory and ledgers', function () {
    $user = User::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $customer = Customer::factory()->create(['is_walk_in' => true, 'cached_balance' => 0]);
    $grade = QualityGrade::first() ?? QualityGrade::factory()->create(['is_sellable' => true]);
    $unit = Unit::firstOrCreate(['code' => 'BOX'], ['name' => 'Box', 'name_bn' => 'বাক্স', 'is_system' => true]);

    $product = Product::factory()->create([
        'pieces_per_box' => 4,
        'sqft_per_piece' => 2.690000,
    ]);

    $batch = Batch::factory()->create(['product_id' => $product->id]);
    $shade = Shade::factory()->create(['product_id' => $product->id]);
    ProductPrice::factory()->create(['product_id' => $product->id, 'unit_code' => 'BOX', 'price' => 1200]);

    $stock = WarehouseStock::create([
        'product_id' => $product->id,
        'batch_id' => $batch->id,
        'shade_id' => $shade->id,
        'quality_grade_id' => $grade->id,
        'warehouse_id' => $warehouse->id,
        'condition' => 'sellable',
        'qty_sqft' => 107.6000,
    ]);

    $payload = [
        'warehouse_id' => $warehouse->id,
        'customer_id' => $customer->id,
        'items' => [
            [
                'product_id' => $product->id,
                'batch_id' => $batch->id,
                'shade_id' => $shade->id,
                'quality_grade_id' => $grade->id,
                'condition' => 'sellable',
                'unit_code' => 'BOX',
                'qty_input' => 2,
                'unit_price' => 1200,
                'discount_amount' => 0,
            ],
        ],
        'discount_total' => 0,
        'payment_method' => 'cash',
        'paid_amount' => 2400,
    ];

    $response = $this->actingAs($user)->post(route('pos.checkout'), $payload);
    $response->assertSessionHasNoErrors();

    // 1. Sale record
    $sale = Sale::where('customer_id', $customer->id)->latest('id')->first();
    expect($sale)->not->toBeNull()
        ->and($sale->source)->toBe('pos')
        ->and($sale->status)->toBe('posted')
        ->and((float) $sale->subtotal)->toBe(2400.0)
        ->and((float) $sale->grand_total)->toBe(2400.0)
        ->and((float) $sale->paid_total)->toBe(2400.0)
        ->and((float) $sale->due_total)->toBe(0.0);

    // 2. Sale item record with snapshots
    $item = SaleItem::where('sale_id', $sale->id)->first();
    expect($item)->not->toBeNull()
        ->and($item->unit_code)->toBe('BOX')
        ->and((float) $item->qty_input)->toBe(2.0)
        ->and((float) $item->qty_sqft)->toBe(21.5200) // 2 * 10.76
        ->and($item->pieces_per_box_snapshot)->toBe(4)
        ->and((float) $item->sqft_per_piece_snapshot)->toBe(2.690000);

    // 3. Stock deduction
    $stock->refresh();
    expect((float) $stock->qty_sqft)->toBe(86.0800); // 107.6 - 21.52

    // 4. Stock movement audit
    $movement = StockMovement::where('document_id', $sale->id)->first();
    expect($movement)->not->toBeNull()
        ->and($movement->movement_type)->toBe('sale')
        ->and($movement->direction)->toBe('out')
        ->and((float) $movement->qty_sqft)->toBe(21.5200);

    // 5. Payment and allocation
    $payment = Payment::where('sale_id', $sale->id)->first();
    expect($payment)->not->toBeNull()
        ->and($payment->method)->toBe('cash')
        ->and((float) $payment->amount)->toBe(2400.0);

    $allocation = PaymentAllocation::where('payment_id', $payment->id)->first();
    expect($allocation)->not->toBeNull()
        ->and($allocation->document_id)->toBe($sale->id)
        ->and((float) $allocation->amount)->toBe(2400.0);

    // 6. Customer ledger
    $entries = CustomerLedgerEntry::where('customer_id', $customer->id)->get();
    expect($entries)->toHaveCount(2); // debit for sale, credit for payment
});

test('pos checkout rejects walk-in customer if due is greater than zero', function () {
    $user = User::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $customer = Customer::factory()->create(['is_walk_in' => true]);
    $grade = QualityGrade::first() ?? QualityGrade::factory()->create(['is_sellable' => true]);
    $product = Product::factory()->create(['pieces_per_box' => 4, 'sqft_per_piece' => 2.69]);

    $payload = [
        'warehouse_id' => $warehouse->id,
        'customer_id' => $customer->id,
        'items' => [
            [
                'product_id' => $product->id,
                'quality_grade_id' => $grade->id,
                'condition' => 'sellable',
                'unit_code' => 'BOX',
                'qty_input' => 1,
                'unit_price' => 1000,
            ],
        ],
        'payment_method' => 'cash',
        'paid_amount' => 500, // Due = 500
    ];

    $response = $this->actingAs($user)->post(route('pos.checkout'), $payload);
    $response->assertSessionHasErrors(['paid_amount']);

    expect(Sale::count())->toBe(0);
});

test('pos checkout permits named customer credit sale with due and updates ledger debit', function () {
    $user = User::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $customer = Customer::factory()->create([
        'is_walk_in' => false,
        'credit_limit' => 50000,
        'cached_balance' => 5000,
    ]);
    $grade = QualityGrade::first() ?? QualityGrade::factory()->create(['is_sellable' => true]);
    $product = Product::factory()->create(['pieces_per_box' => 4, 'sqft_per_piece' => 2.69]);

    $payload = [
        'warehouse_id' => $warehouse->id,
        'customer_id' => $customer->id,
        'items' => [
            [
                'product_id' => $product->id,
                'quality_grade_id' => $grade->id,
                'condition' => 'sellable',
                'unit_code' => 'BOX',
                'qty_input' => 2,
                'unit_price' => 1500,
            ],
        ],
        'payment_method' => 'cash',
        'paid_amount' => 1000, // Total = 3000, Paid = 1000, Due = 2000
    ];

    $response = $this->actingAs($user)->post(route('pos.checkout'), $payload);
    $response->assertSessionHasNoErrors();

    $sale = Sale::where('customer_id', $customer->id)->latest('id')->first();
    expect($sale)->not->toBeNull()
        ->and((float) $sale->grand_total)->toBe(3000.0)
        ->and((float) $sale->paid_total)->toBe(1000.0)
        ->and((float) $sale->due_total)->toBe(2000.0);

    $customer->refresh();
    // cached_balance was 5000 + 3000 - 1000 = 7000
    expect((float) $customer->cached_balance)->toBe(7000.0);
});

test('triple unit conversion correctly converts BOX, PCS, and SQFT to qty_sqft', function () {
    $user = User::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $customer = Customer::factory()->create(['is_walk_in' => false]);
    $grade = QualityGrade::first() ?? QualityGrade::factory()->create(['is_sellable' => true]);

    $product = Product::factory()->create([
        'pieces_per_box' => 4,
        'sqft_per_piece' => 2.690000,
    ]);

    $payload = [
        'warehouse_id' => $warehouse->id,
        'customer_id' => $customer->id,
        'items' => [
            [
                'product_id' => $product->id,
                'quality_grade_id' => $grade->id,
                'condition' => 'sellable',
                'unit_code' => 'BOX',
                'qty_input' => 3, // 3 * 10.76 = 32.28
                'unit_price' => 1000,
            ],
            [
                'product_id' => $product->id,
                'quality_grade_id' => $grade->id,
                'condition' => 'sellable',
                'unit_code' => 'PCS',
                'qty_input' => 2, // 2 * 2.69 = 5.38
                'unit_price' => 250,
            ],
            [
                'product_id' => $product->id,
                'quality_grade_id' => $grade->id,
                'condition' => 'sellable',
                'unit_code' => 'SQFT',
                'qty_input' => 15.5, // 15.5
                'unit_price' => 100,
            ],
        ],
        'payment_method' => 'cash',
        'paid_amount' => 5050,
    ];

    $response = $this->actingAs($user)->post(route('pos.checkout'), $payload);
    $response->assertSessionHasNoErrors();

    $sale = Sale::latest('id')->first();
    $items = SaleItem::where('sale_id', $sale->id)->get();

    expect((float) $items[0]->qty_sqft)->toBe(32.2800)
        ->and((float) $items[1]->qty_sqft)->toBe(5.3800)
        ->and((float) $items[2]->qty_sqft)->toBe(15.5000);
});
