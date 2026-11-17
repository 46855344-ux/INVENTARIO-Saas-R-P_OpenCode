<?php

namespace Database\Seeders;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\Company;
use App\Modules\Inventory\Models\Location;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Metrics\Models\AlertRule;
use App\Modules\Purchasing\Models\Supplier;
use Illuminate\Database\Seeder;

/**
 * Datos mínimos y deterministas para la demo y las pruebas E2E.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->where('slug', 'empresa-demo')->first();

        if ($company === null) {
            return;
        }

        $origin = Location::query()->firstOrCreate(
            ['company_id' => $company->id, 'code' => 'ALM-E2E'],
            ['name' => 'Almacén E2E', 'type' => 'warehouse', 'status' => Location::STATUS_ACTIVE],
        );

        $destination = Location::query()->firstOrCreate(
            ['company_id' => $company->id, 'code' => 'SUC-E2E'],
            ['name' => 'Sucursal E2E', 'type' => 'warehouse', 'status' => Location::STATUS_ACTIVE],
        );

        $product = Product::query()->firstOrCreate(
            ['company_id' => $company->id, 'name' => 'Producto E2E'],
            ['description' => 'Producto para pruebas de aceptación', 'status' => Product::STATUS_ACTIVE],
        );

        $variant = ProductVariant::query()->firstOrCreate(
            ['company_id' => $company->id, 'sku' => 'E2E-1'],
            [
                'product_id' => $product->id,
                'name' => 'Variante E2E',
                'cost' => '10.0000',
                'price' => '15.0000',
                'status' => ProductVariant::STATUS_ACTIVE,
            ],
        );

        StockBalance::query()->updateOrCreate(
            ['company_id' => $company->id, 'variant_id' => $variant->id, 'location_id' => $origin->id],
            ['quantity' => '100.0000', 'reserved_quantity' => '0.0000'],
        );

        Supplier::query()->firstOrCreate(
            ['company_id' => $company->id, 'name' => 'Proveedor E2E'],
            ['tax_id' => '30-E2E-0001', 'status' => Supplier::STATUS_ACTIVE],
        );

        AlertRule::query()->firstOrCreate(
            [
                'company_id' => $company->id,
                'type' => AlertRule::TYPE_MIN_STOCK,
                'variant_id' => $variant->id,
                'location_id' => $destination->id,
            ],
            ['threshold' => '2.0000', 'status' => AlertRule::STATUS_ACTIVE],
        );
    }
}
