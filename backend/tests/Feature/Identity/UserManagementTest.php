<?php

namespace Tests\Feature\Identity;

use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Models\CompanyUser;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Permisos de un administrador de usuarios que además puede ver inventario.
     *
     * @var list<string>
     */
    private const MANAGER_PERMISSIONS = [
        'identity.users.view',
        'identity.users.manage',
        'identity.roles.view',
        'inventory.view',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Origin', 'http://localhost:5173');
    }

    public function test_an_authorized_user_lists_only_users_of_the_active_company(): void
    {
        $ownCompany = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        $actor = $this->member($ownCompany, self::MANAGER_PERMISSIONS);
        $foreign = $this->member($otherCompany, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->getJson('/api/v1/users')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $actor->user_id)
            ->assertJsonPath('data.0.email', $actor->user->email)
            ->assertJsonPath('meta.total', 1);

        $this->assertNotSame($actor->user_id, $foreign->user_id);
    }

    public function test_listing_users_requires_the_company_header(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $this->actingAs($actor->user)
            ->getJson('/api/v1/users')
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN_TENANT');
    }

    public function test_listing_users_requires_permission(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, []);

        $this->asMember($actor)
            ->getJson('/api/v1/users')
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_a_user_from_another_company_is_not_visible(): void
    {
        $ownCompany = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        $actor = $this->member($ownCompany, self::MANAGER_PERMISSIONS);
        $foreign = $this->member($otherCompany, self::MANAGER_PERMISSIONS);

        $this->asMember($actor)
            ->getJson("/api/v1/users/{$foreign->user_id}")
            ->assertStatus(404)
            ->assertJsonPath('error.code', 'NOT_FOUND');
    }

    public function test_a_user_can_be_created_in_the_active_company_with_a_role(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);
        $role = $this->role($company, ['inventory.view']);

        $response = $this->asMember($actor)
            ->postJson('/api/v1/users', [
                'name' => 'Nuevo Usuario',
                'email' => 'nuevo@empresa.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role_id' => $role->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Nuevo Usuario')
            ->assertJsonPath('data.email', 'nuevo@empresa.test')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.role.id', $role->id);

        $this->assertDatabaseHas('users', ['email' => 'nuevo@empresa.test']);
        $this->assertDatabaseHas('company_user', [
            'company_id' => $company->id,
            'user_id' => $response->json('data.id'),
            'role_id' => $role->id,
        ]);
    }

    public function test_creating_a_user_with_a_duplicated_email_is_rejected(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);
        $role = $this->role($company, ['inventory.view']);

        User::factory()->create(['email' => 'repetido@empresa.test']);

        $this->asMember($actor)
            ->postJson('/api/v1/users', [
                'name' => 'Repetido',
                'email' => 'repetido@empresa.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role_id' => $role->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonPath('error.details.fields.email.0', 'Ya existe un usuario con ese correo electrónico.');
    }

    public function test_creating_a_user_requires_the_manage_permission(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, ['identity.users.view']);
        $role = $this->role($company, []);

        $this->asMember($actor)
            ->postJson('/api/v1/users', [
                'name' => 'Sin Permiso',
                'email' => 'sinpermiso@empresa.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role_id' => $role->id,
            ])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_a_role_from_another_company_is_rejected(): void
    {
        $ownCompany = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $actor = $this->member($ownCompany, self::MANAGER_PERMISSIONS);
        $foreignRole = $this->role($otherCompany, []);

        $this->asMember($actor)
            ->postJson('/api/v1/users', [
                'name' => 'Rol Ajeno',
                'email' => 'rolajeno@empresa.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role_id' => $foreignRole->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_an_admin_cannot_assign_a_role_with_more_permissions(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);
        $target = $this->member($company, ['inventory.view']);

        $ownerRole = $this->role($company, [
            'identity.users.view',
            'identity.users.manage',
            'identity.roles.view',
            'identity.roles.manage',
        ]);

        $this->asMember($actor)
            ->putJson("/api/v1/users/{$target->user_id}/role", ['role_id' => $ownerRole->id])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');

        $this->assertDatabaseHas('company_user', [
            'id' => $target->id,
            'role_id' => $target->role_id,
        ]);
    }

    public function test_a_user_can_be_updated(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);
        $target = $this->member($company, ['inventory.view']);

        $this->asMember($actor)
            ->putJson("/api/v1/users/{$target->user_id}", [
                'name' => 'Nombre Actualizado',
                'email' => 'actualizado@empresa.test',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Nombre Actualizado')
            ->assertJsonPath('data.email', 'actualizado@empresa.test');

        $this->assertDatabaseHas('users', [
            'id' => $target->user_id,
            'email' => 'actualizado@empresa.test',
        ]);
    }

    public function test_a_membership_can_be_deactivated_and_reactivated(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);
        $target = $this->member($company, ['inventory.view']);

        $this->asMember($actor)
            ->patchJson("/api/v1/users/{$target->user_id}/status", ['status' => 'inactive'])
            ->assertOk()
            ->assertJsonPath('data.status', 'inactive');

        $this->assertDatabaseHas('company_user', [
            'id' => $target->id,
            'status' => 'inactive',
        ]);

        $this->resetAuthState();

        $this->asMember($actor)
            ->patchJson("/api/v1/users/{$target->user_id}/status", ['status' => 'active'])
            ->assertOk()
            ->assertJsonPath('data.status', 'active');
    }

    public function test_a_role_can_be_assigned_within_the_company(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);
        $target = $this->member($company, ['inventory.view']);
        $newRole = $this->role($company, ['inventory.view']);

        $this->asMember($actor)
            ->putJson("/api/v1/users/{$target->user_id}/role", ['role_id' => $newRole->id])
            ->assertOk()
            ->assertJsonPath('data.role.id', $newRole->id);

        $this->assertDatabaseHas('company_user', [
            'id' => $target->id,
            'role_id' => $newRole->id,
        ]);
    }

    public function test_the_roles_catalog_lists_global_and_company_roles(): void
    {
        $ownCompany = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $actor = $this->member($ownCompany, self::MANAGER_PERMISSIONS);

        Role::factory()->create(['company_id' => null, 'code' => 'global-role']);
        Role::factory()->create(['company_id' => $ownCompany->id, 'code' => 'own-role']);
        Role::factory()->create(['company_id' => $otherCompany->id, 'code' => 'foreign-role']);

        $response = $this->asMember($actor)
            ->getJson('/api/v1/roles?per_page=100')
            ->assertOk();

        $codes = collect($response->json('data'))->pluck('code');

        $this->assertTrue($codes->contains('global-role'));
        $this->assertTrue($codes->contains('own-role'));
        $this->assertFalse($codes->contains('foreign-role'));
    }

    public function test_the_permissions_catalog_requires_the_roles_view_permission(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, ['identity.users.view']);

        $this->asMember($actor)
            ->getJson('/api/v1/permissions')
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_the_permissions_catalog_is_available_with_the_roles_view_permission(): void
    {
        $company = Company::factory()->create();
        $actor = $this->member($company, self::MANAGER_PERMISSIONS);

        $response = $this->asMember($actor)
            ->getJson('/api/v1/permissions?per_page=100')
            ->assertOk();

        $codes = collect($response->json('data'))->pluck('code');

        $this->assertTrue($codes->contains('identity.users.view'));
        $this->assertTrue($codes->contains('identity.roles.view'));
    }

    public function test_an_operator_cannot_list_users(): void
    {
        $company = Company::factory()->create();
        $operator = $this->member($company, ['inventory.view', 'inventory.move']);

        $this->asMember($operator)
            ->getJson('/api/v1/users')
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'FORBIDDEN');
    }

    public function test_the_permission_gate_enforces_the_costs_permission(): void
    {
        Route::middleware(['auth:sanctum', 'tenant'])
            ->get('/api/v1/__test/costs', function () {
                return response()->json([
                    'allowed' => Gate::allows('metrics.costs.view'),
                ]);
            });

        $company = Company::factory()->create();

        $allowed = $this->member($company, ['metrics.costs.view']);
        $this->asMember($allowed)
            ->getJson('/api/v1/__test/costs')
            ->assertOk()
            ->assertJsonPath('allowed', true);

        $this->resetAuthState();

        $denied = $this->member($company, ['inventory.view']);
        $this->asMember($denied)
            ->getJson('/api/v1/__test/costs')
            ->assertOk()
            ->assertJsonPath('allowed', false);
    }

    private function member(Company $company, array $permissions): CompanyUser
    {
        $role = $this->role($company, $permissions);
        $user = User::factory()->create();

        return CompanyUser::factory()->create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
            'status' => CompanyUser::STATUS_ACTIVE,
        ]);
    }

    /**
     * @param  list<string>  $permissions
     */
    private function role(Company $company, array $permissions): Role
    {
        return Role::factory()
            ->withPermissions($permissions)
            ->create(['company_id' => $company->id]);
    }

    private function asMember(CompanyUser $membership): static
    {
        return $this->actingAs($membership->user)
            ->withHeader('X-Company-Id', (string) $membership->company_id);
    }
}
