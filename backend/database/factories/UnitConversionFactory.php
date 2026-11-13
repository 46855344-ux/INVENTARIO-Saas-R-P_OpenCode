<?php

namespace Database\Factories;

use App\Modules\Identity\Models\Company;
use App\Modules\Inventory\Models\Unit;
use App\Modules\Inventory\Models\UnitConversion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UnitConversion>
 */
class UnitConversionFactory extends Factory
{
    protected $model = UnitConversion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'from_unit_id' => Unit::factory(),
            'to_unit_id' => Unit::factory(),
            'factor' => '1.000000',
            'status' => UnitConversion::STATUS_ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UnitConversion::STATUS_INACTIVE,
        ]);
    }
}
