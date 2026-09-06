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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['draft', 'posted', 'cancelled']);
            $table->enum('source', ['pos', 'backoffice']);
            $table->timestamp('sale_at');
            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('discount_total', 16, 2)->default(0);
            $table->decimal('tax_total', 16, 2)->default(0);
            $table->decimal('grand_total', 16, 2)->default(0);
            $table->decimal('paid_total', 16, 2)->default(0);
            $table->decimal('due_total', 16, 2)->default(0);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('batch_id')->constrained()->restrictOnDelete();
            $table->foreignId('shade_id')->constrained()->restrictOnDelete();
            $table->foreignId('quality_grade_id')->constrained()->restrictOnDelete();
            $table->enum('condition', ['sellable', 'damaged'])->default('sellable');
            $table->string('unit_code');
            $table->decimal('qty_input', 16, 4);
            $table->decimal('qty_sqft', 16, 4);
            $table->decimal('unit_price', 16, 2);
            $table->decimal('discount_amount', 16, 2)->default(0);
            $table->decimal('line_total', 16, 2);
            $table->decimal('unit_cost', 16, 2)->nullable();
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
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
