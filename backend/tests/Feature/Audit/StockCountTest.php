<?php

namespace Tests\Feature\Audit;

use App\Modules\Audit\Models\AuditLog;
use App\Modules\Audit\Models\StockCount;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\Company;
use App\Modules\Inventory\Models\Location;
use App\Modules\Inventory\Models\StockBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class StockCountTest extends TestCase
{
    use InteractsWithTenant, RefreshDatabase;

    private const PERMISSIONS = ['counts.view', 'counts.manage', 'counts.approve', 'audit.view'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withHeader('Origin', 'http://localhost:5173');
    }

    public function test_a_count_freezes_the_theoretical_stock_and_does_not_change_it(): void
    {
        $company = Company::factory()->create();
        [$variant, $location] = $this->inventory($company);
        StockBalance::factory()->create([
            'company_id' => $company->id,
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'quantity' => '10.0000',
        ]);
        $actor = $this->member($company, self::PERMISSIONS);

        $countId = $this->asMember($actor)
            ->postJson('/api/v1/stock-counts', [
                'location_id' => $location->id,
                'notes' => 'Conteo mensual',
                'items' => [
                    ['variant_id' => $variant->id, 'counted_quantity' => '8'],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.items.0.theoretical_quantity', '10.0000')
            ->assertJsonPath('data.items.0.counted_quantity', '8.0000')
            ->assertJsonPath('data.items.0.difference', '-2.0000')
            ->json('data.id');

        $this->assertDatabaseHas('stock_balances', [
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'quantity' => '10.0000',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'company_id' => $company->id,
            'action' => 'stock_count.created',
            'entity_id' => $countId,
        ]);
    }

    public function test_reviewing_and_approving_generates_the_adjustment_once(): void
    {
        $company = Company::factory()->create();
        [$variant, $location] = $this->inventory($company);
        StockBalance::factory()->create([
            'company_id' => $company->id,
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'quantity' => '10.0000',
        ]);
        $actor = $this->member($company, self::PERMISSIONS);

        $countId = $this->asMember($actor)
            ->postJson('/api/v1/stock-counts', [
                'location_id' => $location->id,
                'items' => [['variant_id' => $variant->id, 'counted_quantity' => '8']],
            ])
            ->assertCreated()
            ->json('data.id');

        $this->resetAuthState();
        $this->asMember($actor)
            ->patchJson("/api/v1/stock-counts/{$countId}/status", [
                'status' => 'reviewing',
                'reason' => 'Revisión de diferencias',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'reviewing');

        $this->resetAuthState();
        $this->asMember($actor)
            ->patchJson("/api/v1/stock-counts/{$countId}/status", [
                'status' => 'approved',
                'reason' => 'Diferencia verificada',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('stock_balances', [
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'quantity' => '8.0000',
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'company_id' => $company->id,
            'type' => 'adjustment',
            'reference_type' => 'stock_count',
            'reference_id' => $countId,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'stock_count.approved',
            'entity_id' => $countId,
        ]);

        $this->resetAuthState();
        $this->asMember($actor)
            ->patchJson("/api/v1/stock-counts/{$countId}/status", [
                'status' => 'approved',
                'reason' => 'Repetido',
            ])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'INVALID_COUNT_STATUS');
    }

    public function test_counts_are_scoped_and_require_permission(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $foreign = StockCount::factory()->create([
            'company_id' => $otherCompany->id,
        ]);
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->getJson('/api/v1/stock-counts')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->resetAuthState();

        $this->asMember($actor)
            ->getJson("/api/v1/stock-counts/{$foreign->id}")
            ->assertStatus(404);

        $this->resetAuthState();
        $viewer = $this->member($company, ['counts.view']);

        $this->asMember($viewer)
            ->postJson('/api/v1/stock-counts', [])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_audit_events_are_filterable_and_protected(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, self::PERMISSIONS);
        AuditLog::factory()->create([
            'company_id' => $company->id,
            'action' => 'stock_count.created',
        ]);
        AuditLog::factory()->create([
            'company_id' => $company->id,
            'action' => 'stock_count.approved',
        ]);

        $this->asMember($actor)
            ->getJson('/api/v1/audit-events?action=stock_count.approved')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.action', 'stock_count.approved');

        $this->resetAuthState();
        $viewer = $this->member($company, ['counts.view']);

        $this->asMember($viewer)
            ->getJson('/api/v1/audit-events')
            ->assertStatus(403);
    }

    /**
     * @return array{0: ProductVariant, 1: Location}
     */
    private function inventory(Company $company): array
    {
        $product = Product::factory()->create(['company_id' => $company->id]);
        $variant = ProductVariant::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
        ]);
        $location = Location::factory()->create(['company_id' => $company->id]);

        return [$variant, $location];
    }
}
