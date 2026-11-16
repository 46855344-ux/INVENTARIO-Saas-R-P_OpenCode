<?php

use App\Modules\Identity\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| OAuth social
|--------------------------------------------------------------------------
| El inicio y el callback viven en `web` para conservar la sesión y el `state`.
| Al finalizar, Laravel crea la sesión Sanctum que consume la SPA.
*/

Route::get('/auth/social/{provider}/redirect', [SocialAuthController::class, 'redirect'])
    ->whereIn('provider', ['google', 'facebook'])
    ->middleware('throttle:10,1')
    ->name('social.redirect');

Route::get('/auth/social/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->whereIn('provider', ['google', 'facebook'])
    ->middleware('throttle:20,1')
    ->name('social.callback');

Route::get('/auth/social/{provider}/link', [SocialAuthController::class, 'link'])
    ->whereIn('provider', ['google', 'facebook'])
    ->middleware(['auth', 'throttle:10,1'])
    ->name('social.link');
