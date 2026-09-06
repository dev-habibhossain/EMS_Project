<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerLedgerEntry>
 */
class CustomerLedgerEntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'entry_at' => now(),
            'debit' => '1000.00',
            'credit' => '0.00',
            'description' => 'Sale',
            'document_type' => null,
            'document_id' => null,
            'created_by' => User::factory(),
        ];
    }
}
