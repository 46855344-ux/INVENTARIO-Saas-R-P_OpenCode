<?php

namespace Database\Factories;

use App\Modules\Identity\Models\Company;
use App\Modules\Purchasing\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->company(),
            'tax_id' => null,
            'email' => fake()->safeEmail(),
            'phone' => fake()->numerify('+54 11 ####-####'),
            'status' => Supplier::STATUS_ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['status' => Supplier::STATUS_INACTIVE]);
    }
}
