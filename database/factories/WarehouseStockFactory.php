<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use App\Models\QualityGrade;
use App\Models\Shade;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WarehouseStock>
 */
class WarehouseStockFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::factory();

        return [
            'product_id' => $product,
            'batch_id' => Batch::factory()->for($product),
            'shade_id' => Shade::factory()->for($product),
            'quality_grade_id' => QualityGrade::factory(),
            'warehouse_id' => Warehouse::factory(),
            'condition' => 'sellable',
            'qty_sqft' => '0.0000',
        ];
    }
}
