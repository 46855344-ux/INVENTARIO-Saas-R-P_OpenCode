<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Http\Resources\SocialIdentityResource;
use App\Modules\Identity\Models\SocialIdentity;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\SocialAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class SocialAccountController extends Controller
{
    public function __construct(private readonly SocialAuthService $social) {}

    public function providers(): JsonResponse
    {
        $providers = array_map(
            fn (string $provider): array => [
                'provider' => $provider,
                'label' => SocialIdentity::providerLabel($provider),
            ],
            $this->social->configuredProviders(),
        );

        return response()->json(['data' => $providers]);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        return SocialIdentityResource::collection($this->social->accounts($user));
    }

    public function destroy(Request $request, string $provider): Response
    {
        /** @var User $user */
        $user = $request->user();

        $this->social->unlink($user, $provider);

        return response()->noContent();
    }
}
