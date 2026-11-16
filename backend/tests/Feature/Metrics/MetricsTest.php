<?php

namespace Tests\Feature\Metrics;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\Company;
use App\Modules\Inventory\Models\Location;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\StockMovementLine;
use App\Modules\Metrics\Models\AlertRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class MetricsTest extends TestCase
{
    use InteractsWithTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Origin', 'http://localhost:5173');
    }

    public function test_the_overview_aggregates_only_the_active_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id]);
        $product = Product::factory()->create(['company_id' => $company->id]);
        $lowVariant = ProductVariant::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'sku' => 'LOW-1',
        ]);
        $emptyVariant = ProductVariant::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'sku' => 'EMPTY-1',
        ]);
        $inactiveVariant = ProductVariant::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'sku' => 'INACTIVE-1',
        ]);

        StockBalance::factory()->create([
            'company_id' => $company->id,
            'variant_id' => $lowVariant->id,
            'location_id' => $location->id,
            'quantity' => '3.0000',
        ]);
        StockBalance::factory()->create([
            'company_id' => $company->id,
            'variant_id' => $emptyVariant->id,
            'location_id' => $location->id,
            'quantity' => '0.0000',
        ]);
        StockBalance::factory()->create([
            'company_id' => $company->id,
            'variant_id' => $inactiveVariant->id,
            'location_id' => $location->id,
            'quantity' => '7.0000',
        ]);
        AlertRule::factory()->create([
            'company_id' => $company->id,
            'type' => AlertRule::TYPE_MIN_STOCK,
            'variant_id' => $lowVariant->id,
            'location_id' => $location->id,
            'threshold' => '5.0000',
        ]);

        $movement = StockMovement::factory()->create([
            'company_id' => $company->id,
            'type' => StockMovement::TYPE_EXIT,
            'status' => StockMovement::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);
        StockMovementLine::factory()->create([
            'company_id' => $company->id,
            'stock_movement_id' => $movement->id,
            'variant_id' => $lowVariant->id,
            'location_id' => $location->id,
            'quantity' => '2.0000',
            'normalized_quantity' => '2.0000',
            'direction' => StockMovementLine::DIRECTION_OUT,
        ]);

        $foreignLocation = Location::factory()->create(['company_id' => $otherCompany->id]);
        $foreignProduct = Product::factory()->create(['company_id' => $otherCompany->id]);
        $foreignVariant = ProductVariant::factory()->create([
            'company_id' => $otherCompany->id,
            'product_id' => $foreignProduct->id,
        ]);
        StockBalance::factory()->create([
            'company_id' => $otherCompany->id,
            'variant_id' => $foreignVariant->id,
            'location_id' => $foreignLocation->id,
            'quantity' => '99.0000',
        ]);

        $actor = $this->member($company, ['metrics.view']);

        $this->asMember($actor)
            ->getJson('/api/v1/metrics/overview')
            ->assertOk()
            ->assertJsonPath('data.total_products', 1)
            ->assertJsonPath('data.total_variants', 3)
            ->assertJsonPath('data.out_of_stock', 1)
            ->assertJsonPath('data.low_stock', 1)
            ->assertJsonPath('data.total_value', null)
            ->assertJsonPath('data.valuation_status', 'pending')
            ->assertJsonPath('data.top_outflows.0.sku', 'LOW-1')
            ->assertJsonPath('data.top_outflows.0.quantity', '2')
            ->assertJsonPath('data.inactive.0.sku', 'INACTIVE-1');
    }

    public function test_stock_detail_is_paginated_and_scoped(): void
    {
        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id]);
        $product = Product::factory()->create(['company_id' => $company->id]);
        $variant = ProductVariant::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'sku' => 'DETAIL-1',
        ]);
        StockBalance::factory()->create([
            'company_id' => $company->id,
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'quantity' => '4.0000',
        ]);
        $actor = $this->member($company, ['metrics.view']);

        $this->asMember($actor)
            ->getJson('/api/v1/metrics/stock-detail?per_page=10')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.variant.sku', 'DETAIL-1')
            ->assertJsonPath('data.0.quantity', '4.0000')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_metrics_require_permission(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, ['inventory.view']);

        $this->asMember($actor)
            ->getJson('/api/v1/metrics/overview')
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }
}
