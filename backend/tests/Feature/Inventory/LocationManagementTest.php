<?php

namespace Tests\Feature\Inventory;

use App\Modules\Identity\Models\Company;
use App\Modules\Inventory\Models\Location;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class LocationManagementTest extends TestCase
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

    public function test_a_location_can_be_created_in_the_active_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/locations', [
                'code' => 'ALM-01',
                'name' => 'Almacén central',
                'type' => 'warehouse',
                'company_id' => $otherCompany->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'ALM-01')
            ->assertJsonPath('data.type', 'warehouse')
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('locations', [
            'company_id' => $company->id,
            'code' => 'ALM-01',
        ]);
    }

    public function test_a_location_code_is_unique_per_company(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);
        Location::factory()->create(['company_id' => $company->id, 'code' => 'ALM-01']);

        $this->asMember($actor)
            ->postJson('/api/v1/locations', [
                'code' => 'ALM-01',
                'name' => 'Otro',
                'type' => 'warehouse',
            ])
            ->assertStatus(422)
            ->assertJsonPath('error.details.fields.code.0', 'Ya existe una ubicación con ese código en la empresa.');
    }

    public function test_a_location_can_have_a_parent_of_the_same_company(): void
    {
        $company = Company::factory()->create();
        $parent = Location::factory()->create(['company_id' => $company->id, 'code' => 'ALM-01']);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/locations', [
                'code' => 'EST-01',
                'name' => 'Estante 1',
                'type' => 'shelf',
                'parent_id' => $parent->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.parent_id', $parent->id)
            ->assertJsonPath('data.parent.code', 'ALM-01');
    }

    public function test_a_parent_from_another_company_is_rejected(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $foreignParent = Location::factory()->create(['company_id' => $otherCompany->id]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/locations', [
                'code' => 'EST-01',
                'name' => 'Estante 1',
                'type' => 'shelf',
                'parent_id' => $foreignParent->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_a_location_can_be_updated_and_deactivated_without_stock(): void
    {
        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->putJson("/api/v1/locations/{$location->id}", ['name' => 'Almacén renombrado'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Almacén renombrado');

        $this->resetAuthState();

        $this->asMember($actor)
            ->patchJson("/api/v1/locations/{$location->id}/status", ['status' => 'inactive'])
            ->assertOk()
            ->assertJsonPath('data.status', 'inactive');
    }

    public function test_a_location_with_stock_cannot_be_deactivated(): void
    {
        $company = Company::factory()->create();
        $location = Location::factory()->create(['company_id' => $company->id]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->decimal('quantity', 15, 4);
        });
        DB::table('stock_balances')->insert([
            'location_id' => $location->id,
            'quantity' => '1.0000',
        ]);

        $this->asMember($actor)
            ->patchJson("/api/v1/locations/{$location->id}/status", ['status' => 'inactive'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'status' => 'active',
        ]);
    }

    public function test_a_location_from_another_company_is_not_visible(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $foreignLocation = Location::factory()->create(['company_id' => $otherCompany->id]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->getJson("/api/v1/locations/{$foreignLocation->id}")
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'NOT_FOUND');
    }

    public function test_locations_are_listed_only_for_the_active_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        Location::factory()->create(['company_id' => $company->id, 'code' => 'ALM-01', 'type' => 'warehouse']);
        Location::factory()->create(['company_id' => $company->id, 'code' => 'EST-01', 'type' => 'shelf']);
        Location::factory()->create(['company_id' => $otherCompany->id, 'code' => 'AJENA']);

        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->getJson('/api/v1/locations')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonMissing(['code' => 'AJENA']);

        $this->resetAuthState();

        $this->asMember($actor)
            ->getJson('/api/v1/locations?type=shelf')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'EST-01');
    }

    public function test_creating_a_location_requires_the_manage_permission(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, ['settings.view']);

        $this->asMember($actor)
            ->postJson('/api/v1/locations', [
                'code' => 'ALM-01',
                'name' => 'Almacén',
                'type' => 'warehouse',
            ])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }
}
