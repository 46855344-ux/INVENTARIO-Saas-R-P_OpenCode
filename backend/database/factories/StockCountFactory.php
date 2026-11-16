<?php

namespace Database\Factories;

use App\Modules\Audit\Models\StockCount;
use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Models\User;
use App\Modules\Inventory\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockCount>
 */
class StockCountFactory extends Factory
{
    protected $model = StockCount::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'location_id' => Location::factory(),
            'status' => StockCount::STATUS_OPEN,
            'user_id' => User::factory(),
            'counted_at' => now(),
            'notes' => null,
        ];
    }
}
