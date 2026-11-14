<?php

namespace Database\Factories;

use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Models\User;
use App\Modules\Inventory\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'type' => StockMovement::TYPE_ENTRY,
            'status' => StockMovement::STATUS_CONFIRMED,
            'user_id' => User::factory(),
            'reason' => 'Movimiento de prueba',
            'document_reference' => null,
            'original_movement_id' => null,
            'idempotency_key' => null,
            'confirmed_at' => now(),
        ];
    }
}
