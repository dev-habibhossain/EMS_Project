<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\QualityGrade;
use App\Models\Shade;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GoodsReceiptItem>
 */
class GoodsReceiptItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::factory();

        return [
            'goods_receipt_id' => GoodsReceipt::factory(),
            'product_id' => $product,
            'batch_id' => Batch::factory()->for($product),
            'shade_id' => Shade::factory()->for($product),
            'quality_grade_id' => QualityGrade::factory(),
            'condition' => 'sellable',
            'unit_code' => fn (): string => Unit::factory()->create()->code,
            'qty_input' => '10.0000',
            'qty_sqft' => '107.6000',
            'unit_price' => '1000.00',
            'line_total' => '10000.00',
            'pieces_per_box_snapshot' => 4,
            'sqft_per_piece_snapshot' => '2.690000',
        ];
    }
}
