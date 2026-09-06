<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Batch;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\DeliveryChallan;
use App\Models\DeliveryChallanItem;
use App\Models\Expense;
use App\Models\Factory as CeramicFactory;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\QualityGrade;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Setting;
use App\Models\Shade;
use App\Models\SmsLog;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierLedgerEntry;
use App\Models\TileSize;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DemoDataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed three dummy rows for each ERP table, in foreign-key order.
     */
    public function run(): void
    {
        $units = $this->seedUnits();
        $warehouses = $this->seedWarehouses();
        $factories = $this->seedFactories();
        $brands = $this->seedBrands($factories);
        $tileSizes = $this->seedTileSizes();
        $grades = $this->seedQualityGrades();
        $users = $this->seedUsers($warehouses);
        $products = $this->seedProducts($factories, $brands, $tileSizes, $grades);
        $batches = $this->seedBatches($products);
        $shades = $this->seedShades($products);
        $this->seedProductPrices($products, $units);
        $stocks = $this->seedWarehouseStocks($products, $batches, $shades, $grades, $warehouses);
        $this->seedStockMovements($stocks, $users, $units);
        $customers = $this->seedCustomers();
        $suppliers = $this->seedSuppliers();
        $sales = $this->seedSales($customers, $warehouses, $users);
        $saleItems = $this->seedSaleItems($sales, $products, $batches, $shades, $grades, $units);
        $purchases = $this->seedPurchases($suppliers, $warehouses, $users);
        $purchaseItems = $this->seedPurchaseItems($purchases, $products, $batches, $shades, $grades, $units);
        $receipts = $this->seedGoodsReceipts($purchases, $suppliers, $warehouses, $users);
        $this->seedGoodsReceiptItems($receipts, $purchaseItems, $products, $batches, $shades, $grades, $units);
        $transfers = $this->seedTransfers($warehouses, $users);
        $this->seedTransferItems($transfers, $products, $batches, $shades, $grades, $units);
        $challans = $this->seedChallans($customers, $warehouses, $sales, $users);
        $this->seedChallanItems($challans, $products, $batches, $shades, $grades, $units);
        $salesReturns = $this->seedSalesReturns($sales, $customers, $warehouses, $users);
        $this->seedSalesReturnItems($salesReturns, $saleItems, $products, $batches, $shades, $grades, $units);
        $purchaseReturns = $this->seedPurchaseReturns($purchases, $suppliers, $warehouses, $users);
        $this->seedPurchaseReturnItems($purchaseReturns, $purchaseItems, $products, $batches, $shades, $grades, $units);
        $payments = $this->seedPayments($customers, $sales, $users);
        $this->seedPaymentAllocations($payments, $sales);
        $this->seedCustomerLedgers($customers, $sales, $users);
        $this->seedSupplierLedgers($suppliers, $purchases, $users);
        $this->seedExpenses($users);
        $this->seedSettings();
        $this->seedSmsLogs();
        $this->seedActivityLogs($users, $products);
    }

    /**
     * @return Collection<int, Unit>
     */
    private function seedUnits(): Collection
    {
        return collect([
            ['code' => 'BOX', 'name' => 'Box', 'name_bn' => 'বাক্স', 'is_system' => true],
            ['code' => 'PCS', 'name' => 'Piece', 'name_bn' => 'পিস', 'is_system' => true],
            ['code' => 'SQFT', 'name' => 'Square foot', 'name_bn' => 'বর্গফুট', 'is_system' => true],
        ])->map(fn (array $unit): Unit => Unit::query()->create($unit));
    }

    /**
     * @return Collection<int, Warehouse>
     */
    private function seedWarehouses(): Collection
    {
        return collect([
            ['code' => 'SR-1', 'name' => 'Showroom', 'name_bn' => 'শোরুম', 'address' => 'Mirpur 12, Dhaka', 'type' => 'showroom'],
            ['code' => 'GD-1', 'name' => 'Godown', 'name_bn' => 'গুদাম', 'address' => 'Savar, Dhaka', 'type' => 'godown'],
            ['code' => 'TR-1', 'name' => 'Transit', 'name_bn' => 'ট্রানজিট', 'address' => null, 'type' => 'virtual'],
        ])->map(fn (array $warehouse): Warehouse => Warehouse::query()->create([
            ...$warehouse,
            'is_active' => true,
        ]));
    }

    /**
     * @return Collection<int, CeramicFactory>
     */
    private function seedFactories(): Collection
    {
        return collect([
            ['name' => 'RAK Ceramics', 'name_bn' => 'রাক সিরামিক্স', 'code' => 'FAC-RAK'],
            ['name' => 'Fresh Tiles BD', 'name_bn' => 'ফ্রেশ টাইলস', 'code' => 'FAC-FTB'],
            ['name' => 'Mirpur Factory', 'name_bn' => 'মিরপুর ফ্যাক্টরি', 'code' => 'FAC-MIR'],
        ])->map(fn (array $factory): CeramicFactory => CeramicFactory::query()->create([
            ...$factory,
            'is_active' => true,
        ]));
    }

    /**
     * @param  Collection<int, CeramicFactory>  $factories
     * @return Collection<int, Brand>
     */
    private function seedBrands(Collection $factories): Collection
    {
        return collect(['RAK', 'Carrara', 'Nila'])->map(
            fn (string $name, int $index): Brand => Brand::query()->create([
                'name' => $name,
                'name_bn' => null,
                'factory_id' => $factories[$index]->id,
                'is_active' => true,
            ]),
        );
    }

    /**
     * @return Collection<int, TileSize>
     */
    private function seedTileSizes(): Collection
    {
        return collect([
            ['label' => '600x600', 'length_mm' => 600, 'width_mm' => 600, 'default_sqft_per_piece' => '3.875000'],
            ['label' => '300x300', 'length_mm' => 300, 'width_mm' => 300, 'default_sqft_per_piece' => '0.968750'],
            ['label' => '200x1200', 'length_mm' => 200, 'width_mm' => 1200, 'default_sqft_per_piece' => '2.583333'],
        ])->map(fn (array $size): TileSize => TileSize::query()->create($size));
    }

    /**
     * @return Collection<int, QualityGrade>
     */
    private function seedQualityGrades(): Collection
    {
        return collect([
            ['code' => 'A', 'name' => 'Grade A', 'name_bn' => 'গ্রেড এ', 'is_sellable' => true, 'sort_order' => 1],
            ['code' => 'B', 'name' => 'Grade B', 'name_bn' => 'গ্রেড বি', 'is_sellable' => true, 'sort_order' => 2],
            ['code' => 'C', 'name' => 'Commercial', 'name_bn' => 'কমার্শিয়াল', 'is_sellable' => false, 'sort_order' => 3],
        ])->map(fn (array $grade): QualityGrade => QualityGrade::query()->create($grade));
    }

    /**
     * @param  Collection<int, Warehouse>  $warehouses
     * @return Collection<int, User>
     */
    private function seedUsers(Collection $warehouses): Collection
    {
        $roles = Role::query()->whereIn('slug', ['admin', 'sales_shop'])->get()->keyBy('slug');

        return collect([
            [
                'name' => 'Habib Admin',
                'username' => 'admin',
                'email' => 'test@example.com',
                'phone' => '01710000001',
                'role_id' => $roles['admin']->id,
            ],
            [
                'name' => 'Karim Sales',
                'username' => 'sales',
                'email' => 'sales@example.com',
                'phone' => '01710000002',
                'role_id' => $roles['sales_shop']->id,
            ],
            [
                'name' => 'Nila Sales',
                'username' => 'counter',
                'email' => 'counter@example.com',
                'phone' => '01710000003',
                'role_id' => $roles['sales_shop']->id,
            ],
        ])->map(fn (array $user): User => User::query()->create([
            ...$user,
            'password' => 'password',
            'email_verified_at' => now(),
            'locale' => 'en',
            'default_warehouse_id' => $warehouses[0]->id,
            'is_active' => true,
        ]));
    }

    /**
     * @param  Collection<int, CeramicFactory>  $factories
     * @param  Collection<int, Brand>  $brands
     * @param  Collection<int, TileSize>  $tileSizes
     * @param  Collection<int, QualityGrade>  $grades
     * @return Collection<int, Product>
     */
    private function seedProducts(Collection $factories, Collection $brands, Collection $tileSizes, Collection $grades): Collection
    {
        return collect([
            ['sku' => 'RAK-60-WHT', 'name' => 'RAK 600 White', 'name_bn' => 'রাক ৬০০ হোয়াইট', 'pieces_per_box' => 4, 'sqft_per_piece' => '2.690000'],
            ['sku' => 'CAR-60-GLS', 'name' => 'Carrara 600', 'name_bn' => 'কারারা ৬০০', 'pieces_per_box' => 4, 'sqft_per_piece' => '2.690000'],
            ['sku' => 'NIL-30-MAT', 'name' => 'Nila 300 Matte', 'name_bn' => 'নিলা ৩০০ ম্যাট', 'pieces_per_box' => 9, 'sqft_per_piece' => '0.968750'],
        ])->map(fn (array $product, int $index): Product => Product::query()->create([
            ...$product,
            'brand_id' => $brands[$index]->id,
            'factory_id' => $factories[$index]->id,
            'tile_size_id' => $tileSizes[$index]->id,
            'barcode' => '89000000000'.($index + 1),
            'requires_batch' => true,
            'requires_shade' => true,
            'default_quality_id' => $grades[0]->id,
            'is_active' => true,
        ]));
    }

    /**
     * @param  Collection<int, Product>  $products
     * @return Collection<int, Batch>
     */
    private function seedBatches(Collection $products): Collection
    {
        return $products->map(fn (Product $product, int $index): Batch => Batch::query()->create([
            'product_id' => $product->id,
            'code' => 'B-2026-0'.($index + 1),
            'manufactured_on' => now()->subMonths($index + 1)->toDateString(),
            'notes' => 'Opening lot',
        ]));
    }

    /**
     * @param  Collection<int, Product>  $products
     * @return Collection<int, Shade>
     */
    private function seedShades(Collection $products): Collection
    {
        return collect(['A1', 'B2', 'C3'])->map(
            fn (string $code, int $index): Shade => Shade::query()->create([
                'product_id' => $products[$index]->id,
                'code' => $code,
                'name' => 'Shade '.$code,
            ]),
        );
    }

    /**
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, Unit>  $units
     */
    private function seedProductPrices(Collection $products, Collection $units): void
    {
        collect(['1200.00', '1450.00', '780.00'])->each(
            fn (string $price, int $index) => ProductPrice::query()->create([
                'product_id' => $products[$index]->id,
                'unit_code' => $units[0]->code,
                'price' => $price,
            ]),
        );
    }

    /**
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, Batch>  $batches
     * @param  Collection<int, Shade>  $shades
     * @param  Collection<int, QualityGrade>  $grades
     * @param  Collection<int, Warehouse>  $warehouses
     * @return Collection<int, WarehouseStock>
     */
    private function seedWarehouseStocks(
        Collection $products,
        Collection $batches,
        Collection $shades,
        Collection $grades,
        Collection $warehouses,
    ): Collection {
        return collect(['1076.0000', '645.6000', '430.4000'])->map(
            fn (string $qty, int $index): WarehouseStock => WarehouseStock::query()->create([
                'product_id' => $products[$index]->id,
                'batch_id' => $batches[$index]->id,
                'shade_id' => $shades[$index]->id,
                'quality_grade_id' => $grades[0]->id,
                'warehouse_id' => $warehouses[0]->id,
                'condition' => 'sellable',
                'qty_sqft' => $qty,
            ]),
        );
    }

    /**
     * @param  Collection<int, WarehouseStock>  $stocks
     * @param  Collection<int, User>  $users
     * @param  Collection<int, Unit>  $units
     */
    private function seedStockMovements(Collection $stocks, Collection $users, Collection $units): void
    {
        $stocks->each(function (WarehouseStock $stock, int $index) use ($users, $units): void {
            StockMovement::query()->create([
                'warehouse_stock_id' => $stock->id,
                'product_id' => $stock->product_id,
                'warehouse_id' => $stock->warehouse_id,
                'batch_id' => $stock->batch_id,
                'shade_id' => $stock->shade_id,
                'quality_grade_id' => $stock->quality_grade_id,
                'condition' => $stock->condition,
                'direction' => 'in',
                'qty_sqft' => $stock->qty_sqft,
                'qty_input' => (string) ($index + 1),
                'unit_code' => $units[0]->code,
                'pieces_per_box_snapshot' => 4,
                'sqft_per_piece_snapshot' => '2.690000',
                'movement_type' => 'opening',
                'note' => 'Opening stock',
                'occurred_at' => now()->subDays(7),
                'created_by' => $users[0]->id,
            ]);
        });
    }

    /**
     * @return Collection<int, Customer>
     */
    private function seedCustomers(): Collection
    {
        return collect([
            ['code' => 'C-1001', 'name' => 'Rahman Ceramics', 'phone' => '01720000001', 'cached_balance' => '18200.00'],
            ['code' => 'C-1002', 'name' => 'Nila Tiles', 'phone' => '01720000002', 'cached_balance' => '86400.00'],
            ['code' => 'C-1003', 'name' => 'Karim Traders', 'phone' => '01720000003', 'cached_balance' => '24750.00'],
        ])->map(fn (array $customer): Customer => Customer::query()->create([
            ...$customer,
            'name_bn' => null,
            'address' => 'Dhaka',
            'credit_limit' => '100000.00',
            'is_walk_in' => false,
            'is_active' => true,
        ]));
    }

    /**
     * @return Collection<int, Supplier>
     */
    private function seedSuppliers(): Collection
    {
        return collect([
            ['code' => 'S-1001', 'name' => 'RAK Ceramics BD', 'phone' => '01820000001'],
            ['code' => 'S-1002', 'name' => 'Fresh Tiles BD', 'phone' => '01820000002'],
            ['code' => 'S-1003', 'name' => 'Mirpur Factory Supply', 'phone' => '01820000003'],
        ])->map(fn (array $supplier): Supplier => Supplier::query()->create([
            ...$supplier,
            'name_bn' => null,
            'address' => 'Dhaka',
            'credit_limit' => '250000.00',
            'is_active' => true,
            'cached_balance' => '0.00',
        ]));
    }

    /**
     * @param  Collection<int, Customer>  $customers
     * @param  Collection<int, Warehouse>  $warehouses
     * @param  Collection<int, User>  $users
     * @return Collection<int, Sale>
     */
    private function seedSales(Collection $customers, Collection $warehouses, Collection $users): Collection
    {
        return collect([
            ['number' => 'INV-104', 'grand_total' => '18400.00', 'paid_total' => '12400.00', 'due_total' => '6000.00'],
            ['number' => 'INV-103', 'grand_total' => '4280.00', 'paid_total' => '4280.00', 'due_total' => '0.00'],
            ['number' => 'INV-102', 'grand_total' => '32150.00', 'paid_total' => '20000.00', 'due_total' => '12150.00'],
        ])->map(fn (array $sale, int $index): Sale => Sale::query()->create([
            'number' => $sale['number'],
            'customer_id' => $customers[$index]->id,
            'warehouse_id' => $warehouses[0]->id,
            'status' => 'posted',
            'source' => 'pos',
            'sale_at' => now()->subHours($index + 1),
            'subtotal' => $sale['grand_total'],
            'discount_total' => '0.00',
            'tax_total' => '0.00',
            'grand_total' => $sale['grand_total'],
            'paid_total' => $sale['paid_total'],
            'due_total' => $sale['due_total'],
            'created_by' => $users[0]->id,
        ]));
    }

    /**
     * @param  Collection<int, Sale>  $sales
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, Batch>  $batches
     * @param  Collection<int, Shade>  $shades
     * @param  Collection<int, QualityGrade>  $grades
     * @param  Collection<int, Unit>  $units
     * @return Collection<int, SaleItem>
     */
    private function seedSaleItems(
        Collection $sales,
        Collection $products,
        Collection $batches,
        Collection $shades,
        Collection $grades,
        Collection $units,
    ): Collection {
        return $sales->map(fn (Sale $sale, int $index): SaleItem => SaleItem::query()->create([
            'sale_id' => $sale->id,
            'product_id' => $products[$index]->id,
            'batch_id' => $batches[$index]->id,
            'shade_id' => $shades[$index]->id,
            'quality_grade_id' => $grades[0]->id,
            'condition' => 'sellable',
            'unit_code' => $units[0]->code,
            'qty_input' => '1.0000',
            'qty_sqft' => '10.7600',
            'unit_price' => $sale->grand_total,
            'discount_amount' => '0.00',
            'line_total' => $sale->grand_total,
            'unit_cost' => '800.00',
            'pieces_per_box_snapshot' => 4,
            'sqft_per_piece_snapshot' => '2.690000',
        ]));
    }

    /**
     * @param  Collection<int, Supplier>  $suppliers
     * @param  Collection<int, Warehouse>  $warehouses
     * @param  Collection<int, User>  $users
     * @return Collection<int, Purchase>
     */
    private function seedPurchases(Collection $suppliers, Collection $warehouses, Collection $users): Collection
    {
        return collect([
            ['number' => 'PO-041', 'status' => 'partial', 'total' => '86000.00'],
            ['number' => 'PO-040', 'status' => 'received', 'total' => '24500.00'],
            ['number' => 'PO-039', 'status' => 'ordered', 'total' => '41000.00'],
        ])->map(fn (array $purchase, int $index): Purchase => Purchase::query()->create([
            'number' => $purchase['number'],
            'supplier_id' => $suppliers[$index]->id,
            'warehouse_id' => $warehouses[1]->id,
            'status' => $purchase['status'],
            'order_date' => now()->subDays($index + 1)->toDateString(),
            'subtotal' => $purchase['total'],
            'discount' => '0.00',
            'total' => $purchase['total'],
            'created_by' => $users[0]->id,
        ]));
    }

    /**
     * @param  Collection<int, Purchase>  $purchases
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, Batch>  $batches
     * @param  Collection<int, Shade>  $shades
     * @param  Collection<int, QualityGrade>  $grades
     * @param  Collection<int, Unit>  $units
     * @return Collection<int, PurchaseItem>
     */
    private function seedPurchaseItems(
        Collection $purchases,
        Collection $products,
        Collection $batches,
        Collection $shades,
        Collection $grades,
        Collection $units,
    ): Collection {
        return $purchases->map(fn (Purchase $purchase, int $index): PurchaseItem => PurchaseItem::query()->create([
            'purchase_id' => $purchase->id,
            'product_id' => $products[$index]->id,
            'batch_id' => $batches[$index]->id,
            'shade_id' => $shades[$index]->id,
            'quality_grade_id' => $grades[0]->id,
            'unit_code' => $units[0]->code,
            'qty_ordered' => '20.0000',
            'qty_received' => $purchase->status === 'received' ? '20.0000' : '8.0000',
            'qty_sqft_ordered' => '215.2000',
            'unit_price' => '1000.00',
            'line_total' => $purchase->total,
            'pieces_per_box_snapshot' => 4,
            'sqft_per_piece_snapshot' => '2.690000',
        ]));
    }

    /**
     * @param  Collection<int, Purchase>  $purchases
     * @param  Collection<int, Supplier>  $suppliers
     * @param  Collection<int, Warehouse>  $warehouses
     * @param  Collection<int, User>  $users
     * @return Collection<int, GoodsReceipt>
     */
    private function seedGoodsReceipts(Collection $purchases, Collection $suppliers, Collection $warehouses, Collection $users): Collection
    {
        return collect(['GR-019', 'GR-018', 'GR-017'])->map(
            fn (string $number, int $index): GoodsReceipt => GoodsReceipt::query()->create([
                'number' => $number,
                'purchase_id' => $purchases[$index]->id,
                'supplier_id' => $suppliers[$index]->id,
                'warehouse_id' => $warehouses[1]->id,
                'status' => $index === 2 ? 'draft' : 'posted',
                'received_at' => $index === 2 ? null : now()->subDays($index),
                'created_by' => $users[0]->id,
            ]),
        );
    }

    /**
     * @param  Collection<int, GoodsReceipt>  $receipts
     * @param  Collection<int, PurchaseItem>  $purchaseItems
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, Batch>  $batches
     * @param  Collection<int, Shade>  $shades
     * @param  Collection<int, QualityGrade>  $grades
     * @param  Collection<int, Unit>  $units
     */
    private function seedGoodsReceiptItems(
        Collection $receipts,
        Collection $purchaseItems,
        Collection $products,
        Collection $batches,
        Collection $shades,
        Collection $grades,
        Collection $units,
    ): void {
        $receipts->each(function (GoodsReceipt $receipt, int $index) use ($purchaseItems, $products, $batches, $shades, $grades, $units): void {
            GoodsReceiptItem::query()->create([
                'goods_receipt_id' => $receipt->id,
                'purchase_item_id' => $purchaseItems[$index]->id,
                'product_id' => $products[$index]->id,
                'batch_id' => $batches[$index]->id,
                'shade_id' => $shades[$index]->id,
                'quality_grade_id' => $grades[0]->id,
                'condition' => 'sellable',
                'unit_code' => $units[0]->code,
                'qty_input' => '8.0000',
                'qty_sqft' => '86.0800',
                'unit_price' => '1000.00',
                'line_total' => '8000.00',
                'pieces_per_box_snapshot' => 4,
                'sqft_per_piece_snapshot' => '2.690000',
            ]);
        });
    }

    /**
     * @param  Collection<int, Warehouse>  $warehouses
     * @param  Collection<int, User>  $users
     * @return Collection<int, WarehouseTransfer>
     */
    private function seedTransfers(Collection $warehouses, Collection $users): Collection
    {
        return collect(['TR-011', 'TR-010', 'TR-009'])->map(
            fn (string $number, int $index): WarehouseTransfer => WarehouseTransfer::query()->create([
                'number' => $number,
                'from_warehouse_id' => $warehouses[1]->id,
                'to_warehouse_id' => $warehouses[0]->id,
                'status' => $index === 0 ? 'dispatched' : 'draft',
                'dispatched_at' => $index === 0 ? now()->subDay() : null,
                'created_by' => $users[0]->id,
            ]),
        );
    }

    /**
     * @param  Collection<int, WarehouseTransfer>  $transfers
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, Batch>  $batches
     * @param  Collection<int, Shade>  $shades
     * @param  Collection<int, QualityGrade>  $grades
     * @param  Collection<int, Unit>  $units
     */
    private function seedTransferItems(
        Collection $transfers,
        Collection $products,
        Collection $batches,
        Collection $shades,
        Collection $grades,
        Collection $units,
    ): void {
        $transfers->each(function (WarehouseTransfer $transfer, int $index) use ($products, $batches, $shades, $grades, $units): void {
            WarehouseTransferItem::query()->create([
                'transfer_id' => $transfer->id,
                'product_id' => $products[$index]->id,
                'batch_id' => $batches[$index]->id,
                'shade_id' => $shades[$index]->id,
                'quality_grade_id' => $grades[0]->id,
                'unit_code' => $units[0]->code,
                'qty_input' => '2.0000',
                'qty_sqft' => '21.5200',
                'qty_received_sqft' => '0.0000',
                'pieces_per_box_snapshot' => 4,
                'sqft_per_piece_snapshot' => '2.690000',
            ]);
        });
    }

    /**
     * @param  Collection<int, Customer>  $customers
     * @param  Collection<int, Warehouse>  $warehouses
     * @param  Collection<int, Sale>  $sales
     * @param  Collection<int, User>  $users
     * @return Collection<int, DeliveryChallan>
     */
    private function seedChallans(Collection $customers, Collection $warehouses, Collection $sales, Collection $users): Collection
    {
        return collect(['CH-021', 'CH-020', 'CH-019'])->map(
            fn (string $number, int $index): DeliveryChallan => DeliveryChallan::query()->create([
                'number' => $number,
                'customer_id' => $customers[$index]->id,
                'warehouse_id' => $warehouses[0]->id,
                'sale_id' => $sales[$index]->id,
                'destination_address' => 'Dhaka',
                'driver_name' => 'Driver '.($index + 1),
                'vehicle_no' => 'DHK-100'.($index + 1),
                'status' => $index === 0 ? 'dispatched' : 'draft',
                'created_by' => $users[0]->id,
            ]),
        );
    }

    /**
     * @param  Collection<int, DeliveryChallan>  $challans
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, Batch>  $batches
     * @param  Collection<int, Shade>  $shades
     * @param  Collection<int, QualityGrade>  $grades
     * @param  Collection<int, Unit>  $units
     */
    private function seedChallanItems(
        Collection $challans,
        Collection $products,
        Collection $batches,
        Collection $shades,
        Collection $grades,
        Collection $units,
    ): void {
        $challans->each(function (DeliveryChallan $challan, int $index) use ($products, $batches, $shades, $grades, $units): void {
            DeliveryChallanItem::query()->create([
                'challan_id' => $challan->id,
                'product_id' => $products[$index]->id,
                'batch_id' => $batches[$index]->id,
                'shade_id' => $shades[$index]->id,
                'quality_grade_id' => $grades[0]->id,
                'unit_code' => $units[0]->code,
                'qty_input' => '1.0000',
                'qty_sqft' => '10.7600',
                'pieces_per_box_snapshot' => 4,
                'sqft_per_piece_snapshot' => '2.690000',
            ]);
        });
    }

    /**
     * @param  Collection<int, Sale>  $sales
     * @param  Collection<int, Customer>  $customers
     * @param  Collection<int, Warehouse>  $warehouses
     * @param  Collection<int, User>  $users
     * @return Collection<int, SalesReturn>
     */
    private function seedSalesReturns(Collection $sales, Collection $customers, Collection $warehouses, Collection $users): Collection
    {
        return collect(['SR-008', 'SR-007', 'SR-006'])->map(
            fn (string $number, int $index): SalesReturn => SalesReturn::query()->create([
                'number' => $number,
                'sale_id' => $sales[$index]->id,
                'customer_id' => $customers[$index]->id,
                'warehouse_id' => $warehouses[0]->id,
                'status' => 'draft',
                'restock_mode' => 'sellable',
                'money_action' => 'credit',
                'subtotal' => '1000.00',
                'total' => '1000.00',
                'created_by' => $users[0]->id,
            ]),
        );
    }

    /**
     * @param  Collection<int, SalesReturn>  $returns
     * @param  Collection<int, SaleItem>  $saleItems
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, Batch>  $batches
     * @param  Collection<int, Shade>  $shades
     * @param  Collection<int, QualityGrade>  $grades
     * @param  Collection<int, Unit>  $units
     */
    private function seedSalesReturnItems(
        Collection $returns,
        Collection $saleItems,
        Collection $products,
        Collection $batches,
        Collection $shades,
        Collection $grades,
        Collection $units,
    ): void {
        $returns->each(function (SalesReturn $return, int $index) use ($saleItems, $products, $batches, $shades, $grades, $units): void {
            SalesReturnItem::query()->create([
                'sales_return_id' => $return->id,
                'sale_item_id' => $saleItems[$index]->id,
                'product_id' => $products[$index]->id,
                'batch_id' => $batches[$index]->id,
                'shade_id' => $shades[$index]->id,
                'quality_grade_id' => $grades[0]->id,
                'condition' => 'sellable',
                'unit_code' => $units[0]->code,
                'qty_input' => '1.0000',
                'qty_sqft' => '10.7600',
                'unit_price' => '1000.00',
                'line_total' => '1000.00',
                'pieces_per_box_snapshot' => 4,
                'sqft_per_piece_snapshot' => '2.690000',
            ]);
        });
    }

    /**
     * @param  Collection<int, Purchase>  $purchases
     * @param  Collection<int, Supplier>  $suppliers
     * @param  Collection<int, Warehouse>  $warehouses
     * @param  Collection<int, User>  $users
     * @return Collection<int, PurchaseReturn>
     */
    private function seedPurchaseReturns(Collection $purchases, Collection $suppliers, Collection $warehouses, Collection $users): Collection
    {
        return collect(['PR-005', 'PR-004', 'PR-003'])->map(
            fn (string $number, int $index): PurchaseReturn => PurchaseReturn::query()->create([
                'number' => $number,
                'purchase_id' => $purchases[$index]->id,
                'supplier_id' => $suppliers[$index]->id,
                'warehouse_id' => $warehouses[1]->id,
                'status' => 'draft',
                'money_action' => 'credit',
                'subtotal' => '1000.00',
                'total' => '1000.00',
                'created_by' => $users[0]->id,
            ]),
        );
    }

    /**
     * @param  Collection<int, PurchaseReturn>  $returns
     * @param  Collection<int, PurchaseItem>  $purchaseItems
     * @param  Collection<int, Product>  $products
     * @param  Collection<int, Batch>  $batches
     * @param  Collection<int, Shade>  $shades
     * @param  Collection<int, QualityGrade>  $grades
     * @param  Collection<int, Unit>  $units
     */
    private function seedPurchaseReturnItems(
        Collection $returns,
        Collection $purchaseItems,
        Collection $products,
        Collection $batches,
        Collection $shades,
        Collection $grades,
        Collection $units,
    ): void {
        $returns->each(function (PurchaseReturn $return, int $index) use ($purchaseItems, $products, $batches, $shades, $grades, $units): void {
            PurchaseReturnItem::query()->create([
                'purchase_return_id' => $return->id,
                'purchase_item_id' => $purchaseItems[$index]->id,
                'product_id' => $products[$index]->id,
                'batch_id' => $batches[$index]->id,
                'shade_id' => $shades[$index]->id,
                'quality_grade_id' => $grades[0]->id,
                'condition' => 'sellable',
                'unit_code' => $units[0]->code,
                'qty_input' => '1.0000',
                'qty_sqft' => '10.7600',
                'unit_price' => '1000.00',
                'line_total' => '1000.00',
                'pieces_per_box_snapshot' => 4,
                'sqft_per_piece_snapshot' => '2.690000',
            ]);
        });
    }

    /**
     * @param  Collection<int, Customer>  $customers
     * @param  Collection<int, Sale>  $sales
     * @param  Collection<int, User>  $users
     * @return Collection<int, Payment>
     */
    private function seedPayments(Collection $customers, Collection $sales, Collection $users): Collection
    {
        return collect(['PAY-031', 'PAY-030', 'PAY-029'])->map(
            fn (string $number, int $index): Payment => Payment::query()->create([
                'number' => $number,
                'party_type' => 'customer',
                'customer_id' => $customers[$index]->id,
                'method' => 'cash',
                'amount' => $sales[$index]->paid_total,
                'direction' => 'in',
                'paid_at' => now()->subHours($index + 1),
                'sale_id' => $sales[$index]->id,
                'created_by' => $users[0]->id,
            ]),
        );
    }

    /**
     * @param  Collection<int, Payment>  $payments
     * @param  Collection<int, Sale>  $sales
     */
    private function seedPaymentAllocations(Collection $payments, Collection $sales): void
    {
        $payments->each(function (Payment $payment, int $index) use ($sales): void {
            PaymentAllocation::query()->create([
                'payment_id' => $payment->id,
                'document_type' => $sales[$index]->getMorphClass(),
                'document_id' => $sales[$index]->id,
                'amount' => $payment->amount,
            ]);
        });
    }

    /**
     * @param  Collection<int, Customer>  $customers
     * @param  Collection<int, Sale>  $sales
     * @param  Collection<int, User>  $users
     */
    private function seedCustomerLedgers(Collection $customers, Collection $sales, Collection $users): void
    {
        $customers->each(function (Customer $customer, int $index) use ($sales, $users): void {
            CustomerLedgerEntry::query()->create([
                'customer_id' => $customer->id,
                'entry_at' => $sales[$index]->sale_at,
                'debit' => $sales[$index]->grand_total,
                'credit' => '0.00',
                'description' => 'Sale '.$sales[$index]->number,
                'document_type' => $sales[$index]->getMorphClass(),
                'document_id' => $sales[$index]->id,
                'created_by' => $users[0]->id,
            ]);
        });
    }

    /**
     * @param  Collection<int, Supplier>  $suppliers
     * @param  Collection<int, Purchase>  $purchases
     * @param  Collection<int, User>  $users
     */
    private function seedSupplierLedgers(Collection $suppliers, Collection $purchases, Collection $users): void
    {
        $suppliers->each(function (Supplier $supplier, int $index) use ($purchases, $users): void {
            SupplierLedgerEntry::query()->create([
                'supplier_id' => $supplier->id,
                'entry_at' => now()->subDays($index + 1),
                'debit' => '0.00',
                'credit' => $purchases[$index]->total,
                'description' => 'Purchase '.$purchases[$index]->number,
                'document_type' => $purchases[$index]->getMorphClass(),
                'document_id' => $purchases[$index]->id,
                'created_by' => $users[0]->id,
            ]);
        });
    }

    /**
     * @param  Collection<int, User>  $users
     */
    private function seedExpenses(Collection $users): void
    {
        collect([
            ['category' => 'transport', 'amount' => '2500.00'],
            ['category' => 'utilities', 'amount' => '4800.00'],
            ['category' => 'misc', 'amount' => '750.00'],
        ])->each(fn (array $expense, int $index) => Expense::query()->create([
            ...$expense,
            'spent_at' => now()->subDays($index + 1),
            'notes' => 'Dummy expense',
            'created_by' => $users[0]->id,
        ]));
    }

    private function seedSettings(): void
    {
        collect([
            ['key' => 'company.name', 'value' => ['en' => 'TileGrid', 'bn' => 'টাইলগ্রিড']],
            ['key' => 'allow_negative_stock', 'value' => ['enabled' => false]],
            ['key' => 'locale.default', 'value' => ['locale' => 'en']],
        ])->each(fn (array $setting) => Setting::query()->create($setting));
    }

    private function seedSmsLogs(): void
    {
        collect([
            ['to_phone' => '01720000001', 'body' => 'Due reminder for INV-104', 'status' => 'sent'],
            ['to_phone' => '01720000002', 'body' => 'Challan CH-021 dispatched', 'status' => 'sent'],
            ['to_phone' => '01720000003', 'body' => 'Payment received PAY-029', 'status' => 'failed'],
        ])->each(fn (array $log) => SmsLog::query()->create([
            ...$log,
            'provider' => 'log',
            'payload' => ['ok' => $log['status'] === 'sent'],
        ]));
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  Collection<int, Product>  $products
     */
    private function seedActivityLogs(Collection $users, Collection $products): void
    {
        collect(['created', 'updated', 'posted'])->each(
            fn (string $action, int $index) => ActivityLog::query()->create([
                'user_id' => $users[0]->id,
                'action' => $action,
                'subject_type' => $products[$index]->getMorphClass(),
                'subject_id' => $products[$index]->id,
                'properties' => ['sku' => $products[$index]->sku],
                'ip' => '127.0.0.1',
            ]),
        );
    }
}
