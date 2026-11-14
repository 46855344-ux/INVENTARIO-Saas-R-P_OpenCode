<?php

namespace Tests\Feature\Inventory;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\Company;
use App\Modules\Inventory\Models\Location;
use App\Modules\Inventory\Models\StockBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class InventoryStockTest extends TestCase
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

    public function test_stock_is_listed_only_for_the_active_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        $own = $this->balance($company, 'SKU-OWN', 'ALM-01', '5.0000');
        $this->balance($otherCompany, 'SKU-OTHER', 'ALM-02', '9.0000');

        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->getJson('/api/v1/inventory/stocks')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.variant.sku', 'SKU-OWN')
            ->assertJsonPath('data.0.quantity', '5.0000')
            ->assertJsonPath('data.0.location.code', 'ALM-01')
            ->assertJsonMissing(['sku' => 'SKU-OTHER']);

        $this->assertSame((int) $company->id, (int) $own->company_id);
    }

    public function test_stock_can_be_filtered_by_variant_and_location(): void
    {
        $company = Company::factory()->create();
        $variant = $this->variant($company, 'SKU-A');
        $location = $this->location($company, 'ALM-01');
        $otherLocation = $this->location($company, 'ALM-02');

        StockBalance::factory()->create([
            'company_id' => $company->id,
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'quantity' => '3.0000',
        ]);
        StockBalance::factory()->create([
            'company_id' => $company->id,
            'variant_id' => $variant->id,
            'location_id' => $otherLocation->id,
            'quantity' => '7.0000',
        ]);

        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->getJson("/api/v1/inventory/stocks?location_id={$location->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.quantity', '3.0000');

        $this->resetAuthState();

        $this->asMember($actor)
            ->getJson('/api/v1/inventory/stocks?search=SKU-A')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_listing_stock_requires_the_view_permission(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, []);

        $this->asMember($actor)
            ->getJson('/api/v1/inventory/stocks')
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    private function balance(Company $company, string $sku, string $locationCode, string $quantity): StockBalance
    {
        $variant = $this->variant($company, $sku);
        $location = $this->location($company, $locationCode);

        return StockBalance::factory()->create([
            'company_id' => $company->id,
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'quantity' => $quantity,
        ]);
    }

    private function variant(Company $company, string $sku): ProductVariant
    {
        $product = Product::factory()->create(['company_id' => $company->id]);

        return ProductVariant::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'sku' => $sku,
        ]);
    }

    private function location(Company $company, string $code): Location
    {
        return Location::factory()->create([
            'company_id' => $company->id,
            'code' => $code,
        ]);
    }
}
