<?php

namespace Tests\Feature\Catalog;

use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Identity\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class ProductManagementTest extends TestCase
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

    public function test_a_product_can_be_created_with_category_and_brand(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $category = Category::factory()->create(['company_id' => $company->id]);
        $brand = Brand::factory()->create(['company_id' => $company->id]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/products', [
                'name' => 'Café molido',
                'description' => 'Paquete de 500 g',
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'company_id' => $otherCompany->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Café molido')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonPath('data.brand.id', $brand->id);

        $this->assertDatabaseHas('products', [
            'company_id' => $company->id,
            'name' => 'Café molido',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);
        $this->assertDatabaseMissing('products', [
            'company_id' => $otherCompany->id,
            'name' => 'Café molido',
        ]);
    }

    public function test_creating_a_product_requires_a_name(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/products', ['description' => 'Sin nombre'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_a_product_name_is_unique_per_company(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);
        Product::factory()->create(['company_id' => $company->id, 'name' => 'Café molido']);

        $this->asMember($actor)
            ->postJson('/api/v1/products', ['name' => 'Café molido'])
            ->assertStatus(422)
            ->assertJsonPath('error.details.fields.name.0', 'Ya existe un producto con ese nombre en la empresa.');
    }

    public function test_the_same_product_name_is_allowed_in_another_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        Product::factory()->create(['company_id' => $otherCompany->id, 'name' => 'Café molido']);

        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/products', ['name' => 'Café molido'])
            ->assertCreated();
    }

    public function test_products_are_listed_only_for_the_active_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        $category = Category::factory()->create(['company_id' => $company->id]);
        Product::factory()->create([
            'company_id' => $company->id,
            'name' => 'Café molido',
            'category_id' => $category->id,
        ]);
        Product::factory()->create(['company_id' => $company->id, 'name' => 'Té verde']);
        Product::factory()->create(['company_id' => $otherCompany->id, 'name' => 'Ajeno']);

        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->getJson('/api/v1/products')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonMissing(['name' => 'Ajeno']);

        $this->resetAuthState();

        $this->asMember($actor)
            ->getJson("/api/v1/products?category_id={$category->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Café molido');
    }

    public function test_a_product_can_be_updated(): void
    {
        $company = Company::factory()->create();
        $product = Product::factory()->create(['company_id' => $company->id, 'name' => 'Café']);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->putJson("/api/v1/products/{$product->id}", [
                'name' => 'Café tostado',
                'description' => 'Nueva descripción',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Café tostado')
            ->assertJsonPath('data.description', 'Nueva descripción');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Café tostado',
        ]);
    }

    public function test_a_product_can_be_deactivated(): void
    {
        $company = Company::factory()->create();
        $product = Product::factory()->create(['company_id' => $company->id]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->patchJson("/api/v1/products/{$product->id}/status", ['status' => 'inactive'])
            ->assertOk()
            ->assertJsonPath('data.status', 'inactive');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'inactive',
        ]);
    }

    public function test_a_product_from_another_company_is_not_visible(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $foreignProduct = Product::factory()->create(['company_id' => $otherCompany->id]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->getJson("/api/v1/products/{$foreignProduct->id}")
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'NOT_FOUND');
    }

    public function test_a_product_cannot_reference_a_category_from_another_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $foreignCategory = Category::factory()->create(['company_id' => $otherCompany->id]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/products', [
                'name' => 'Producto cruzado',
                'category_id' => $foreignCategory->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_creating_a_product_requires_the_manage_permission(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, ['catalog.view']);

        $this->asMember($actor)
            ->postJson('/api/v1/products', ['name' => 'Sin permiso'])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }
}
