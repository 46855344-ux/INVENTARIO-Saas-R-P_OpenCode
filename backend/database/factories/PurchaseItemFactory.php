<?php

namespace Database\Factories;

use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\Company;
use App\Modules\Inventory\Models\Location;
use App\Modules\Purchasing\Models\Purchase;
use App\Modules\Purchasing\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseItem>
 */
class PurchaseItemFactory extends Factory
{
    protected $model = PurchaseItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'purchase_id' => Purchase::factory(),
            'variant_id' => ProductVariant::factory(),
            'location_id' => Location::factory(),
            'quantity' => '1.0000',
            'unit_cost' => '10.0000',
            'subtotal' => '10.00',
        ];
    }
}
