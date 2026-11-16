<?php

namespace Database\Factories;

use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\Company;
use App\Modules\Inventory\Models\Location;
use App\Modules\Metrics\Models\AlertRule;
use App\Modules\Metrics\Models\StockAlert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockAlert>
 */
class StockAlertFactory extends Factory
{
    protected $model = StockAlert::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'alert_rule_id' => AlertRule::factory(),
            'type' => AlertRule::TYPE_MIN_STOCK,
            'variant_id' => ProductVariant::factory(),
            'location_id' => Location::factory(),
            'message' => 'Existencia por debajo del mínimo.',
            'status' => StockAlert::STATUS_OPEN,
            'detected_at' => now(),
            'resolved_at' => null,
            'dedup_key' => fake()->unique()->uuid(),
        ];
    }
}
