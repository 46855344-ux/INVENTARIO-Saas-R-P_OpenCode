<?php

namespace Tests\Concerns;

use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Models\CompanyUser;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;

trait InteractsWithTenant
{
    /**
     * @param  list<string>  $permissions
     */
    protected function member(Company $company, array $permissions): CompanyUser
    {
        $role = Role::factory()
            ->withPermissions($permissions)
            ->create(['company_id' => $company->id]);

        $user = User::factory()->create();

        return CompanyUser::factory()->create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
            'status' => CompanyUser::STATUS_ACTIVE,
        ]);
    }

    protected function asMember(CompanyUser $membership): static
    {
        return $this->actingAs($membership->user)
            ->withHeader('X-Company-Id', (string) $membership->company_id);
    }
}
