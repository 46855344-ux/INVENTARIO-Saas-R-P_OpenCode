<?php

namespace Database\Factories;

use App\Modules\Audit\Models\StockCount;
use App\Modules\Audit\Models\StockCountItem;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockCountItem>
 */
class StockCountItemFactory extends Factory
{
    protected $model = StockCountItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'stock_count_id' => StockCount::factory(),
            'variant_id' => ProductVariant::factory(),
            'theoretical_quantity' => '10.0000',
            'counted_quantity' => '8.0000',
            'difference' => '-2.0000',
        ];
    }
}
