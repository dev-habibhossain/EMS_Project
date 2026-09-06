<?php

namespace Database\Factories;

use App\Models\Factory as CeramicFactory;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CeramicFactory>
 */
#[UseModel(CeramicFactory::class)]
class FactoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'name_bn' => null,
            'code' => fake()->unique()->bothify('FAC-###'),
            'is_active' => true,
        ];
    }
}
