<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => fake()->unique()->bothify('SKU-####??'),
            'name' => fake()->words(3, true),
            'name_bn' => null,
            'pieces_per_box' => 4,
            'sqft_per_piece' => '2.690000',
            'barcode' => null,
            'requires_batch' => true,
            'requires_shade' => true,
            'is_active' => true,
        ];
    }
}
