<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Batch>
 */
class BatchFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'code' => fake()->unique()->bothify('B-####'),
            'manufactured_on' => fake()->optional()->date(),
            'notes' => null,
        ];
    }
}
