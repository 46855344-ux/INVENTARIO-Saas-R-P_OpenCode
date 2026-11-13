<?php

namespace Database\Factories;

use App\Modules\Identity\Models\Company;
use App\Modules\Inventory\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'parent_id' => null,
            'code' => 'LOC-'.Str::upper(Str::random(6)),
            'name' => fake()->unique()->words(2, true),
            'type' => 'warehouse',
            'status' => Location::STATUS_ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Location::STATUS_INACTIVE,
        ]);
    }
}
