<?php

namespace Database\Factories;

use App\Models\GoodsReceipt;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GoodsReceipt>
 */
class GoodsReceiptFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $purchase = Purchase::factory();

        return [
            'number' => fake()->unique()->numerify('GR-######'),
            'purchase_id' => $purchase,
            'supplier_id' => Supplier::factory(),
            'warehouse_id' => Warehouse::factory(),
            'status' => 'draft',
            'received_at' => null,
            'notes' => null,
            'created_by' => User::factory(),
        ];
    }
}
