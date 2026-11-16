<?php

namespace App\Modules\Identity\Services;

use App\Modules\Identity\Exceptions\AuthenticationFailedException;
use App\Modules\Identity\Exceptions\LastAuthMethodException;
use App\Modules\Identity\Exceptions\SocialAuthFailedException;
use App\Modules\Identity\Exceptions\SocialIdentityConflictException;
use App\Modules\Identity\Exceptions\SocialIdentityNotLinkedException;
use App\Modules\Identity\Models\SocialIdentity;
use App\Modules\Identity\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Contracts\User as SocialUser;
use Laravel\Socialite\Facades\Socialite;

/**
 * Resuelve identidades de Google/Facebook contra el usuario interno.
 * La identidad externa autentica; la empresa, el rol y los permisos se
 * resuelven siempre en la base interna.
 */
class SocialAuthService
{
    /**
     * @return list<string>
     */
    public function configuredProviders(): array
    {
        return array_values(array_filter(
            SocialIdentity::PROVIDERS,
            fn (string $provider): bool => $this->isConfigured($provider),
        ));
    }

    public function isConfigured(string $provider): bool
    {
        if (! in_array($provider, SocialIdentity::PROVIDERS, true)) {
            return false;
        }

        return filled(config("services.{$provider}.client_id"))
            && filled(config("services.{$provider}.client_secret"));
    }

    public function assertConfigured(string $provider): void
    {
        if (! in_array($provider, SocialIdentity::PROVIDERS, true)) {
            throw new SocialAuthFailedException('El proveedor de identidad no es válido.');
        }

        if (! $this->isConfigured($provider)) {
            throw new SocialAuthFailedException('El proveedor de identidad no está configurado.');
        }
    }

    public function providerUser(string $provider): SocialUser
    {
        return Socialite::driver($provider)->user();
    }

    public function redirectToProvider(string $provider): RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }

    public function login(string $provider, SocialUser $providerUser): User
    {
        $identity = $this->findIdentity($provider, (string) $providerUser->getId());

        if ($identity === null) {
            throw new SocialIdentityNotLinkedException;
        }

        $user = $identity->user;

        if ($user === null || ! $user->isActive()) {
            throw AuthenticationFailedException::accountInactive();
        }

        Auth::guard('web')->login($user, true);

        return $user;
    }

    public function link(User $user, string $provider, SocialUser $providerUser): SocialIdentity
    {
        $providerUserId = (string) $providerUser->getId();

        $existing = $this->findIdentity($provider, $providerUserId);

        if ($existing !== null) {
            if ((int) $existing->user_id === (int) $user->id) {
                return $existing;
            }

            throw new SocialIdentityConflictException;
        }

        if ($user->socialIdentities()->where('provider', $provider)->exists()) {
            throw new SocialIdentityConflictException;
        }

        try {
            return SocialIdentity::query()->create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_user_id' => $providerUserId,
                'linked_at' => now(),
            ]);
        } catch (QueryException) {
            throw new SocialIdentityConflictException;
        }
    }

    public function unlink(User $user, string $provider): void
    {
        $identity = $user->socialIdentities()->where('provider', $provider)->first();

        if ($identity === null) {
            return;
        }

        if (blank($user->password) && $user->socialIdentities()->count() <= 1) {
            throw new LastAuthMethodException;
        }

        $identity->delete();
    }

    /**
     * @return Collection<int, SocialIdentity>
     */
    public function accounts(User $user): Collection
    {
        return $user->socialIdentities()->orderBy('provider')->get();
    }

    private function findIdentity(string $provider, string $providerUserId): ?SocialIdentity
    {
        return SocialIdentity::query()
            ->with('user')
            ->where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->first();
    }
}
