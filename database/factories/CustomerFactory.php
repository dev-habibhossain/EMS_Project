<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('C-####'),
            'name' => fake()->name(),
            'name_bn' => null,
            'phone' => fake()->unique()->numerify('017########'),
            'alt_phone' => null,
            'address' => fake()->address(),
            'credit_limit' => null,
            'is_walk_in' => false,
            'is_active' => true,
            'cached_balance' => '0.00',
        ];
    }
}
