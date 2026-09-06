<?php

namespace Database\Factories;

use App\Models\TileSize;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TileSize>
 */
class TileSizeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => fake()->randomElement(['600x600', '300x300', '800x800']),
            'length_mm' => 600,
            'width_mm' => 600,
            'default_sqft_per_piece' => '3.875000',
        ];
    }
}
