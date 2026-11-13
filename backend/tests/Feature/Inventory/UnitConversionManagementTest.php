<?php

namespace Tests\Feature\Inventory;

use App\Modules\Identity\Models\Company;
use App\Modules\Inventory\Models\Unit;
use App\Modules\Inventory\Models\UnitConversion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class UnitConversionManagementTest extends TestCase
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

    public function test_a_conversion_can_be_created_between_compatible_units(): void
    {
        $company = Company::factory()->create();
        $kg = $this->unit($company, 'KG', 'mass');
        $g = $this->unit($company, 'G', 'mass');
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/conversions', [
                'from_unit_id' => $kg->id,
                'to_unit_id' => $g->id,
                'factor' => '1000',
            ])
            ->assertCreated()
            ->assertJsonPath('data.factor', '1000.000000')
            ->assertJsonPath('data.from_unit.code', 'KG')
            ->assertJsonPath('data.to_unit.code', 'G');

        $this->assertDatabaseHas('unit_conversions', [
            'company_id' => $company->id,
            'from_unit_id' => $kg->id,
            'to_unit_id' => $g->id,
        ]);
    }

    public function test_a_conversion_pair_is_unique_per_company(): void
    {
        $company = Company::factory()->create();
        $kg = $this->unit($company, 'KG', 'mass');
        $g = $this->unit($company, 'G', 'mass');
        UnitConversion::factory()->create([
            'company_id' => $company->id,
            'from_unit_id' => $kg->id,
            'to_unit_id' => $g->id,
            'factor' => '1000',
        ]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/conversions', [
                'from_unit_id' => $kg->id,
                'to_unit_id' => $g->id,
                'factor' => '1000',
            ])
            ->assertStatus(422)
            ->assertJsonPath('error.details.fields.from_unit_id.0', 'Ya existe una conversión entre esas unidades en la empresa.');
    }

    public function test_incompatible_units_are_rejected(): void
    {
        $company = Company::factory()->create();
        $kg = $this->unit($company, 'KG', 'mass');
        $liter = $this->unit($company, 'L', 'volume');
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/conversions', [
                'from_unit_id' => $kg->id,
                'to_unit_id' => $liter->id,
                'factor' => '1',
            ])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'INCOMPATIBLE_UNITS');
    }

    public function test_the_simulator_applies_a_direct_conversion(): void
    {
        $company = Company::factory()->create();
        $kg = $this->unit($company, 'KG', 'mass');
        $g = $this->unit($company, 'G', 'mass');
        UnitConversion::factory()->create([
            'company_id' => $company->id,
            'from_unit_id' => $kg->id,
            'to_unit_id' => $g->id,
            'factor' => '1000',
        ]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/conversions/simulate', [
                'from_unit_id' => $kg->id,
                'to_unit_id' => $g->id,
                'quantity' => '2.5',
            ])
            ->assertOk()
            ->assertJsonPath('data.quantity', '2.5000')
            ->assertJsonPath('data.factor', '1000.000000')
            ->assertJsonPath('data.result', '2500.0000')
            ->assertJsonPath('data.rule', 'direct')
            ->assertJsonPath('data.rounding', 'HALF_UP');
    }

    public function test_the_simulator_applies_the_inverse_conversion(): void
    {
        $company = Company::factory()->create();
        $kg = $this->unit($company, 'KG', 'mass');
        $g = $this->unit($company, 'G', 'mass');
        UnitConversion::factory()->create([
            'company_id' => $company->id,
            'from_unit_id' => $kg->id,
            'to_unit_id' => $g->id,
            'factor' => '1000',
        ]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/conversions/simulate', [
                'from_unit_id' => $g->id,
                'to_unit_id' => $kg->id,
                'quantity' => '500',
            ])
            ->assertOk()
            ->assertJsonPath('data.result', '0.5000')
            ->assertJsonPath('data.rule', 'inverse');
    }

    public function test_the_simulator_rounds_half_up_to_four_decimals(): void
    {
        $company = Company::factory()->create();
        $kg = $this->unit($company, 'KG', 'mass');
        $lb = $this->unit($company, 'LB', 'mass');
        UnitConversion::factory()->create([
            'company_id' => $company->id,
            'from_unit_id' => $kg->id,
            'to_unit_id' => $lb->id,
            'factor' => '2.204622',
        ]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/conversions/simulate', [
                'from_unit_id' => $kg->id,
                'to_unit_id' => $lb->id,
                'quantity' => '3',
            ])
            ->assertOk()
            ->assertJsonPath('data.result', '6.6139');
    }

    public function test_the_simulator_accepts_the_same_unit(): void
    {
        $company = Company::factory()->create();
        $kg = $this->unit($company, 'KG', 'mass');
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/conversions/simulate', [
                'from_unit_id' => $kg->id,
                'to_unit_id' => $kg->id,
                'quantity' => '2.5',
            ])
            ->assertOk()
            ->assertJsonPath('data.factor', '1.000000')
            ->assertJsonPath('data.result', '2.5000')
            ->assertJsonPath('data.rule', 'same_unit');
    }

    public function test_an_undefined_conversion_is_rejected(): void
    {
        $company = Company::factory()->create();
        $kg = $this->unit($company, 'KG', 'mass');
        $g = $this->unit($company, 'G', 'mass');
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/conversions/simulate', [
                'from_unit_id' => $kg->id,
                'to_unit_id' => $g->id,
                'quantity' => '1',
            ])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'CONVERSION_NOT_DEFINED');
    }

    public function test_units_from_another_company_are_not_visible_in_the_simulator(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $foreign = $this->unit($otherCompany, 'KG', 'mass');
        $own = $this->unit($company, 'G', 'mass');
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/conversions/simulate', [
                'from_unit_id' => $foreign->id,
                'to_unit_id' => $own->id,
                'quantity' => '1',
            ])
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'NOT_FOUND');
    }

    public function test_conversions_are_listed_and_deactivated(): void
    {
        $company = Company::factory()->create();
        $kg = $this->unit($company, 'KG', 'mass');
        $g = $this->unit($company, 'G', 'mass');
        $conversion = UnitConversion::factory()->create([
            'company_id' => $company->id,
            'from_unit_id' => $kg->id,
            'to_unit_id' => $g->id,
            'factor' => '1000',
        ]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->getJson('/api/v1/conversions')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.from_unit.code', 'KG');

        $this->resetAuthState();

        $this->asMember($actor)
            ->patchJson("/api/v1/conversions/{$conversion->id}/status", ['status' => 'inactive'])
            ->assertOk()
            ->assertJsonPath('data.status', 'inactive');
    }

    public function test_creating_a_conversion_requires_the_manage_permission(): void
    {
        $company = Company::factory()->create();
        $kg = $this->unit($company, 'KG', 'mass');
        $g = $this->unit($company, 'G', 'mass');
        $actor = $this->member($company, ['settings.view']);

        $this->asMember($actor)
            ->postJson('/api/v1/conversions', [
                'from_unit_id' => $kg->id,
                'to_unit_id' => $g->id,
                'factor' => '1000',
            ])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    private function unit(Company $company, string $code, string $dimension): Unit
    {
        return Unit::factory()->create([
            'company_id' => $company->id,
            'code' => $code,
            'dimension' => $dimension,
        ]);
    }
}
