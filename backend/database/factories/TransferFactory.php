<?php

namespace Database\Factories;

use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Models\User;
use App\Modules\Inventory\Models\Location;
use App\Modules\Purchasing\Models\Transfer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transfer>
 */
class TransferFactory extends Factory
{
    protected $model = Transfer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'from_location_id' => Location::factory(),
            'to_location_id' => Location::factory(),
            'status' => Transfer::STATUS_CONFIRMED,
            'user_id' => User::factory(),
            'confirmed_at' => now(),
            'idempotency_key' => null,
            'notes' => null,
        ];
    }
}
