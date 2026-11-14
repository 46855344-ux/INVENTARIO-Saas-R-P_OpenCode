<?php

namespace Database\Factories;

use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\Company;
use App\Modules\Purchasing\Models\Transfer;
use App\Modules\Purchasing\Models\TransferItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransferItem>
 */
class TransferItemFactory extends Factory
{
    protected $model = TransferItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'transfer_id' => Transfer::factory(),
            'variant_id' => ProductVariant::factory(),
            'quantity' => '1.0000',
        ];
    }
}
