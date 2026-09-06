<?php

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseReturn>
 */
class PurchaseReturnFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => fake()->unique()->numerify('PR-######'),
            'purchase_id' => Purchase::factory(),
            'supplier_id' => Supplier::factory(),
            'warehouse_id' => Warehouse::factory(),
            'status' => 'draft',
            'money_action' => 'credit',
            'subtotal' => '0.00',
            'total' => '0.00',
            'notes' => null,
            'created_by' => User::factory(),
        ];
    }
}
