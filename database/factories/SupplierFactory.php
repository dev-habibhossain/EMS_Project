<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('S-####'),
            'name' => fake()->company(),
            'name_bn' => null,
            'phone' => fake()->unique()->numerify('018########'),
            'alt_phone' => null,
            'address' => fake()->address(),
            'credit_limit' => null,
            'is_active' => true,
            'cached_balance' => '0.00',
        ];
    }
}
