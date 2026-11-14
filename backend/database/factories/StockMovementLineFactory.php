<?php

namespace Database\Factories;

use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\Company;
use App\Modules\Inventory\Models\Location;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\StockMovementLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovementLine>
 */
class StockMovementLineFactory extends Factory
{
    protected $model = StockMovementLine::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'stock_movement_id' => StockMovement::factory(),
            'variant_id' => ProductVariant::factory(),
            'location_id' => Location::factory(),
            'unit_id' => null,
            'quantity' => '1.0000',
            'normalized_quantity' => '1.0000',
            'direction' => StockMovementLine::DIRECTION_IN,
        ];
    }
}
