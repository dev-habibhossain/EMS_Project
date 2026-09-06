<?php

namespace Database\Factories;

use App\Models\QualityGrade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QualityGrade>
 */
class QualityGradeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('Q-##'),
            'name' => fake()->randomElement(['Grade A', 'Grade B', 'Commercial']),
            'name_bn' => null,
            'is_sellable' => true,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
