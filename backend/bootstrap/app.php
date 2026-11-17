<?php

use App\Modules\Audit\Exceptions\InvalidStockCountStatusException;
use App\Modules\Identity\Exceptions\AuthenticationFailedException;
use App\Modules\Identity\Exceptions\ForbiddenException;
use App\Modules\Identity\Exceptions\ForbiddenTenantException;
use App\Modules\Identity\Exceptions\LastAuthMethodException;
use App\Modules\Identity\Exceptions\SocialAuthFailedException;
use App\Modules\Identity\Exceptions\SocialIdentityConflictException;
use App\Modules\Identity\Exceptions\SocialIdentityNotLinkedException;
use App\Modules\Identity\Http\Middleware\ResolveTenant;
use App\Modules\Inventory\Exceptions\ConversionNotDefinedException;
use App\Modules\Inventory\Exceptions\IncompatibleUnitsException;
use App\Modules\Inventory\Exceptions\InsufficientStockException;
use App\Modules\Inventory\Exceptions\MovementAlreadyReversedException;
use App\Modules\Inventory\Exceptions\MovementNotReversibleException;
use App\Modules\Purchasing\Exceptions\IdempotencyConflictException;
use App\Modules\Purchasing\Exceptions\PurchaseNotEditableException;
use App\Modules\Shared\Http\ErrorResponse;
use App\Modules\Shared\Http\Middleware\SecurityHeaders;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Autenticación de SPA con cookies de Sanctum (ver ADR-0001).
        $middleware->statefulApi();

        $middleware->alias([
            'tenant' => ResolveTenant::class,
        ]);

        $middleware->append(SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ErrorResponse::make(
                'VALIDATION_ERROR',
                'Los datos enviados no son válidos.',
                422,
                ['fields' => $e->errors()],
            );
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ErrorResponse::make(
                'UNAUTHENTICATED',
                'Debe iniciar sesión para continuar.',
                401,
            );
        });

        $exceptions->render(function (AuthenticationFailedException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ErrorResponse::make($e->errorCode, $e->getMessage(), $e->status);
        });

        $exceptions->render(function (ForbiddenTenantException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ErrorResponse::make('FORBIDDEN_TENANT', $e->getMessage(), 403);
        });

        $exceptions->render(function (ForbiddenException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ErrorResponse::make('FORBIDDEN', $e->getMessage(), 403);
        });

        $exceptions->render(function (SocialAuthFailedException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ErrorResponse::make('SOCIAL_AUTH_FAILED', $e->getMessage(), 401);
        });

        $exceptions->render(function (SocialIdentityNotLinkedException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ErrorResponse::make('SOCIAL_IDENTITY_NOT_LINKED', $e->getMessage(), 403);
        });

        $exceptions->render(function (SocialIdentityConflictException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ErrorResponse::make('SOCIAL_IDENTITY_CONFLICT', $e->getMessage(), 409);
        });

        $exceptions->render(function (LastAuthMethodException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ErrorResponse::make('LAST_AUTH_METHOD', $e->getMessage(), 409);
        });

        $exceptions->render(function (InvalidStockCountStatusException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ErrorResponse::make('INVALID_COUNT_STATUS', $e->getMessage(), 409);
        });

        $exceptions->render(function (IncompatibleUnitsException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ErrorResponse::make('INCOMPATIBLE_UNITS', $e->getMessage(), 422);
        });

        $exceptions->render(function (ConversionNotDefinedException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ErrorResponse::make('CONVERSION_NOT_DEFINED', $e->getMessage(), 422);
        });

        $exceptions->render(function (InsufficientStockException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ErrorResponse::make('INSUFFICIENT_STOCK', $e->getMessage(), 409);
        });

        $exceptions->render(function (MovementAlreadyReversedException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ErrorResponse::make('ALREADY_REVERSED', $e->getMessage(), 409);
        });

        $exceptions->render(function (MovementNotReversibleException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ErrorResponse::make('NOT_REVERSIBLE', $e->getMessage(), 409);
        });

        $exceptions->render(function (PurchaseNotEditableException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ErrorResponse::make('PURCHASE_NOT_EDITABLE', $e->getMessage(), 409);
        });

        $exceptions->render(function (IdempotencyConflictException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ErrorResponse::make('IDEMPOTENCY_CONFLICT', $e->getMessage(), 409);
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return match ($e->getStatusCode()) {
                403 => ErrorResponse::make(
                    'FORBIDDEN',
                    'No tiene permiso para realizar esta acción.',
                    403,
                ),
                404 => ErrorResponse::make(
                    'NOT_FOUND',
                    'El recurso solicitado no existe.',
                    404,
                ),
                default => null,
            };
        });
    })->create();
