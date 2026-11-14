<?php

namespace Tests\Feature\Purchasing;

use App\Modules\Identity\Models\Company;
use App\Modules\Purchasing\Models\Purchase;
use App\Modules\Purchasing\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class SupplierManagementTest extends TestCase
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

    public function test_a_supplier_is_created_and_listed_for_the_active_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        Supplier::factory()->create(['company_id' => $otherCompany->id, 'name' => 'Proveedor Ajeno']);
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/suppliers', [
                'name' => 'Distribuidora Central',
                'tax_id' => '30-12345678-9',
                'email' => 'compras@central.test',
                'phone' => '+54 11 5555-1234',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Distribuidora Central')
            ->assertJsonPath('data.status', 'active');

        $this->resetAuthState();

        $this->asMember($actor)
            ->getJson('/api/v1/suppliers')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Distribuidora Central')
            ->assertJsonMissing(['name' => 'Proveedor Ajeno']);
    }

    public function test_supplier_data_is_validated(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/suppliers', [
                'name' => '',
                'email' => 'no-es-un-correo',
            ])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_tax_id_is_unique_per_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        Supplier::factory()->create([
            'company_id' => $company->id,
            'tax_id' => '20-11111111-1',
        ]);
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/suppliers', [
                'name' => 'Duplicado',
                'tax_id' => '20-11111111-1',
            ])
            ->assertStatus(422)
            ->assertJsonPath('error.details.fields.tax_id.0', 'Ya existe un proveedor con ese documento en la empresa.');

        $this->resetAuthState();

        $foreignActor = $this->member($otherCompany, self::PERMISSIONS);

        $this->asMember($foreignActor)
            ->postJson('/api/v1/suppliers', [
                'name' => 'Mismo documento en otra empresa',
                'tax_id' => '20-11111111-1',
            ])
            ->assertCreated();
    }

    public function test_a_supplier_can_be_updated_and_deactivated_while_preserving_purchases(): void
    {
        $company = Company::factory()->create();
        $supplier = Supplier::factory()->create(['company_id' => $company->id]);
        $actor = $this->member($company, self::PERMISSIONS);
        $purchase = Purchase::factory()->create([
            'company_id' => $company->id,
            'supplier_id' => $supplier->id,
            'user_id' => $actor->user_id,
        ]);

        $this->asMember($actor)
            ->putJson("/api/v1/suppliers/{$supplier->id}", ['name' => 'Proveedor Renombrado'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Proveedor Renombrado');

        $this->resetAuthState();

        $this->asMember($actor)
            ->patchJson("/api/v1/suppliers/{$supplier->id}/status", ['status' => 'inactive'])
            ->assertOk()
            ->assertJsonPath('data.status', 'inactive');

        $this->resetAuthState();

        $this->asMember($actor)
            ->getJson("/api/v1/purchases/{$purchase->id}")
            ->assertOk()
            ->assertJsonPath('data.supplier.id', $supplier->id)
            ->assertJsonPath('data.supplier.name', 'Proveedor Renombrado');
    }

    public function test_a_supplier_from_another_company_is_not_visible(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $foreign = Supplier::factory()->create(['company_id' => $otherCompany->id]);
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->getJson("/api/v1/suppliers/{$foreign->id}")
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'NOT_FOUND');

        $this->resetAuthState();

        $this->asMember($actor)
            ->putJson("/api/v1/suppliers/{$foreign->id}", ['name' => 'Intruso'])
            ->assertStatus(404);
    }

    public function test_managing_suppliers_requires_the_manage_permission(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, ['purchases.view']);

        $this->asMember($actor)
            ->postJson('/api/v1/suppliers', ['name' => 'Sin permiso'])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }
}
