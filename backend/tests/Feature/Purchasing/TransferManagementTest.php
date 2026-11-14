<?php

namespace Tests\Feature\Purchasing;

use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Models\User;
use App\Modules\Inventory\Models\Location;
use App\Modules\Inventory\Models\StockBalance;
use App\Modules\Purchasing\Models\Transfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class TransferManagementTest extends TestCase
{
    use InteractsWithTenant, RefreshDatabase;

    /**
     * @var list<string>
     */
    private const PERMISSIONS = ['transfers.view', 'transfers.manage'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Origin', 'http://localhost:5173');
    }

    public function test_a_transfer_moves_stock_atomically_between_locations(): void
    {
        $company = Company::factory()->create();
        $variant = $this->variant($company);
        $origin = $this->location($company, 'ALM-01');
        $destination = $this->location($company, 'ALM-02');
        $this->balance($company, $variant, $origin, '10.0000');
        $actor = $this->member($company, self::PERMISSIONS);

        $response = $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'transfer-1')
            ->postJson('/api/v1/transfers', $this->payload($origin, $destination, $variant, '3'))
            ->assertCreated()
            ->assertJsonPath('data.status', 'confirmed')
            ->assertJsonPath('data.from_location.code', 'ALM-01')
            ->assertJsonPath('data.to_location.code', 'ALM-02')
            ->assertJsonPath('data.items.0.quantity', '3.0000');

        $transferId = $response->json('data.id');

        $this->assertDatabaseHas('stock_balances', [
            'variant_id' => $variant->id,
            'location_id' => $origin->id,
            'quantity' => '7.0000',
        ]);
        $this->assertDatabaseHas('stock_balances', [
            'variant_id' => $variant->id,
            'location_id' => $destination->id,
            'quantity' => '3.0000',
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'company_id' => $company->id,
            'type' => 'transfer_out',
            'reference_type' => 'transfer',
            'reference_id' => $transferId,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'company_id' => $company->id,
            'type' => 'transfer_in',
            'reference_type' => 'transfer',
            'reference_id' => $transferId,
        ]);

        $total = (float) StockBalance::query()
            ->where('company_id', $company->id)
            ->where('variant_id', $variant->id)
            ->sum('quantity');

        $this->assertEqualsWithDelta(10.0, $total, 0.0001);
    }

    public function test_a_transfer_cannot_exceed_the_origin_balance(): void
    {
        $company = Company::factory()->create();
        $variant = $this->variant($company);
        $origin = $this->location($company, 'ALM-01');
        $destination = $this->location($company, 'ALM-02');
        $this->balance($company, $variant, $origin, '2.0000');
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'transfer-1')
            ->postJson('/api/v1/transfers', $this->payload($origin, $destination, $variant, '3'))
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'INSUFFICIENT_STOCK');

        $this->assertDatabaseCount('transfers', 0);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseHas('stock_balances', [
            'variant_id' => $variant->id,
            'location_id' => $origin->id,
            'quantity' => '2.0000',
        ]);
    }

    public function test_a_failed_line_rolls_back_the_whole_transfer(): void
    {
        $company = Company::factory()->create();
        $first = $this->variant($company);
        $second = $this->variant($company);
        $origin = $this->location($company, 'ALM-01');
        $destination = $this->location($company, 'ALM-02');
        $this->balance($company, $first, $origin, '5.0000');
        $this->balance($company, $second, $origin, '1.0000');
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'transfer-rollback')
            ->postJson('/api/v1/transfers', [
                'from_location_id' => $origin->id,
                'to_location_id' => $destination->id,
                'items' => [
                    ['variant_id' => $first->id, 'quantity' => '2'],
                    ['variant_id' => $second->id, 'quantity' => '4'],
                ],
            ])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'INSUFFICIENT_STOCK');

        $this->assertDatabaseCount('transfers', 0);
        $this->assertDatabaseCount('transfer_items', 0);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseHas('stock_balances', [
            'variant_id' => $first->id,
            'location_id' => $origin->id,
            'quantity' => '5.0000',
        ]);
        $this->assertDatabaseHas('stock_balances', [
            'variant_id' => $second->id,
            'location_id' => $origin->id,
            'quantity' => '1.0000',
        ]);
    }

    public function test_origin_and_destination_must_be_different(): void
    {
        $company = Company::factory()->create();
        $variant = $this->variant($company);
        $origin = $this->location($company, 'ALM-01');
        $this->balance($company, $variant, $origin, '5.0000');
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'transfer-same')
            ->postJson('/api/v1/transfers', $this->payload($origin, $origin, $variant, '1'))
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_locations_and_variants_from_another_company_are_rejected(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $variant = $this->variant($company);
        $origin = $this->location($company, 'ALM-01');
        $foreign = $this->location($otherCompany, 'ALM-EX');
        $this->balance($company, $variant, $origin, '5.0000');
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'transfer-foreign')
            ->postJson('/api/v1/transfers', $this->payload($origin, $foreign, $variant, '1'))
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');

        $this->assertDatabaseCount('transfers', 0);
    }

    public function test_repeating_the_request_with_the_same_key_does_not_move_stock_twice(): void
    {
        $company = Company::factory()->create();
        $variant = $this->variant($company);
        $origin = $this->location($company, 'ALM-01');
        $destination = $this->location($company, 'ALM-02');
        $this->balance($company, $variant, $origin, '10.0000');
        $actor = $this->member($company, self::PERMISSIONS);
        $payload = $this->payload($origin, $destination, $variant, '3');

        $firstId = $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'transfer-1')
            ->postJson('/api/v1/transfers', $payload)
            ->assertCreated()
            ->json('data.id');

        $this->resetAuthState();

        $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'transfer-1')
            ->postJson('/api/v1/transfers', $payload)
            ->assertOk()
            ->assertJsonPath('data.id', $firstId);

        $this->assertDatabaseCount('transfers', 1);
        $this->assertDatabaseCount('stock_movements', 2);
        $this->assertDatabaseHas('stock_balances', [
            'variant_id' => $variant->id,
            'location_id' => $origin->id,
            'quantity' => '7.0000',
        ]);
        $this->assertDatabaseHas('stock_balances', [
            'variant_id' => $variant->id,
            'location_id' => $destination->id,
            'quantity' => '3.0000',
        ]);
    }

    public function test_a_transfer_requires_the_idempotency_header_and_permission(): void
    {
        $company = Company::factory()->create();
        $variant = $this->variant($company);
        $origin = $this->location($company, 'ALM-01');
        $destination = $this->location($company, 'ALM-02');
        $this->balance($company, $variant, $origin, '5.0000');
        $actor = $this->member($company, self::PERMISSIONS);

        $this->asMember($actor)
            ->postJson('/api/v1/transfers', $this->payload($origin, $destination, $variant, '1'))
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');

        $this->resetAuthState();

        $viewer = $this->member($company, ['transfers.view']);

        $this->asMember($viewer)
            ->withHeader('Idempotency-Key', 'transfer-viewer')
            ->postJson('/api/v1/transfers', $this->payload($origin, $destination, $variant, '1'))
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_transfer_history_is_scoped_to_the_active_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $variant = $this->variant($company);
        $origin = $this->location($company, 'ALM-01');
        $destination = $this->location($company, 'ALM-02');
        $this->balance($company, $variant, $origin, '5.0000');
        $actor = $this->member($company, self::PERMISSIONS);

        Transfer::factory()->create([
            'company_id' => $otherCompany->id,
            'from_location_id' => $this->location($otherCompany, 'EX-01')->id,
            'to_location_id' => $this->location($otherCompany, 'EX-02')->id,
            'user_id' => User::factory()->create()->id,
        ]);

        $transferId = $this->asMember($actor)
            ->withHeader('Idempotency-Key', 'transfer-1')
            ->postJson('/api/v1/transfers', $this->payload($origin, $destination, $variant, '1'))
            ->assertCreated()
            ->json('data.id');

        $this->resetAuthState();

        $this->asMember($actor)
            ->getJson('/api/v1/transfers')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $transferId);

        $this->resetAuthState();

        $this->asMember($actor)
            ->getJson('/api/v1/transfers/999999')
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'NOT_FOUND');
    }

    private function variant(Company $company): ProductVariant
    {
        $product = Product::factory()->create(['company_id' => $company->id]);

        return ProductVariant::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
        ]);
    }

    private function location(Company $company, string $code): Location
    {
        return Location::factory()->create([
            'company_id' => $company->id,
            'code' => $code,
        ]);
    }

    private function balance(Company $company, ProductVariant $variant, Location $location, string $quantity): StockBalance
    {
        return StockBalance::factory()->create([
            'company_id' => $company->id,
            'variant_id' => $variant->id,
            'location_id' => $location->id,
            'quantity' => $quantity,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Location $from, Location $to, ProductVariant $variant, string $quantity): array
    {
        return [
            'from_location_id' => $from->id,
            'to_location_id' => $to->id,
            'items' => [
                ['variant_id' => $variant->id, 'quantity' => $quantity],
            ],
        ];
    }
}
