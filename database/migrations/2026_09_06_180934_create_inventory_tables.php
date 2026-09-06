<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warehouse_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('batch_id')->constrained()->restrictOnDelete();
            $table->foreignId('shade_id')->constrained()->restrictOnDelete();
            $table->foreignId('quality_grade_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->enum('condition', ['sellable', 'damaged'])->default('sellable');
            $table->decimal('qty_sqft', 16, 4)->default(0);
            $table->timestamps();
            $table->unique(
                ['product_id', 'batch_id', 'shade_id', 'quality_grade_id', 'warehouse_id', 'condition'],
                'warehouse_stocks_lot_unique'
            );
            $table->index(['warehouse_id', 'product_id'], 'warehouse_stocks_warehouse_product_index');
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_stock_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->foreignId('batch_id')->constrained()->restrictOnDelete();
            $table->foreignId('shade_id')->constrained()->restrictOnDelete();
            $table->foreignId('quality_grade_id')->constrained()->restrictOnDelete();
            $table->enum('condition', ['sellable', 'damaged']);
            $table->enum('direction', ['in', 'out']);
            $table->decimal('qty_sqft', 16, 4);
            $table->decimal('qty_input', 16, 4);
            $table->string('unit_code');
            $table->unsignedInteger('pieces_per_box_snapshot');
            $table->decimal('sqft_per_piece_snapshot', 12, 6);
            $table->enum('movement_type', [
                'opening',
                'purchase',
                'purchase_return',
                'sale',
                'sale_return',
                'transfer_out',
                'transfer_in',
                'transfer_transit',
                'adjustment',
                'damage',
                'cancel',
            ]);
            $table->string('document_type')->nullable();
            $table->unsignedBigInteger('document_id')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('occurred_at');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('unit_code')->references('code')->on('units')->restrictOnDelete();
            $table->index(['document_type', 'document_id'], 'stock_movements_document_index');
            $table->index(['product_id', 'occurred_at'], 'stock_movements_product_occurred_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('warehouse_stocks');
    }
};
