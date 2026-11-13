<?php

namespace Database\Factories;

use App\Modules\Identity\Models\Company;
use App\Modules\Inventory\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    protected $model = Unit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'code' => 'U-'.Str::upper(Str::random(6)),
            'name' => fake()->unique()->word(),
            'dimension' => 'count',
            'precision' => 4,
            'status' => Unit::STATUS_ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Unit::STATUS_INACTIVE,
        ]);
    }
}
