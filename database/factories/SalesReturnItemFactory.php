<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use App\Models\QualityGrade;
use App\Models\SaleItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Shade;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesReturnItem>
 */
class SalesReturnItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::factory();

        return [
            'sales_return_id' => SalesReturn::factory(),
            'sale_item_id' => SaleItem::factory(),
            'product_id' => $product,
            'batch_id' => Batch::factory()->for($product),
            'shade_id' => Shade::factory()->for($product),
            'quality_grade_id' => QualityGrade::factory(),
            'condition' => 'sellable',
            'unit_code' => fn (): string => Unit::factory()->create()->code,
            'qty_input' => '1.0000',
            'qty_sqft' => '10.7600',
            'unit_price' => '1000.00',
            'line_total' => '1000.00',
            'pieces_per_box_snapshot' => 4,
            'sqft_per_piece_snapshot' => '2.690000',
        ];
    }
}
