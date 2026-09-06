<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = fake()->unique()->lexify('U???');

        return [
            'code' => strtoupper($code),
            'name' => fake()->word(),
            'name_bn' => null,
            'is_system' => false,
        ];
    }
}
