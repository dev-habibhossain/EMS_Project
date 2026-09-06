<?php

namespace Database\Factories;

use App\Models\SmsLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SmsLog>
 */
class SmsLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => 'log',
            'to_phone' => fake()->numerify('017########'),
            'body' => fake()->sentence(),
            'status' => 'sent',
            'provider_message_id' => fake()->uuid(),
            'payload' => ['ok' => true],
        ];
    }
}
