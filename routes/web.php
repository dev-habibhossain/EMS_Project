<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ChallanController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FactoryController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\QualityGradeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SalesReturnController;
use App\Http\Controllers\ShadeController;
use App\Http\Controllers\SmsLogController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TileSizeController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('pos', [PosController::class, 'index'])->name('pos');
    Route::post('pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');

    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::post('products', [ProductController::class, 'store'])->name('products.store');
    Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('brands', [BrandController::class, 'index'])->name('brands.index');
    Route::get('factories', [FactoryController::class, 'index'])->name('factories.index');
    Route::get('tile-sizes', [TileSizeController::class, 'index'])->name('tile-sizes.index');
    Route::get('shades', [ShadeController::class, 'index'])->name('shades.index');
    Route::get('quality-grades', [QualityGradeController::class, 'index'])->name('quality-grades.index');
    Route::get('batches', [BatchController::class, 'index'])->name('batches.index');

    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/damaged', [InventoryController::class, 'damaged'])->name('inventory.damaged');
    Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::get('transfers', [TransferController::class, 'index'])->name('transfers.index');

    Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('challans', [ChallanController::class, 'index'])->name('challans.index');
    Route::get('returns/sales', [SalesReturnController::class, 'index'])->name('sales-returns.index');
    Route::get('returns/purchases', [PurchaseReturnController::class, 'index'])->name('purchase-returns.index');

    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('settings/sms', [SmsLogController::class, 'index'])->name('sms-logs.index');
    Route::get('activity', [ActivityLogController::class, 'index'])->name('activity-logs.index');
});

require __DIR__.'/settings.php';
