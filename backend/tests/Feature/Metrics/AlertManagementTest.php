<?php

namespace Tests\Feature\Metrics;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Services\TenantContext;
use App\Modules\Inventory\Models\Location;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Metrics\Models\AlertRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class AlertManagementTest extends TestCase
{
    use InteractsWithTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Origin', 'http://localhost:5173');
    }

    public function test_a_minimum_stock_alert_is_generated_once_and_resolved(): void
    {
        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id]);
        $product = Product::factory()->create(['company_id' => $company->id]);
        $variant = ProductVariant::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'sku' => 'MIN-1',
        ]);
        $balance = StockBalance::factory()->create([
            'company_id' => $company->id,
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'quantity' => '3.0000',
        ]);
        $actor = $this->member($company, ['alerts.view', 'alerts.manage', 'metrics.view']);

        $this->asMember($actor)
            ->postJson('/api/v1/alert-rules', [
                'type' => 'min_stock',
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'threshold' => '5',
            ])
            ->assertCreated()
            ->assertJsonPath('data.type', 'min_stock');

        $this->resetAuthState();
        $this->clearTenantContext();

        $this->artisan('alerts:evaluate', ['--company' => $company->id])->assertSuccessful();

        $this->assertDatabaseCount('stock_alerts', 1);
        $this->assertDatabaseHas('stock_alerts', [
            'company_id' => $company->id,
            'status' => 'open',
            'type' => 'min_stock',
        ]);
        $this->assertDatabaseHas('alert_evaluation_runs', [
            'company_id' => $company->id,
            'status' => 'completed',
            'evaluated' => 1,
            'generated' => 1,
        ]);

        $this->artisan('alerts:evaluate', ['--company' => $company->id])->assertSuccessful();

        $this->assertDatabaseCount('stock_alerts', 1);

        $balance->update(['quantity' => '10.0000']);
        $this->artisan('alerts:evaluate', ['--company' => $company->id])->assertSuccessful();

        $this->assertDatabaseHas('stock_alerts', [
            'company_id' => $company->id,
            'status' => 'resolved',
        ]);
    }

    public function test_an_inactivity_rule_generates_an_alert_without_movements(): void
    {
        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id]);
        $product = Product::factory()->create(['company_id' => $company->id]);
        $variant = ProductVariant::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
        ]);
        StockBalance::factory()->create([
            'company_id' => $company->id,
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'quantity' => '8.0000',
        ]);
        $actor = $this->member($company, ['alerts.view', 'alerts.manage']);

        $this->asMember($actor)
            ->postJson('/api/v1/alert-rules', [
                'type' => 'inactivity',
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'inactivity_days' => 30,
            ])
            ->assertCreated();

        $this->resetAuthState();
        $this->clearTenantContext();

        $this->artisan('alerts:evaluate', ['--company' => $company->id])->assertSuccessful();

        $this->assertDatabaseHas('stock_alerts', [
            'company_id' => $company->id,
            'type' => 'inactivity',
            'status' => 'open',
        ]);
    }

    public function test_alerts_and_rules_are_scoped_and_require_permission(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $foreignRule = AlertRule::factory()->create([
            'company_id' => $otherCompany->id,
        ]);
        $ownLocation = Location::factory()->create(['company_id' => $company->id]);
        $ownProduct = Product::factory()->create(['company_id' => $company->id]);
        $ownVariant = ProductVariant::factory()->create([
            'company_id' => $company->id,
            'product_id' => $ownProduct->id,
        ]);
        $actor = $this->member($company, ['alerts.view', 'alerts.manage']);

        $this->asMember($actor)
            ->getJson('/api/v1/alerts')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->resetAuthState();

        $viewer = $this->member($company, ['metrics.view']);

        $this->asMember($viewer)
            ->getJson('/api/v1/alerts')
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');

        $this->resetAuthState();

        $this->asMember($actor)
            ->patchJson("/api/v1/alert-rules/{$foreignRule->id}", [
                'type' => 'min_stock',
                'variant_id' => $ownVariant->id,
                'location_id' => $ownLocation->id,
                'threshold' => '1',
            ])
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'NOT_FOUND');
    }

    public function test_duplicate_rules_are_rejected(): void
    {
        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id]);
        $product = Product::factory()->create(['company_id' => $company->id]);
        $variant = ProductVariant::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
        ]);
        $actor = $this->member($company, ['alerts.manage']);
        $payload = [
            'type' => 'min_stock',
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'threshold' => '5',
        ];

        $this->asMember($actor)->postJson('/api/v1/alert-rules', $payload)->assertCreated();
        $this->resetAuthState();
        $this->asMember($actor)->postJson('/api/v1/alert-rules', $payload)
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    private function clearTenantContext(): void
    {
        request()->attributes->remove(TenantContext::class);
    }
}
