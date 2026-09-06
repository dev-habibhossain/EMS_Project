<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\DeliveryChallan;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryChallan>
 */
class DeliveryChallanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => fake()->unique()->numerify('CH-######'),
            'customer_id' => Customer::factory(),
            'warehouse_id' => Warehouse::factory(),
            'sale_id' => null,
            'destination_address' => fake()->address(),
            'driver_name' => fake()->name(),
            'vehicle_no' => fake()->bothify('DHK-####'),
            'status' => 'draft',
            'dispatched_at' => null,
            'notes' => null,
            'created_by' => User::factory(),
        ];
    }
}
