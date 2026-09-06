<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => 'created',
            'subject_type' => (new Product)->getMorphClass(),
            'subject_id' => Product::factory(),
            'properties' => ['sku' => 'RAK-60-WHT'],
            'ip' => '127.0.0.1',
        ];
    }
}
