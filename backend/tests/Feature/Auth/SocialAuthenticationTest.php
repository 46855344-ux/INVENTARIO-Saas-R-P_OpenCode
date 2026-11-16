<?php

namespace Tests\Feature\Auth;

use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Models\SocialIdentity;
use App\Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\Concerns\InteractsWithTenant;
use Tests\TestCase;

class SocialAuthenticationTest extends TestCase
{
    use InteractsWithTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.frontend_url' => 'http://localhost:5173',
            'services.google.client_id' => 'test-google-id',
            'services.google.client_secret' => 'test-google-secret',
            'services.facebook.client_id' => null,
            'services.facebook.client_secret' => null,
        ]);

        $this->withHeader('Origin', 'http://localhost:5173');
    }

    public function test_google_callback_logs_in_a_linked_user_and_preserves_membership(): void
    {
        $company = Company::factory()->create();
        $membership = $this->member($company, ['inventory.view']);
        SocialIdentity::factory()->create([
            'user_id' => $membership->user_id,
            'provider' => SocialIdentity::PROVIDER_GOOGLE,
            'provider_user_id' => 'google-123',
        ]);
        $this->fakeProvider('google', 'google-123');

        $this->get('/auth/social/google/callback')
            ->assertRedirect('http://localhost:5173/');

        $this->assertAuthenticatedAs($membership->user);

        $this->getJson('/api/v1/auth/me', ['X-Company-Id' => (string) $company->id])
            ->assertOk()
            ->assertJsonPath('data.user.id', $membership->user_id)
            ->assertJsonPath('data.companies.0.id', $company->id)
            ->assertJsonPath('data.companies.0.permissions.0', 'inventory.view');
    }

    public function test_an_unlinked_identity_is_rejected_without_a_session(): void
    {
        $this->fakeProvider('google', 'google-unknown');

        $this->get('/auth/social/google/callback')
            ->assertRedirect('http://localhost:5173/login?social_error=not_linked');

        $this->assertGuest();
    }

    public function test_a_linked_user_can_link_a_provider_from_an_authenticated_session(): void
    {
        $company = Company::factory()->create();
        $membership = $this->member($company, ['inventory.view']);
        $this->fakeProvider('google', 'google-new');

        $this->actingAs($membership->user)
            ->withSession(['social_intent' => 'link'])
            ->get('/auth/social/google/callback')
            ->assertRedirect('http://localhost:5173/account?social=linked');

        $this->assertDatabaseHas('social_identities', [
            'user_id' => $membership->user_id,
            'provider' => 'google',
            'provider_user_id' => 'google-new',
        ]);
    }

    public function test_linking_an_identity_owned_by_another_user_is_rejected(): void
    {
        $company = Company::factory()->create();
        $membership = $this->member($company, ['inventory.view']);
        $other = User::factory()->create();
        SocialIdentity::factory()->create([
            'user_id' => $other->id,
            'provider' => 'google',
            'provider_user_id' => 'google-owned',
        ]);
        $this->fakeProvider('google', 'google-owned');

        $this->actingAs($membership->user)
            ->withSession(['social_intent' => 'link'])
            ->get('/auth/social/google/callback')
            ->assertRedirect('http://localhost:5173/account?social_error=conflict');

        $this->assertDatabaseCount('social_identities', 1);
        $this->assertDatabaseMissing('social_identities', [
            'user_id' => $membership->user_id,
        ]);
    }

    public function test_a_user_cannot_link_two_identities_of_the_same_provider(): void
    {
        $company = Company::factory()->create();
        $membership = $this->member($company, ['inventory.view']);
        SocialIdentity::factory()->create([
            'user_id' => $membership->user_id,
            'provider' => 'google',
            'provider_user_id' => 'google-first',
        ]);
        $this->fakeProvider('google', 'google-second');

        $this->actingAs($membership->user)
            ->withSession(['social_intent' => 'link'])
            ->get('/auth/social/google/callback')
            ->assertRedirect('http://localhost:5173/account?social_error=conflict');

        $this->assertDatabaseCount('social_identities', 1);
    }

    public function test_social_accounts_are_listed_and_can_be_unlinked(): void
    {
        $company = Company::factory()->create();
        $membership = $this->member($company, ['inventory.view']);
        SocialIdentity::factory()->create([
            'user_id' => $membership->user_id,
            'provider' => 'google',
            'provider_user_id' => 'google-123',
        ]);

        $this->actingAs($membership->user)
            ->getJson('/api/v1/auth/social/accounts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.provider', 'google')
            ->assertJsonPath('data.0.label', 'Google');

        $this->deleteJson('/api/v1/auth/social/google')
            ->assertNoContent();

        $this->assertDatabaseCount('social_identities', 0);
    }

    public function test_only_configured_providers_are_exposed_to_the_login_ui(): void
    {
        $this->getJson('/api/v1/auth/social/providers')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.provider', 'google');
    }

    public function test_social_accounts_require_authentication(): void
    {
        $this->getJson('/api/v1/auth/social/accounts')->assertUnauthorized();
    }

    public function test_an_unconfigured_provider_redirects_to_a_safe_login_error(): void
    {
        $this->get('/auth/social/facebook/redirect')
            ->assertRedirect('http://localhost:5173/login?social_error=provider_unavailable');
    }

    private function fakeProvider(string $provider, string $providerUserId): void
    {
        $socialUser = (new SocialiteUser)->map([
            'id' => $providerUserId,
            'name' => 'Usuario Social',
            'email' => 'social@example.test',
        ]);

        $driver = Mockery::mock(SocialiteProvider::class);
        $driver->shouldReceive('user')->andReturn($socialUser);

        $factory = Mockery::mock(SocialiteFactory::class);
        $factory->shouldReceive('driver')->with($provider)->andReturn($driver);

        $this->app->instance(SocialiteFactory::class, $factory);
    }
}
