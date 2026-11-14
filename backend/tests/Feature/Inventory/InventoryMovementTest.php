<?php

namespace Tests\Feature\Inventory;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\Company;
use App\Modules\Inventory\Models\Location;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class InventoryMovementTest extends TestCase
{
    use InteractsWithTenant, RefreshDatabase;

    /**
     * @var list<string>
     */
    private const PERMISSIONS = ['inventory.view', 'inventory.move', 'inventory.adjust', 'inventory.approve'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Origin', 'http://localhost:5173');
    }

    public function test_an_entry_increments_stock_and_registers_a_movement(): void
    {
        $company = Company::factory()->create();
        [$variant, $location, $unit] = $this->inventory($company);
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'entry-1')
            ->postJson('/api/v1/inventory/entries', [
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'unit_id' => $unit->id,
                'quantity' => '10',
                'reason' => 'Ingreso inicial',
                'document_reference' => 'DOC-1',
            ])
            ->assertCreated()
            ->assertJsonPath('data.type', 'entry')
            ->assertJsonPath('data.lines.0.direction', 'in')
            ->assertJsonPath('data.lines.0.quantity', '10.0000');

        $this->assertDatabaseHas('stock_balances', [
            'company_id' => $company->id,
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'quantity' => '10.0000',
        ]);
    }

    public function test_a_repeated_entry_with_the_same_idempotency_key_does_not_duplicate_stock(): void
    {
        $company = Company::factory()->create();
        [$variant, $location] = $this->inventory($company);
        $actor = $this->member($company, self::PERMISSIONS);
        $payload = [
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'quantity' => '10',
            'reason' => 'Ingreso',
        ];

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'entry-1')
            ->postJson('/api/v1/inventory/entries', $payload)
            ->assertCreated();

        $this->resetAuthState();

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'entry-1')
            ->postJson('/api/v1/inventory/entries', $payload)
            ->assertOk();

        $this->assertDatabaseHas('stock_balances', [
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'quantity' => '10.0000',
        ]);
        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_an_entry_requires_the_idempotency_header(): void
    {
        $company = Company::factory()->create();
        [$variant, $location] = $this->inventory($company);
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/inventory/entries', [
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'quantity' => '10',
                'reason' => 'Ingreso',
            ])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_an_exit_decrements_stock_and_rejects_insufficient_stock(): void
    {
        $company = Company::factory()->create();
        [$variant, $location] = $this->inventory($company);
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'entry-1')
            ->postJson('/api/v1/inventory/entries', [
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'quantity' => '10',
                'reason' => 'Ingreso',
            ])
            ->assertCreated();

        $this->resetAuthState();

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'exit-1')
            ->postJson('/api/v1/inventory/exits', [
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'quantity' => '4',
                'reason' => 'Salida',
            ])
            ->assertCreated()
            ->assertJsonPath('data.type', 'exit');

        $this->assertDatabaseHas('stock_balances', [
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'quantity' => '6.0000',
        ]);

        $this->resetAuthState();

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'exit-2')
            ->postJson('/api/v1/inventory/exits', [
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'quantity' => '7',
                'reason' => 'Salida excedida',
            ])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'INSUFFICIENT_STOCK');

        $this->assertDatabaseHas('stock_balances', [
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'quantity' => '6.0000',
        ]);
    }

    public function test_an_adjustment_requires_reason_and_permission(): void
    {
        $company = Company::factory()->create();
        [$variant, $location] = $this->inventory($company);
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'entry-1')
            ->postJson('/api/v1/inventory/entries', [
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'quantity' => '5',
                'reason' => 'Ingreso',
            ])
            ->assertCreated();

        $this->resetAuthState();

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'adjust-1')
            ->postJson('/api/v1/inventory/adjustments', [
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'quantity' => '2',
                'direction' => 'out',
                'reason' => 'Conteo',
            ])
            ->assertCreated()
            ->assertJsonPath('data.type', 'adjustment');

        $this->assertDatabaseHas('stock_balances', [
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'quantity' => '3.0000',
        ]);

        $this->resetAuthState();

        $operator = $this->member($company, ['inventory.view', 'inventory.move']);

        $this->asMember($operator)
            ->withHeader('Idempotency-Key', 'adjust-2')
            ->postJson('/api/v1/inventory/adjustments', [
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'quantity' => '1',
                'direction' => 'out',
                'reason' => 'Sin permiso',
            ])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_a_return_registers_an_independent_movement(): void
    {
        $company = Company::factory()->create();
        [$variant, $location] = $this->inventory($company);
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'exit-origin')
            ->postJson('/api/v1/inventory/entries', [
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'quantity' => '5',
                'reason' => 'Ingreso',
            ])
            ->assertCreated();

        $origin = StockMovement::query()->where('idempotency_key', 'exit-origin')->firstOrFail();

        $this->resetAuthState();

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'return-1')
            ->postJson('/api/v1/inventory/returns', [
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'quantity' => '2',
                'reason' => 'Devolución',
                'original_movement_id' => $origin->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.type', 'return')
            ->assertJsonPath('data.original_movement_id', $origin->id);

        $this->assertDatabaseHas('stock_balances', [
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'quantity' => '7.0000',
        ]);
    }

    public function test_a_movement_can_be_reversed_once(): void
    {
        $company = Company::factory()->create();
        [$variant, $location] = $this->inventory($company);
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'entry-1')
            ->postJson('/api/v1/inventory/entries', [
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'quantity' => '10',
                'reason' => 'Ingreso',
            ])
            ->assertCreated();

        $original = StockMovement::query()->where('idempotency_key', 'entry-1')->firstOrFail();

        $this->resetAuthState();

        $this->asMember($actor)
            ->postJson("/api/v1/inventory/movements/{$original->id}/reverse", [
                'reason' => 'Error de captura',
            ])
            ->assertCreated()
            ->assertJsonPath('data.type', 'reversal')
            ->assertJsonPath('data.reversal_of_movement_id', $original->id);

        $this->assertDatabaseHas('stock_balances', [
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'quantity' => '0.0000',
        ]);

        $this->resetAuthState();

        $this->asMember($actor)
            ->postJson("/api/v1/inventory/movements/{$original->id}/reverse", [
                'reason' => 'Segunda reversión',
            ])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'ALREADY_REVERSED');
    }

    public function test_a_reversal_movement_cannot_be_reversed(): void
    {
        $company = Company::factory()->create();
        [$variant, $location] = $this->inventory($company);
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'entry-1')
            ->postJson('/api/v1/inventory/entries', [
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'quantity' => '10',
                'reason' => 'Ingreso',
            ])
            ->assertCreated();

        $original = StockMovement::query()->where('idempotency_key', 'entry-1')->firstOrFail();

        $this->resetAuthState();

        $this->asMember($actor)
            ->postJson("/api/v1/inventory/movements/{$original->id}/reverse", ['reason' => 'Error'])
            ->assertCreated();

        $reversal = StockMovement::query()->where('reversal_of_movement_id', $original->id)->firstOrFail();

        $this->resetAuthState();

        $this->asMember($actor)
            ->postJson("/api/v1/inventory/movements/{$reversal->id}/reverse", ['reason' => 'Otra'])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'NOT_REVERSIBLE');
    }

    public function test_a_variant_or_location_from_another_company_is_rejected(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        [$ownVariant, $ownLocation] = $this->inventory($company);
        [$foreignVariant] = $this->inventory($otherCompany);
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'foreign-1')
            ->postJson('/api/v1/inventory/entries', [
                'variant_id' => $foreignVariant->id,
                'location_id' => $ownLocation->id,
                'quantity' => '1',
                'reason' => 'Cruzado',
            ])
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'NOT_FOUND');

        $this->resetAuthState();

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'foreign-2')
            ->postJson('/api/v1/inventory/entries', [
                'variant_id' => $ownVariant->id,
                'location_id' => $ownLocation->id,
                'quantity' => '1',
                'reason' => 'Propio',
            ])
            ->assertCreated();
    }

    public function test_movements_are_listed_and_visible_only_for_the_active_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        [$variant, $location] = $this->inventory($company);
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'entry-1')
            ->postJson('/api/v1/inventory/entries', [
                'variant_id' => $variant->id,
                'location_id' => $location->id,
                'quantity' => '10',
                'reason' => 'Ingreso',
            ])
            ->assertCreated();

        $this->resetAuthState();

        $this->asMember($actor)
            ->getJson('/api/v1/inventory/movements')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'entry');

        $foreignActor = $this->member($otherCompany, self::PERMISSIONS);

        $this->resetAuthState();

        $this->asMember($foreignActor)
            ->getJson('/api/v1/inventory/movements')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    /**
     * @return array{0: ProductVariant, 1: Location, 2: Unit}
     */
    private function inventory(Company $company): array
    {
        $product = Product::factory()->create(['company_id' => $company->id]);
        $variant = ProductVariant::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
        ]);
        $location = Location::factory()->create(['company_id' => $company->id]);
        $unit = Unit::factory()->create(['company_id' => $company->id]);

        return [$variant, $location, $unit];
    }
}
