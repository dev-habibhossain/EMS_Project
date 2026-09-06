<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\SupplierLedgerEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierLedgerEntry>
 */
class SupplierLedgerEntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'entry_at' => now(),
            'debit' => '0.00',
            'credit' => '1000.00',
            'description' => 'Purchase',
            'document_type' => null,
            'document_id' => null,
            'created_by' => User::factory(),
        ];
    }
}
