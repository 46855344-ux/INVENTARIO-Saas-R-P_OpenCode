<?php

namespace Database\Factories;

use App\Modules\Catalog\Models\Product;
use App\Modules\Identity\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'category_id' => null,
            'brand_id' => null,
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
            'status' => Product::STATUS_ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Product::STATUS_INACTIVE,
        ]);
    }
}
