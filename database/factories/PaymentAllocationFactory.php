<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentAllocation>
 */
class PaymentAllocationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sale = Sale::factory()->create();

        return [
            'payment_id' => Payment::factory(),
            'document_type' => $sale->getMorphClass(),
            'document_id' => $sale->id,
            'amount' => '1000.00',
        ];
    }
}
