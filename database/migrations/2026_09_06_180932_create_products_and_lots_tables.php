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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('factory_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tile_size_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('pieces_per_box');
            $table->decimal('sqft_per_piece', 12, 6);
            $table->decimal('sqft_per_box', 16, 6)->storedAs('pieces_per_box * sqft_per_piece');
            $table->string('barcode')->nullable();
            $table->boolean('requires_batch')->default(true);
            $table->boolean('requires_shade')->default(true);
            $table->foreignId('default_quality_id')->nullable()->constrained('quality_grades')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('name');
        });

        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('unit_code');
            $table->decimal('price', 16, 2);
            $table->timestamps();
            $table->foreign('unit_code')->references('code')->on('units')->restrictOnDelete();
            $table->unique(['product_id', 'unit_code'], 'product_prices_product_unit_unique');
        });

        Schema::create('shades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code');
            $table->string('name')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'code']);
        });

        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('code');
            $table->date('manufactured_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['product_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
        Schema::dropIfExists('shades');
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('products');
    }
};
