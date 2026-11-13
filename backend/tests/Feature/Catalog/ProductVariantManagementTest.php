<?php

namespace Tests\Feature\Catalog;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class ProductVariantManagementTest extends TestCase
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

    public function test_a_variant_can_be_created_for_a_product_of_the_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $product = Product::factory()->create(['company_id' => $company->id]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson("/api/v1/products/{$product->id}/variants", [
                'name' => 'Talle M',
                'sku' => 'SKU-001',
                'barcode' => '779000000001',
                'cost' => '10.5000',
                'price' => '15.0000',
                'company_id' => $otherCompany->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Talle M')
            ->assertJsonPath('data.sku', 'SKU-001')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.cost', '10.5000')
            ->assertJsonPath('data.price', '15.0000');

        $this->assertDatabaseHas('product_variants', [
            'company_id' => $company->id,
            'product_id' => $product->id,
            'sku' => 'SKU-001',
        ]);
        $this->assertDatabaseMissing('product_variants', [
            'company_id' => $otherCompany->id,
            'sku' => 'SKU-001',
        ]);
    }

    public function test_a_variant_sku_is_unique_per_company(): void
    {
        $company = Company::factory()->create();
        $product = Product::factory()->create(['company_id' => $company->id]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        ProductVariant::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'sku' => 'SKU-001',
        ]);

        $this->asMember($actor)
            ->postJson("/api/v1/products/{$product->id}/variants", [
                'name' => 'Otra',
                'sku' => 'SKU-001',
            ])
            ->assertStatus(422)
            ->assertJsonPath('error.details.fields.sku.0', 'Ya existe una variante con ese SKU en la empresa.');
    }

    public function test_a_variant_barcode_is_unique_per_company(): void
    {
        $company = Company::factory()->create();
        $product = Product::factory()->create(['company_id' => $company->id]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        ProductVariant::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'sku' => 'SKU-001',
            'barcode' => 'BAR-001',
        ]);

        $this->asMember($actor)
            ->postJson("/api/v1/products/{$product->id}/variants", [
                'name' => 'Otra',
                'sku' => 'SKU-002',
                'barcode' => 'BAR-001',
            ])
            ->assertStatus(422)
            ->assertJsonPath('error.details.fields.barcode.0', 'Ya existe una variante con ese código de barras en la empresa.');
    }

    public function test_the_same_sku_is_allowed_in_another_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $otherProduct = Product::factory()->create(['company_id' => $otherCompany->id]);
        ProductVariant::factory()->create([
            'company_id' => $otherCompany->id,
            'product_id' => $otherProduct->id,
            'sku' => 'SKU-001',
        ]);

        $product = Product::factory()->create(['company_id' => $company->id]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson("/api/v1/products/{$product->id}/variants", [
                'name' => 'Propia',
                'sku' => 'SKU-001',
            ])
            ->assertCreated();
    }

    public function test_variants_are_listed_only_for_the_selected_product_and_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $product = Product::factory()->create(['company_id' => $company->id]);
        $otherProduct = Product::factory()->create(['company_id' => $company->id]);

        ProductVariant::factory()->create(['company_id' => $company->id, 'product_id' => $product->id, 'sku' => 'SKU-A']);
        ProductVariant::factory()->create(['company_id' => $company->id, 'product_id' => $product->id, 'sku' => 'SKU-B']);
        ProductVariant::factory()->create(['company_id' => $company->id, 'product_id' => $otherProduct->id, 'sku' => 'SKU-C']);
        $foreignProduct = Product::factory()->create(['company_id' => $otherCompany->id]);
        ProductVariant::factory()->create(['company_id' => $otherCompany->id, 'product_id' => $foreignProduct->id, 'sku' => 'SKU-D']);

        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->getJson("/api/v1/products/{$product->id}/variants")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonMissing(['sku' => 'SKU-C'])
            ->assertJsonMissing(['sku' => 'SKU-D']);
    }

    public function test_a_variant_can_be_updated(): void
    {
        $company = Company::factory()->create();
        $product = Product::factory()->create(['company_id' => $company->id]);
        $variant = ProductVariant::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'sku' => 'SKU-001',
        ]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->putJson("/api/v1/variants/{$variant->id}", [
                'name' => 'Talle L',
                'cost' => '12.2500',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Talle L')
            ->assertJsonPath('data.cost', '12.2500');

        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'name' => 'Talle L',
        ]);
    }

    public function test_a_variant_can_be_deactivated(): void
    {
        $company = Company::factory()->create();
        $product = Product::factory()->create(['company_id' => $company->id]);
        $variant = ProductVariant::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
        ]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->patchJson("/api/v1/variants/{$variant->id}/status", ['status' => 'inactive'])
            ->assertOk()
            ->assertJsonPath('data.status', 'inactive');

        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'status' => 'inactive',
        ]);
    }

    public function test_a_variant_from_another_company_is_not_visible(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $foreignProduct = Product::factory()->create(['company_id' => $otherCompany->id]);
        $foreignVariant = ProductVariant::factory()->create([
            'company_id' => $otherCompany->id,
            'product_id' => $foreignProduct->id,
        ]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->getJson("/api/v1/variants/{$foreignVariant->id}")
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'NOT_FOUND');
    }

    public function test_a_variant_cannot_be_created_for_a_product_of_another_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $foreignProduct = Product::factory()->create(['company_id' => $otherCompany->id]);
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->postJson("/api/v1/products/{$foreignProduct->id}/variants", [
                'name' => 'Cruzada',
                'sku' => 'SKU-X',
            ])
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'NOT_FOUND');
    }

    public function test_creating_a_variant_requires_the_manage_permission(): void
    {
        $company = Company::factory()->create();
        $product = Product::factory()->create(['company_id' => $company->id]);
        $actor = $this->member($company, ['catalog.view']);

        $this->asMember($actor)
            ->postJson("/api/v1/products/{$product->id}/variants", [
                'name' => 'Sin permiso',
                'sku' => 'SKU-001',
            ])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }
}
