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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['draft', 'ordered', 'partial', 'received', 'cancelled']);
            $table->date('order_date');
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('discount', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('shade_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('quality_grade_id')->constrained()->restrictOnDelete();
            $table->string('unit_code');
            $table->decimal('qty_ordered', 16, 4);
            $table->decimal('qty_received', 16, 4)->default(0);
            $table->decimal('qty_sqft_ordered', 16, 4);
            $table->decimal('unit_price', 16, 2);
            $table->decimal('line_total', 16, 2);
            $table->unsignedInteger('pieces_per_box_snapshot');
            $table->decimal('sqft_per_piece_snapshot', 12, 6);
            $table->timestamps();
            $table->foreign('unit_code')->references('code')->on('units')->restrictOnDelete();
        });

        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('purchase_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['draft', 'posted', 'cancelled']);
            $table->timestamp('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
