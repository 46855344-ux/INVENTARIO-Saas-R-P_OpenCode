<?php

namespace Database\Factories;

use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\Company;
use App\Modules\Inventory\Models\Location;
use App\Modules\Metrics\Models\AlertRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlertRule>
 */
class AlertRuleFactory extends Factory
{
    protected $model = AlertRule::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'type' => AlertRule::TYPE_MIN_STOCK,
            'variant_id' => ProductVariant::factory(),
            'location_id' => Location::factory(),
            'threshold' => '5.0000',
            'inactivity_days' => null,
            'status' => AlertRule::STATUS_ACTIVE,
        ];
    }
}
