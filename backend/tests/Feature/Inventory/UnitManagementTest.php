<?php

namespace Tests\Feature\Inventory;

use App\Modules\Identity\Models\Company;
use App\Modules\Inventory\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class UnitManagementTest extends TestCase
{
    use InteractsWithTenant, RefreshDatabase;

    /**
     * @var list<string>
     */
    private const MANAGER_PERMISSIONS = ['settings.view', 'settings.manage'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Origin', 'http://localhost:5173');
    }

    public function test_a_unit_can_be_created_in_the_active_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/units', [
                'code' => 'KG',
                'name' => 'Kilogramo',
                'dimension' => 'mass',
                'precision' => 3,
                'company_id' => $otherCompany->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'KG')
            ->assertJsonPath('data.dimension', 'mass')
            ->assertJsonPath('data.precision', 3)
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('units', [
            'company_id' => $company->id,
            'code' => 'KG',
        ]);
        $this->assertDatabaseMissing('units', [
            'company_id' => $otherCompany->id,
            'code' => 'KG',
        ]);
    }

    public function test_a_unit_code_is_unique_per_company(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);
        Unit::factory()->create(['company_id' => $company->id, 'code' => 'KG']);

        $this->asMember($actor)
            ->postJson('/api/v1/units', [
                'code' => 'KG',
                'name' => 'Kilogramo',
                'dimension' => 'mass',
                'precision' => 3,
            ])
            ->assertStatus(422)
            ->assertJsonPath('error.details.fields.code.0', 'Ya existe una unidad con ese código en la empresa.');
    }

    public function test_the_same_unit_code_is_allowed_in_another_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        Unit::factory()->create(['company_id' => $otherCompany->id, 'code' => 'KG']);

        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/units', [
                'code' => 'KG',
                'name' => 'Kilogramo',
                'dimension' => 'mass',
                'precision' => 3,
            ])
            ->assertCreated();
    }

    public function test_unit_precision_must_be_between_zero_and_four(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/units', [
                'code' => 'KG',
                'name' => 'Kilogramo',
                'dimension' => 'mass',
                'precision' => 5,
            ])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_units_are_listed_only_for_the_active_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        Unit::factory()->create(['company_id' => $company->id, 'code' => 'KG', 'dimension' => 'mass']);
        Unit::factory()->create(['company_id' => $company->id, 'code' => 'L', 'dimension' => 'volume']);
        Unit::factory()->create(['company_id' => $otherCompany->id, 'code' => 'AJENA']);

        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->getJson('/api/v1/units')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonMissing(['code' => 'AJENA']);

        $this->resetAuthState();

        $this->asMember($actor)
            ->getJson('/api/v1/units?dimension=mass')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'KG');
    }

    public function test_a_unit_can_be_updated_and_deactivated(): void
    {
        $company = Company::factory()->create();
        $unit = Unit::factory()->create(['company_id' => $company->id, 'code' => 'KG']);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->putJson("/api/v1/units/{$unit->id}", ['name' => 'Kilogramo actualizado'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Kilogramo actualizado');

        $this->resetAuthState();

        $this->asMember($actor)
            ->patchJson("/api/v1/units/{$unit->id}/status", ['status' => 'inactive'])
            ->assertOk()
            ->assertJsonPath('data.status', 'inactive');
    }

    public function test_a_unit_from_another_company_is_not_visible(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $foreignUnit = Unit::factory()->create(['company_id' => $otherCompany->id]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->getJson("/api/v1/units/{$foreignUnit->id}")
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'NOT_FOUND');
    }

    public function test_creating_a_unit_requires_the_manage_permission(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, ['settings.view']);

        $this->asMember($actor)
            ->postJson('/api/v1/units', [
                'code' => 'KG',
                'name' => 'Kilogramo',
                'dimension' => 'mass',
                'precision' => 3,
            ])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }
}
