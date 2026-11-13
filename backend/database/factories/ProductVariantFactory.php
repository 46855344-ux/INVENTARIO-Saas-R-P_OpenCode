<?php

namespace Database\Factories;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'product_id' => Product::factory(),
            'name' => fake()->unique()->words(2, true),
            'sku' => 'SKU-'.Str::upper(Str::random(8)),
            'barcode' => null,
            'attributes' => null,
            'cost' => '0.0000',
            'price' => '0.0000',
            'status' => ProductVariant::STATUS_ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProductVariant::STATUS_INACTIVE,
        ]);
    }
}
