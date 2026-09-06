<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseTransfer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WarehouseTransfer>
 */
class WarehouseTransferFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => fake()->unique()->numerify('TR-######'),
            'from_warehouse_id' => Warehouse::factory(),
            'to_warehouse_id' => Warehouse::factory(),
            'status' => 'draft',
            'dispatched_at' => null,
            'received_at' => null,
            'created_by' => User::factory(),
        ];
    }
}
