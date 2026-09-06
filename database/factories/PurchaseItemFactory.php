<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\QualityGrade;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseItem>
 */
class PurchaseItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'purchase_id' => Purchase::factory(),
            'product_id' => Product::factory(),
            'quality_grade_id' => QualityGrade::factory(),
            'unit_code' => fn (): string => Unit::factory()->create()->code,
            'qty_ordered' => '10.0000',
            'qty_received' => '0.0000',
            'qty_sqft_ordered' => '107.6000',
            'unit_price' => '1000.00',
            'line_total' => '10000.00',
            'pieces_per_box_snapshot' => 4,
            'sqft_per_piece_snapshot' => '2.690000',
        ];
    }
}
