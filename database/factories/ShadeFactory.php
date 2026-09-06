<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Shade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shade>
 */
class ShadeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'code' => fake()->unique()->bothify('SH-##'),
            'name' => fake()->optional()->colorName(),
        ];
    }
}
