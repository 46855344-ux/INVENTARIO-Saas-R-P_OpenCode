<?php

namespace App\Modules\Identity\Services;

use App\Modules\Identity\Exceptions\AuthenticationFailedException;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Reglas de autenticación del módulo de identidad (US-0101).
 * La sesión se gestiona con el guard `web`; Sanctum la valida en las rutas.
 */
class AuthenticationService
{
    public function attempt(string $email, string $password): User
    {
        $user = User::query()->where('email', $email)->first();

        if ($user === null || ! Hash::check($password, $user->password)) {
            throw AuthenticationFailedException::invalidCredentials();
        }

        if (! $user->isActive()) {
            throw AuthenticationFailedException::accountInactive();
        }

        // SessionGuard::login() migra el identificador de sesión (evita fijación).
        Auth::guard('web')->login($user);

        return $user;
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
    }
}
