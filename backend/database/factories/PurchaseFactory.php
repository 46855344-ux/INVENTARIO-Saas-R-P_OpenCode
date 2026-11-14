<?php

namespace Database\Factories;

use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Models\User;
use App\Modules\Purchasing\Models\Purchase;
use App\Modules\Purchasing\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'supplier_id' => Supplier::factory(),
            'document_number' => fake()->bothify('FAC-####'),
            'ordered_at' => now()->toDateString(),
            'status' => Purchase::STATUS_DRAFT,
            'total' => '0.00',
            'user_id' => User::factory(),
            'received_by' => null,
            'received_at' => null,
            'reception_idempotency_key' => null,
            'notes' => null,
        ];
    }

    public function received(): static
    {
        return $this->state(fn (): array => [
            'status' => Purchase::STATUS_RECEIVED,
            'received_at' => now(),
        ]);
    }
}
