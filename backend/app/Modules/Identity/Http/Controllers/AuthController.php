<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Http\Requests\LoginRequest;
use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function __construct(private readonly AuthenticationService $authentication) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->authentication->attempt(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
        );

        return response()->json(['data' => $this->sessionPayload($user)]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json(['data' => $this->sessionPayload($user)]);
    }

    public function logout(Request $request): Response
    {
        $this->authentication->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->noContent();
    }

    /**
     * Perfil de sesión: usuario, empresas activas y permisos vigentes.
     *
     * @return array{user: array<string, mixed>, companies: list<array<string, mixed>>, permissions: list<string>}
     */
    private function sessionPayload(User $user): array
    {
        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'companies' => $user->activeCompanies()
                ->orderBy('companies.name')
                ->get()
                ->map(fn (Company $company): array => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'slug' => $company->slug,
                ])
                ->all(),
            // Los permisos se completan con US-0103.
            'permissions' => [],
        ];
    }
}
