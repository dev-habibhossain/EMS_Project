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
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('sale_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['draft', 'posted', 'cancelled']);
            $table->enum('restock_mode', ['sellable', 'damaged', 'none']);
            $table->enum('money_action', ['refund', 'credit']);
            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_item_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('batch_id')->constrained()->restrictOnDelete();
            $table->foreignId('shade_id')->constrained()->restrictOnDelete();
            $table->foreignId('quality_grade_id')->constrained()->restrictOnDelete();
            $table->enum('condition', ['sellable', 'damaged'])->default('sellable');
            $table->string('unit_code');
            $table->decimal('qty_input', 16, 4);
            $table->decimal('qty_sqft', 16, 4);
            $table->decimal('unit_price', 16, 2);
            $table->decimal('line_total', 16, 2);
            $table->unsignedInteger('pieces_per_box_snapshot');
            $table->decimal('sqft_per_piece_snapshot', 12, 6);
            $table->timestamps();
            $table->foreign('unit_code')->references('code')->on('units')->restrictOnDelete();
        });

        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('purchase_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['draft', 'posted', 'cancelled']);
            $table->enum('money_action', ['refund', 'credit']);
            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('batch_id')->constrained()->restrictOnDelete();
            $table->foreignId('shade_id')->constrained()->restrictOnDelete();
            $table->foreignId('quality_grade_id')->constrained()->restrictOnDelete();
            $table->enum('condition', ['sellable', 'damaged'])->default('sellable');
            $table->string('unit_code');
            $table->decimal('qty_input', 16, 4);
            $table->decimal('qty_sqft', 16, 4);
            $table->decimal('unit_price', 16, 2);
            $table->decimal('line_total', 16, 2);
            $table->unsignedInteger('pieces_per_box_snapshot');
            $table->decimal('sqft_per_piece_snapshot', 12, 6);
            $table->timestamps();
            $table->foreign('unit_code')->references('code')->on('units')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
        Schema::dropIfExists('sales_return_items');
        Schema::dropIfExists('sales_returns');
    }
};
