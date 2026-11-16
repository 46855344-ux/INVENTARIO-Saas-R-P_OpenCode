<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Exceptions\AuthenticationFailedException;
use App\Modules\Identity\Exceptions\SocialAuthFailedException;
use App\Modules\Identity\Exceptions\SocialIdentityConflictException;
use App\Modules\Identity\Exceptions\SocialIdentityNotLinkedException;
use App\Modules\Identity\Services\SocialAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Inicio y callback OAuth. Vive en rutas `web` para conservar sesión y `state`;
 * la sesión final es la misma cookie Sanctum de la SPA.
 */
class SocialAuthController extends Controller
{
    public function __construct(private readonly SocialAuthService $social) {}

    public function redirect(Request $request, string $provider): RedirectResponse
    {
        try {
            $this->social->assertConfigured($provider);
        } catch (SocialAuthFailedException) {
            return $this->frontend('/login', ['social_error' => 'provider_unavailable']);
        }

        $request->session()->put('social_intent', 'login');

        return $this->social->redirectToProvider($provider);
    }

    public function link(Request $request, string $provider): RedirectResponse
    {
        try {
            $this->social->assertConfigured($provider);
        } catch (SocialAuthFailedException) {
            return $this->frontend('/account', ['social_error' => 'provider_unavailable']);
        }

        $request->session()->put('social_intent', 'link');

        return $this->social->redirectToProvider($provider);
    }

    public function callback(Request $request, string $provider): RedirectResponse
    {
        $intent = $request->session()->pull('social_intent', 'login');

        try {
            $this->social->assertConfigured($provider);
            $providerUser = $this->social->providerUser($provider);

            if ($intent === 'link') {
                $user = $request->user();

                if ($user === null) {
                    return $this->frontend('/login', ['social_error' => 'session_expired']);
                }

                $this->social->link($user, $provider, $providerUser);

                return $this->frontend('/account', ['social' => 'linked']);
            }

            $user = $this->social->login($provider, $providerUser);

            Auth::guard('web')->login($user, true);
            $request->session()->regenerate();

            return $this->frontend('/');
        } catch (SocialIdentityNotLinkedException) {
            return $this->frontend('/login', ['social_error' => 'not_linked']);
        } catch (SocialIdentityConflictException) {
            return $this->frontend('/account', ['social_error' => 'conflict']);
        } catch (AuthenticationFailedException) {
            return $this->frontend('/login', ['social_error' => 'account_inactive']);
        } catch (Throwable $exception) {
            report($exception);

            $path = $intent === 'link' ? '/account' : '/login';

            return $this->frontend($path, ['social_error' => 'failed']);
        }
    }

    /**
     * @param  array<string, string>  $query
     */
    private function frontend(string $path, array $query = []): RedirectResponse
    {
        $base = rtrim((string) config('app.frontend_url'), '/');
        $url = $base.$path;

        if ($query !== []) {
            $url .= '?'.http_build_query($query);
        }

        return redirect()->away($url);
    }
}
