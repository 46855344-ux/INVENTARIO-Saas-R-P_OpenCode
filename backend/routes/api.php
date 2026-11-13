<?php

use App\Modules\Catalog\Http\Controllers\BrandController;
use App\Modules\Catalog\Http\Controllers\CategoryController;
use App\Modules\Catalog\Http\Controllers\ProductController;
use App\Modules\Catalog\Http\Controllers\ProductVariantController;
use App\Modules\Identity\Http\Controllers\AuthController;
use App\Modules\Identity\Http\Controllers\PermissionController;
use App\Modules\Identity\Http\Controllers\RoleController;
use App\Modules\Identity\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API routes
|--------------------------------------------------------------------------
| Los recursos se nombran en plural y en inglés. El ámbito de empresa se
| resuelve desde la cabecera validada X-Company-Id (ver ADR-0002).
*/

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => config('app.name'),
        'time' => now()->toIso8601String(),
    ]);
});

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])
            ->middleware('can:catalog.view');
        Route::post('/categories', [CategoryController::class, 'store'])
            ->middleware('can:catalog.manage');
        Route::get('/categories/{id}', [CategoryController::class, 'show'])
            ->middleware('can:catalog.view');
        Route::put('/categories/{id}', [CategoryController::class, 'update'])
            ->middleware('can:catalog.manage');
        Route::patch('/categories/{id}/status', [CategoryController::class, 'updateStatus'])
            ->middleware('can:catalog.manage');

        Route::get('/brands', [BrandController::class, 'index'])
            ->middleware('can:catalog.view');
        Route::post('/brands', [BrandController::class, 'store'])
            ->middleware('can:catalog.manage');
        Route::get('/brands/{id}', [BrandController::class, 'show'])
            ->middleware('can:catalog.view');
        Route::put('/brands/{id}', [BrandController::class, 'update'])
            ->middleware('can:catalog.manage');
        Route::patch('/brands/{id}/status', [BrandController::class, 'updateStatus'])
            ->middleware('can:catalog.manage');

        Route::get('/products', [ProductController::class, 'index'])
            ->middleware('can:catalog.view');
        Route::post('/products', [ProductController::class, 'store'])
            ->middleware('can:catalog.manage');
        Route::get('/products/{product}/variants', [ProductVariantController::class, 'index'])
            ->middleware('can:catalog.view');
        Route::post('/products/{product}/variants', [ProductVariantController::class, 'store'])
            ->middleware('can:catalog.manage');
        Route::get('/products/{id}', [ProductController::class, 'show'])
            ->middleware('can:catalog.view');
        Route::put('/products/{id}', [ProductController::class, 'update'])
            ->middleware('can:catalog.manage');
        Route::patch('/products/{id}/status', [ProductController::class, 'updateStatus'])
            ->middleware('can:catalog.manage');

        Route::get('/variants/{id}', [ProductVariantController::class, 'show'])
            ->middleware('can:catalog.view');
        Route::put('/variants/{id}', [ProductVariantController::class, 'update'])
            ->middleware('can:catalog.manage');
        Route::patch('/variants/{id}/status', [ProductVariantController::class, 'updateStatus'])
            ->middleware('can:catalog.manage');

        Route::get('/roles', [RoleController::class, 'index'])
            ->middleware('can:identity.roles.view');
        Route::get('/permissions', [PermissionController::class, 'index'])
            ->middleware('can:identity.roles.view');

        Route::get('/users', [UserController::class, 'index'])
            ->middleware('can:identity.users.view');
        Route::post('/users', [UserController::class, 'store'])
            ->middleware('can:identity.users.manage');
        Route::get('/users/{id}', [UserController::class, 'show'])
            ->middleware('can:identity.users.view');
        Route::put('/users/{id}', [UserController::class, 'update'])
            ->middleware('can:identity.users.manage');
        Route::patch('/users/{id}/status', [UserController::class, 'updateStatus'])
            ->middleware('can:identity.users.manage');
        Route::put('/users/{id}/role', [UserController::class, 'assignRole'])
            ->middleware('can:identity.users.manage');
    });
});
