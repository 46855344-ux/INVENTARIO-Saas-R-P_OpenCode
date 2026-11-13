<?php

namespace Tests\Feature\Tenancy;

use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Models\CompanyUser;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\TenantContext;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\Fixtures\TenantItem;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('tenant_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function test_the_tenant_scope_only_returns_records_of_the_active_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        TenantItem::create(['company_id' => $companyA->id, 'name' => 'A-1']);
        TenantItem::create(['company_id' => $companyA->id, 'name' => 'A-2']);
        TenantItem::create(['company_id' => $companyB->id, 'name' => 'B-1']);

        $this->withinTenant($companyA);

        $items = TenantItem::query()->orderBy('name')->get();

        $this->assertCount(2, $items);
        $this->assertEqualsCanonicalizing(['A-1', 'A-2'], $items->pluck('name')->all());
        $this->assertTrue($items->every(
            fn (TenantItem $item): bool => (int) $item->company_id === (int) $companyA->id,
        ));
        $this->assertFalse(
            TenantItem::query()->where('company_id', $companyB->id)->exists(),
        );
        $this->assertSame(2, TenantItem::query()->count());
    }

    public function test_creating_a_record_ignores_the_payload_company_id(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $this->withinTenant($companyA);

        $item = TenantItem::create([
            'company_id' => $companyB->id,
            'name' => 'Creado para A',
        ]);

        $this->assertSame((int) $companyA->id, (int) $item->company_id);
        $this->assertDatabaseHas('tenant_items', [
            'id' => $item->id,
            'company_id' => $companyA->id,
        ]);
    }

    public function test_a_record_cannot_be_moved_to_another_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $item = TenantItem::create(['company_id' => $companyA->id, 'name' => 'Original']);

        $this->withinTenant($companyA);

        $item = TenantItem::query()->findOrFail($item->id);
        $item->update([
            'company_id' => $companyB->id,
            'name' => 'Renombrado',
        ]);

        $fresh = $item->fresh();

        $this->assertSame((int) $companyA->id, (int) $fresh->company_id);
        $this->assertSame('Renombrado', $fresh->name);
    }

    public function test_records_of_the_same_company_are_detected(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $itemA = TenantItem::create(['company_id' => $companyA->id, 'name' => 'A']);
        $itemA2 = TenantItem::create(['company_id' => $companyA->id, 'name' => 'A-2']);
        $itemB = TenantItem::create(['company_id' => $companyB->id, 'name' => 'B']);

        $this->assertTrue($itemA->belongsToSameCompany($itemA2));
        $this->assertFalse($itemA->belongsToSameCompany($itemB));
    }

    private function withinTenant(Company $company): void
    {
        $user = User::factory()->create();

        $membership = CompanyUser::factory()->create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role_id' => null,
        ]);

        $request = Request::create('/api/v1/tenant-items');
        $request->attributes->set(TenantContext::class, new TenantContext(
            $company,
            $membership,
            null,
            [],
        ));

        $this->app->instance('request', $request);
    }
}
