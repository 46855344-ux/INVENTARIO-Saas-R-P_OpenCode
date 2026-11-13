<?php

namespace Tests\Feature\Auth;

use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Simula el origen de la SPA para que Sanctum trate la petición como
     * stateful (cookies + sesión), como en el flujo real.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeader('Origin', 'http://localhost:5173');
    }

    public function test_a_user_can_log_in_with_valid_credentials(): void
    {
        $company = Company::factory()->create(['name' => 'Empresa Demo']);
        $user = User::factory()->create(['email' => 'admin@empresa.test']);
        $user->companies()->attach($company, ['status' => 'active']);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@empresa.test',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('data.user.email', 'admin@empresa.test')
            ->assertJsonPath('data.companies.0.id', $company->id)
            ->assertJsonPath('data.companies.0.name', 'Empresa Demo');

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_lists_only_active_companies(): void
    {
        $active = Company::factory()->create(['name' => 'Activa']);
        $inactive = Company::factory()->create(['name' => 'Inactiva']);

        $user = User::factory()->create();
        $user->companies()->attach($active, ['status' => 'active']);
        $user->companies()->attach($inactive, ['status' => 'inactive']);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data.companies')
            ->assertJsonPath('data.companies.0.name', 'Activa');
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'INVALID_CREDENTIALS');

        $this->assertGuest();
    }

    public function test_inactive_users_cannot_log_in(): void
    {
        $user = User::factory()->inactive()->create();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertStatus(403)
            ->assertJsonPath('error.code', 'ACCOUNT_INACTIVE');

        $this->assertGuest();
    }

    public function test_login_validation_errors_use_the_api_error_contract(): void
    {
        $this->postJson('/api/v1/auth/login', ['email' => 'not-an-email'])
            ->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'error' => [
                    'code',
                    'message',
                    'details' => ['fields' => ['email', 'password']],
                    'trace_id',
                ],
            ]);
    }

    public function test_an_authenticated_user_can_fetch_their_profile(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create();
        $user->companies()->attach($company, ['status' => 'active']);

        $this->actingAs($user)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonCount(1, 'data.companies');
    }

    public function test_protected_routes_reject_unauthenticated_requests(): void
    {
        $this->getJson('/api/v1/auth/me')
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    public function test_a_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $this->resetAuthState();

        $this->assertAuthenticated('web');

        $this->postJson('/api/v1/auth/logout')
            ->assertNoContent();

        $this->resetAuthState();

        $this->assertGuest('web');
    }

    public function test_a_logged_out_user_cannot_access_protected_routes(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $this->resetAuthState();

        $this->postJson('/api/v1/auth/logout')
            ->assertNoContent();

        $this->resetAuthState();

        $this->getJson('/api/v1/auth/me')
            ->assertStatus(401)
            ->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }
}
