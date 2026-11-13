<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Http\Requests\LoginRequest;
use App\Modules\Identity\Models\Company;
use App\Modules\Identity\Models\CompanyUser;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

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
     * Perfil de sesión: usuario, empresas activas con su rol y permisos.
     *
     * @return array{user: array<string, mixed>, companies: list<array<string, mixed>>, permissions: list<string>}
     */
    private function sessionPayload(User $user): array
    {
        /** @var Collection<int, CompanyUser> $memberships */
        $memberships = CompanyUser::query()
            ->where('user_id', $user->id)
            ->where('status', CompanyUser::STATUS_ACTIVE)
            ->whereHas('company', fn ($query) => $query->where('status', Company::STATUS_ACTIVE))
            ->with(['company', 'role.permissions'])
            ->get()
            ->sortBy(fn (CompanyUser $membership): string => $membership->company->name)
            ->values();

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'companies' => $memberships
                ->map(fn (CompanyUser $membership): array => [
                    'id' => $membership->company->id,
                    'name' => $membership->company->name,
                    'slug' => $membership->company->slug,
                    'role' => $membership->role ? [
                        'id' => $membership->role->id,
                        'code' => $membership->role->code,
                        'name' => $membership->role->name,
                    ] : null,
                    'permissions' => $membership->role?->permissionCodes() ?? [],
                ])
                ->all(),
            'permissions' => $memberships
                ->flatMap(fn (CompanyUser $membership): array => $membership->role?->permissionCodes() ?? [])
                ->unique()
                ->values()
                ->all(),
        ];
    }
}
