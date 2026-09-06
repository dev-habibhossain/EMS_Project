<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SalesReturn>
 */
class SalesReturnFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sale = Sale::factory();

        return [
            'number' => fake()->unique()->numerify('SR-######'),
            'sale_id' => $sale,
            'customer_id' => Customer::factory(),
            'warehouse_id' => Warehouse::factory(),
            'status' => 'draft',
            'restock_mode' => 'sellable',
            'money_action' => 'credit',
            'subtotal' => '0.00',
            'total' => '0.00',
            'notes' => null,
            'created_by' => User::factory(),
        ];
    }
}
