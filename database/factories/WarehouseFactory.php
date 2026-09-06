<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warehouse>
 */
class WarehouseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('WH-##'),
            'name' => fake()->city().' Warehouse',
            'name_bn' => null,
            'address' => fake()->address(),
            'type' => fake()->randomElement(['showroom', 'godown', 'virtual']),
            'is_active' => true,
        ];
    }
}
