<?php

namespace Database\Factories;

use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\User;
use App\Models\WarehouseStock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stock = WarehouseStock::factory()->create();

        return [
            'warehouse_stock_id' => $stock->id,
            'product_id' => $stock->product_id,
            'warehouse_id' => $stock->warehouse_id,
            'batch_id' => $stock->batch_id,
            'shade_id' => $stock->shade_id,
            'quality_grade_id' => $stock->quality_grade_id,
            'condition' => $stock->condition,
            'direction' => 'in',
            'qty_sqft' => '10.7600',
            'qty_input' => '1.0000',
            'unit_code' => Unit::factory()->create()->code,
            'pieces_per_box_snapshot' => 4,
            'sqft_per_piece_snapshot' => '2.690000',
            'movement_type' => 'opening',
            'document_type' => null,
            'document_id' => null,
            'note' => null,
            'occurred_at' => now(),
            'created_by' => User::factory(),
        ];
    }
}
