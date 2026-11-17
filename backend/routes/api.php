<?php

use App\Modules\Audit\Http\Controllers\AuditLogController;
use App\Modules\Audit\Http\Controllers\StockCountController;
use App\Modules\Catalog\Http\Controllers\BrandController;
use App\Modules\Catalog\Http\Controllers\CategoryController;
use App\Modules\Catalog\Http\Controllers\ProductController;
use App\Modules\Catalog\Http\Controllers\ProductVariantController;
use App\Modules\Identity\Http\Controllers\AuthController;
use App\Modules\Identity\Http\Controllers\PermissionController;
use App\Modules\Identity\Http\Controllers\RoleController;
use App\Modules\Identity\Http\Controllers\SocialAccountController;
use App\Modules\Identity\Http\Controllers\UserController;
use App\Modules\Inventory\Http\Controllers\LocationController;
use App\Modules\Inventory\Http\Controllers\StockController;
use App\Modules\Inventory\Http\Controllers\StockMovementController;
use App\Modules\Inventory\Http\Controllers\UnitController;
use App\Modules\Inventory\Http\Controllers\UnitConversionController;
use App\Modules\Metrics\Http\Controllers\AlertController;
use App\Modules\Metrics\Http\Controllers\AlertRuleController;
use App\Modules\Metrics\Http\Controllers\ChatAssistantController;
use App\Modules\Metrics\Http\Controllers\MetricsController;
use App\Modules\Purchasing\Http\Controllers\PurchaseController;
use App\Modules\Purchasing\Http\Controllers\SupplierController;
use App\Modules\Purchasing\Http\Controllers\TransferController;
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
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:login');
        Route::get('/social/providers', [SocialAccountController::class, 'providers']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::get('/social/accounts', [SocialAccountController::class, 'index']);
            Route::delete('/social/{provider}', [SocialAccountController::class, 'destroy'])
                ->whereIn('provider', ['google', 'facebook']);
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

        Route::get('/variants', [ProductVariantController::class, 'indexAll'])
            ->middleware('can:catalog.view');
        Route::get('/variants/{id}', [ProductVariantController::class, 'show'])
            ->middleware('can:catalog.view');
        Route::put('/variants/{id}', [ProductVariantController::class, 'update'])
            ->middleware('can:catalog.manage');
        Route::patch('/variants/{id}/status', [ProductVariantController::class, 'updateStatus'])
            ->middleware('can:catalog.manage');

        Route::get('/units', [UnitController::class, 'index'])
            ->middleware('can:settings.view');
        Route::post('/units', [UnitController::class, 'store'])
            ->middleware('can:settings.manage');
        Route::get('/units/{id}', [UnitController::class, 'show'])
            ->middleware('can:settings.view');
        Route::put('/units/{id}', [UnitController::class, 'update'])
            ->middleware('can:settings.manage');
        Route::patch('/units/{id}/status', [UnitController::class, 'updateStatus'])
            ->middleware('can:settings.manage');

        Route::get('/conversions', [UnitConversionController::class, 'index'])
            ->middleware('can:settings.view');
        Route::post('/conversions', [UnitConversionController::class, 'store'])
            ->middleware('can:settings.manage');
        Route::post('/conversions/simulate', [UnitConversionController::class, 'simulate'])
            ->middleware('can:settings.view');
        Route::put('/conversions/{id}', [UnitConversionController::class, 'update'])
            ->middleware('can:settings.manage');
        Route::patch('/conversions/{id}/status', [UnitConversionController::class, 'updateStatus'])
            ->middleware('can:settings.manage');

        Route::get('/locations', [LocationController::class, 'index'])
            ->middleware('can:settings.view');
        Route::post('/locations', [LocationController::class, 'store'])
            ->middleware('can:settings.manage');
        Route::get('/locations/{id}', [LocationController::class, 'show'])
            ->middleware('can:settings.view');
        Route::put('/locations/{id}', [LocationController::class, 'update'])
            ->middleware('can:settings.manage');
        Route::patch('/locations/{id}/status', [LocationController::class, 'updateStatus'])
            ->middleware('can:settings.manage');

        Route::get('/inventory/stocks', [StockController::class, 'index'])
            ->middleware('can:inventory.view');
        Route::get('/inventory/movements', [StockMovementController::class, 'index'])
            ->middleware('can:inventory.view');
        Route::get('/inventory/movements/{id}', [StockMovementController::class, 'show'])
            ->middleware('can:inventory.view');
        Route::post('/inventory/entries', [StockMovementController::class, 'storeEntry'])
            ->middleware('can:inventory.move');
        Route::post('/inventory/exits', [StockMovementController::class, 'storeExit'])
            ->middleware('can:inventory.move');
        Route::post('/inventory/returns', [StockMovementController::class, 'storeReturn'])
            ->middleware('can:inventory.move');
        Route::post('/inventory/adjustments', [StockMovementController::class, 'storeAdjustment'])
            ->middleware('can:inventory.adjust');
        Route::post('/inventory/movements/{id}/reverse', [StockMovementController::class, 'reverse'])
            ->middleware('can:inventory.approve');

        Route::get('/suppliers', [SupplierController::class, 'index'])
            ->middleware('can:purchases.view');
        Route::post('/suppliers', [SupplierController::class, 'store'])
            ->middleware('can:purchases.manage');
        Route::get('/suppliers/{id}', [SupplierController::class, 'show'])
            ->middleware('can:purchases.view');
        Route::put('/suppliers/{id}', [SupplierController::class, 'update'])
            ->middleware('can:purchases.manage');
        Route::patch('/suppliers/{id}/status', [SupplierController::class, 'updateStatus'])
            ->middleware('can:purchases.manage');

        Route::get('/purchases', [PurchaseController::class, 'index'])
            ->middleware('can:purchases.view');
        Route::post('/purchases', [PurchaseController::class, 'store'])
            ->middleware('can:purchases.manage');
        Route::get('/purchases/{id}', [PurchaseController::class, 'show'])
            ->middleware('can:purchases.view');
        Route::put('/purchases/{id}', [PurchaseController::class, 'update'])
            ->middleware('can:purchases.manage');
        Route::post('/purchases/{id}/receive', [PurchaseController::class, 'receive'])
            ->middleware('can:purchases.receive');

        Route::get('/transfers', [TransferController::class, 'index'])
            ->middleware('can:transfers.view');
        Route::post('/transfers', [TransferController::class, 'store'])
            ->middleware('can:transfers.manage');
        Route::get('/transfers/{id}', [TransferController::class, 'show'])
            ->middleware('can:transfers.view');

        Route::get('/metrics/overview', [MetricsController::class, 'overview'])
            ->middleware('can:metrics.view');
        Route::get('/metrics/stock-detail', [MetricsController::class, 'stockDetail'])
            ->middleware('can:metrics.view');

        Route::get('/alerts', [AlertController::class, 'index'])
            ->middleware('can:alerts.view');
        Route::patch('/alerts/{id}/status', [AlertController::class, 'updateStatus'])
            ->middleware('can:alerts.manage');

        Route::get('/alert-rules', [AlertRuleController::class, 'index'])
            ->middleware('can:alerts.view');
        Route::post('/alert-rules', [AlertRuleController::class, 'store'])
            ->middleware('can:alerts.manage');
        Route::patch('/alert-rules/{id}', [AlertRuleController::class, 'update'])
            ->middleware('can:alerts.manage');

        Route::get('/stock-counts', [StockCountController::class, 'index'])
            ->middleware('can:counts.view');
        Route::post('/stock-counts', [StockCountController::class, 'store'])
            ->middleware('can:counts.manage');
        Route::get('/stock-counts/{id}', [StockCountController::class, 'show'])
            ->middleware('can:counts.view');
        Route::patch('/stock-counts/{id}/status', [StockCountController::class, 'updateStatus'])
            ->middleware('can:counts.view');

        Route::get('/audit-events', [AuditLogController::class, 'index'])
            ->middleware('can:audit.view');

        Route::post('/assistant/query', [ChatAssistantController::class, 'query'])
            ->middleware(['can:assistant.use', 'throttle:assistant']);
        Route::get('/assistant/suggestions', [ChatAssistantController::class, 'suggestions'])
            ->middleware(['can:assistant.use', 'throttle:assistant']);

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
