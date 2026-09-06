<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => fake()->unique()->numerify('INV-######'),
            'customer_id' => Customer::factory(),
            'warehouse_id' => Warehouse::factory(),
            'status' => 'posted',
            'source' => 'pos',
            'sale_at' => now(),
            'subtotal' => '0.00',
            'discount_total' => '0.00',
            'tax_total' => '0.00',
            'grand_total' => '0.00',
            'paid_total' => '0.00',
            'due_total' => '0.00',
            'created_by' => User::factory(),
        ];
    }
}
