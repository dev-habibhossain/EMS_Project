<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => fake()->unique()->numerify('PAY-######'),
            'party_type' => 'customer',
            'customer_id' => Customer::factory(),
            'supplier_id' => null,
            'method' => 'cash',
            'amount' => '1000.00',
            'direction' => 'in',
            'paid_at' => now(),
            'sale_id' => null,
            'notes' => null,
            'reversed_at' => null,
            'created_by' => User::factory(),
        ];
    }
}
