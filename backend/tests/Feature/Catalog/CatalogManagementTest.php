<?php

namespace Tests\Feature\Catalog;

use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Identity\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class CatalogManagementTest extends TestCase
{
    use InteractsWithTenant, RefreshDatabase;

    /**
     * @var list<string>
     */
    private const MANAGER_PERMISSIONS = ['catalog.view', 'catalog.manage'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Origin', 'http://localhost:5173');
    }

    public function test_a_category_can_be_created_in_the_active_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/categories', [
                'name' => 'Bebidas',
                'company_id' => $otherCompany->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Bebidas')
            ->assertJsonPath('data.status', 'active');

        $this->assertDatabaseHas('categories', [
            'company_id' => $company->id,
            'name' => 'Bebidas',
        ]);
        $this->assertDatabaseMissing('categories', [
            'company_id' => $otherCompany->id,
            'name' => 'Bebidas',
        ]);
    }

    public function test_a_category_name_is_unique_per_company(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);
        Category::factory()->create(['company_id' => $company->id, 'name' => 'Bebidas']);

        $this->asMember($actor)
            ->postJson('/api/v1/categories', ['name' => 'Bebidas'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonPath('error.details.fields.name.0', 'Ya existe una categoría con ese nombre en la empresa.');
    }

    public function test_the_same_category_name_is_allowed_in_another_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        Category::factory()->create(['company_id' => $otherCompany->id, 'name' => 'Bebidas']);

        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/categories', ['name' => 'Bebidas'])
            ->assertCreated();
    }

    public function test_categories_are_listed_only_for_the_active_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        Category::factory()->create(['company_id' => $company->id, 'name' => 'Bebidas']);
        Category::factory()->create(['company_id' => $company->id, 'name' => 'Almacén']);
        Category::factory()->create(['company_id' => $otherCompany->id, 'name' => 'Ajenas']);

        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->getJson('/api/v1/categories')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonMissing(['name' => 'Ajenas']);
    }

    public function test_a_category_can_be_updated(): void
    {
        $company = Company::factory()->create();
        $category = Category::factory()->create(['company_id' => $company->id, 'name' => 'Bebidas']);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->putJson("/api/v1/categories/{$category->id}", ['name' => 'Bebidas sin alcohol'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Bebidas sin alcohol');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Bebidas sin alcohol',
        ]);
    }

    public function test_a_category_can_be_deactivated(): void
    {
        $company = Company::factory()->create();
        $category = Category::factory()->create(['company_id' => $company->id]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->patchJson("/api/v1/categories/{$category->id}/status", ['status' => 'inactive'])
            ->assertOk()
            ->assertJsonPath('data.status', 'inactive');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'status' => 'inactive',
        ]);
    }

    public function test_a_category_from_another_company_is_not_visible(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $foreignCategory = Category::factory()->create(['company_id' => $otherCompany->id]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->getJson("/api/v1/categories/{$foreignCategory->id}")
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'NOT_FOUND');
    }

    public function test_creating_a_category_requires_the_manage_permission(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, ['catalog.view']);

        $this->asMember($actor)
            ->postJson('/api/v1/categories', ['name' => 'Sin permiso'])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_a_brand_can_be_created_and_listed(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/brands', ['name' => 'Marca Demo'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Marca Demo');

        $this->resetAuthState();

        $this->asMember($actor)
            ->getJson('/api/v1/brands')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Marca Demo');
    }

    public function test_a_brand_can_be_updated_and_deactivated(): void
    {
        $company = Company::factory()->create();
        $brand = Brand::factory()->create(['company_id' => $company->id, 'name' => 'Marca']);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->putJson("/api/v1/brands/{$brand->id}", ['name' => 'Marca Renombrada'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Marca Renombrada');

        $this->resetAuthState();

        $this->asMember($actor)
            ->patchJson("/api/v1/brands/{$brand->id}/status", ['status' => 'inactive'])
            ->assertOk()
            ->assertJsonPath('data.status', 'inactive');
    }

    public function test_a_brand_from_another_company_is_not_visible(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $foreignBrand = Brand::factory()->create(['company_id' => $otherCompany->id]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->getJson("/api/v1/brands/{$foreignBrand->id}")
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'NOT_FOUND');
    }

    public function test_a_brand_name_is_unique_per_company(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);
        Brand::factory()->create(['company_id' => $company->id, 'name' => 'Marca']);

        $this->asMember($actor)
            ->postJson('/api/v1/brands', ['name' => 'Marca'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }
}
