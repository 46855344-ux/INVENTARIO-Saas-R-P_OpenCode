<?php

namespace Tests\Feature\Purchasing;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Models\User;
use App\Modules\Inventory\Models\Location;
use App\Modules\Purchasing\Models\Purchase;
use App\Modules\Purchasing\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class PurchaseManagementTest extends TestCase
{
    use InteractsWithTenant, RefreshDatabase;

    /**
     * @var list<string>
     */
    private const PERMISSIONS = ['purchases.view', 'purchases.manage', 'purchases.receive'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Origin', 'http://localhost:5173');
    }

    public function test_a_purchase_is_created_as_draft_without_touching_stock(): void
    {
        $company = Company::factory()->create();
        $supplier = $this->supplier($company);
        $variant = $this->variant($company);
        $location = $this->location($company);
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/purchases', $this->payload($supplier, $variant, $location, [
                'quantity' => '12',
                'unit_cost' => '24.50',
            ]))
            ->assertCreated()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.total', '294.00')
            ->assertJsonPath('data.items.0.quantity', '12.0000')
            ->assertJsonPath('data.items.0.unit_cost', '24.5000')
            ->assertJsonPath('data.items.0.subtotal', '294.00');

        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseCount('stock_balances', 0);
    }

    public function test_purchase_costs_are_historical_between_purchases(): void
    {
        $company = Company::factory()->create();
        $supplier = $this->supplier($company);
        $variant = $this->variant($company);
        $location = $this->location($company);
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/purchases', $this->payload($supplier, $variant, $location, [
                'quantity' => '2',
                'unit_cost' => '10',
            ]))
            ->assertCreated();

        $this->resetAuthState();

        $this->asMember($actor)
            ->postJson('/api/v1/purchases', $this->payload($supplier, $variant, $location, [
                'quantity' => '3',
                'unit_cost' => '15',
                'document_number' => 'FAC-002',
            ]))
            ->assertCreated()
            ->assertJsonPath('data.total', '45.00');

        $this->assertDatabaseHas('purchase_items', [
            'variant_id' => $variant->id,
            'unit_cost' => '10.0000',
            'subtotal' => '20.00',
        ]);
        $this->assertDatabaseHas('purchase_items', [
            'variant_id' => $variant->id,
            'unit_cost' => '15.0000',
            'subtotal' => '45.00',
        ]);
    }

    public function test_a_purchase_can_be_edited_while_it_is_a_draft(): void
    {
        $company = Company::factory()->create();
        $supplier = $this->supplier($company);
        $variant = $this->variant($company);
        $location = $this->location($company);
        $actor = $this->member($company, self::PERMISSIONS);

        $purchaseId = $this->asMember($actor)
            ->postJson('/api/v1/purchases', $this->payload($supplier, $variant, $location, [
                'quantity' => '2',
                'unit_cost' => '10',
            ]))
            ->assertCreated()
            ->json('data.id');

        $this->resetAuthState();

        $this->asMember($actor)
            ->putJson("/api/v1/purchases/{$purchaseId}", $this->payload($supplier, $variant, $location, [
                'quantity' => '5',
                'unit_cost' => '12.25',
                'document_number' => 'FAC-EDITADA',
            ]))
            ->assertOk()
            ->assertJsonPath('data.total', '61.25')
            ->assertJsonPath('data.document_number', 'FAC-EDITADA')
            ->assertJsonPath('data.items.0.quantity', '5.0000')
            ->assertJsonPath('data.items.0.subtotal', '61.25');

        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_receiving_a_purchase_increments_stock_exactly_once(): void
    {
        $company = Company::factory()->create();
        $supplier = $this->supplier($company);
        $variant = $this->variant($company);
        $location = $this->location($company);
        $actor = $this->member($company, self::PERMISSIONS);

        $purchaseId = $this->asMember($actor)
            ->postJson('/api/v1/purchases', $this->payload($supplier, $variant, $location, [
                'quantity' => '10',
                'unit_cost' => '5',
                'document_number' => 'FAC-001',
            ]))
            ->assertCreated()
            ->json('data.id');

        $this->resetAuthState();

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'receive-1')
            ->postJson("/api/v1/purchases/{$purchaseId}/receive")
            ->assertOk()
            ->assertJsonPath('data.status', 'received')
            ->assertJsonPath('data.items.0.quantity', '10.0000');

        $this->assertDatabaseHas('stock_balances', [
            'company_id' => $company->id,
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'quantity' => '10.0000',
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'company_id' => $company->id,
            'type' => 'entry',
            'reference_type' => 'purchase',
            'reference_id' => $purchaseId,
            'document_reference' => 'FAC-001',
        ]);

        $this->resetAuthState();

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'receive-2')
            ->postJson("/api/v1/purchases/{$purchaseId}/receive")
            ->assertOk();

        $this->assertDatabaseCount('stock_movements', 1);
        $this->assertDatabaseHas('stock_balances', [
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'quantity' => '10.0000',
        ]);
    }

    public function test_a_received_purchase_cannot_be_edited(): void
    {
        $company = Company::factory()->create();
        $supplier = $this->supplier($company);
        $variant = $this->variant($company);
        $location = $this->location($company);
        $actor = $this->member($company, self::PERMISSIONS);

        $purchaseId = $this->asMember($actor)
            ->postJson('/api/v1/purchases', $this->payload($supplier, $variant, $location))
            ->assertCreated()
            ->json('data.id');

        $this->resetAuthState();

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'receive-1')
            ->postJson("/api/v1/purchases/{$purchaseId}/receive")
            ->assertOk();

        $this->resetAuthState();

        $this->asMember($actor)
            ->putJson("/api/v1/purchases/{$purchaseId}", $this->payload($supplier, $variant, $location))
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'PURCHASE_NOT_EDITABLE');
    }

    public function test_receiving_requires_the_idempotency_header_and_permission(): void
    {
        $company = Company::factory()->create();
        $supplier = $this->supplier($company);
        $variant = $this->variant($company);
        $location = $this->location($company);
        $actor = $this->member($company, self::PERMISSIONS);

        $purchaseId = $this->asMember($actor)
            ->postJson('/api/v1/purchases', $this->payload($supplier, $variant, $location))
            ->assertCreated()
            ->json('data.id');

        $this->resetAuthState();

        $this->asMember($actor)
            ->postJson("/api/v1/purchases/{$purchaseId}/receive")
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');

        $this->resetAuthState();

        $viewer = $this->member($company, ['purchases.view']);

        $this->asMember($viewer)
            ->withHeader('Idempotency-Key', 'receive-viewer')
            ->postJson("/api/v1/purchases/{$purchaseId}/receive")
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_an_inactive_supplier_cannot_receive_new_purchases(): void
    {
        $company = Company::factory()->create();
        $supplier = Supplier::factory()->inactive()->create(['company_id' => $company->id]);
        $variant = $this->variant($company);
        $location = $this->location($company);
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/purchases', $this->payload($supplier, $variant, $location))
            ->assertStatus(422)
            ->assertJsonPath('error.details.fields.supplier_id.0', 'El proveedor está inactivo y no admite compras nuevas.');
    }

    public function test_purchase_lines_from_another_company_are_rejected(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $supplier = $this->supplier($company);
        $location = $this->location($company);
        $foreignVariant = $this->variant($otherCompany);
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/purchases', $this->payload($supplier, $foreignVariant, $location))
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');

        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_purchases_are_listed_and_shown_only_for_the_active_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $supplier = $this->supplier($company);
        $variant = $this->variant($company);
        $location = $this->location($company);
        $actor = $this->member($company, self::PERMISSIONS);

        Purchase::factory()->create([
            'company_id' => $otherCompany->id,
            'supplier_id' => Supplier::factory()->create(['company_id' => $otherCompany->id])->id,
            'user_id' => User::factory()->create()->id,
        ]);

        $purchaseId = $this->asMember($actor)
            ->postJson('/api/v1/purchases', $this->payload($supplier, $variant, $location))
            ->assertCreated()
            ->json('data.id');

        $this->resetAuthState();

        $this->asMember($actor)
            ->getJson('/api/v1/purchases')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $purchaseId);

        $this->resetAuthState();

        $this->asMember($actor)
            ->getJson('/api/v1/purchases/999999')
            ->assertStatus(404);
    }

    public function test_creating_a_purchase_requires_the_manage_permission(): void
    {
        $company = Company::factory()->create();
        $supplier = $this->supplier($company);
        $variant = $this->variant($company);
        $location = $this->location($company);
        $actor = $this->member($company, ['purchases.view']);

        $this->asMember($actor)
            ->postJson('/api/v1/purchases', $this->payload($supplier, $variant, $location))
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    private function supplier(Company $company): Supplier
    {
        return Supplier::factory()->create(['company_id' => $company->id]);
    }

    private function variant(Company $company): ProductVariant
    {
        $product = Product::factory()->create(['company_id' => $company->id]);

        return ProductVariant::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
        ]);
    }

    private function location(Company $company): Location
    {
        return Location::factory()->create(['company_id' => $company->id]);
    }

    /**
     * @param  array<string, mixed>  $line
     * @return array<string, mixed>
     */
    private function payload(Supplier $supplier, ProductVariant $variant, Location $location, array $line = []): array
    {
        return [
            'supplier_id' => $supplier->id,
            'ordered_at' => '2026-11-14',
            'document_number' => $line['document_number'] ?? 'FAC-001',
            'notes' => null,
            'items' => [
                [
                    'variant_id' => $variant->id,
                    'location_id' => $location->id,
                    'quantity' => $line['quantity'] ?? '1',
                    'unit_cost' => $line['unit_cost'] ?? '10',
                ],
            ],
        ];
    }
}
