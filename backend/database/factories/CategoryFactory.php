<?php

namespace Database\Factories;

use App\Modules\Catalog\Models\Category;
use App\Modules\Identity\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

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
            'name' => fake()->unique()->words(2, true),
            'status' => Category::STATUS_ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Category::STATUS_INACTIVE,
        ]);
    }
}
