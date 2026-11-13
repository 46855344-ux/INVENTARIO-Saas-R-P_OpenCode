<?php

use App\Modules\Identity\Exceptions\AuthenticationFailedException;
use App\Modules\Identity\Exceptions\ForbiddenException;
use App\Modules\Identity\Exceptions\ForbiddenTenantException;
use App\Modules\Identity\Http\Middleware\EnsurePermission;
use App\Modules\Identity\Http\Middleware\ResolveTenant;
use App\Modules\Shared\Http\ErrorResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            'permission' => EnsurePermission::class,
        ]);
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

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return ErrorResponse::make('NOT_FOUND', 'El recurso solicitado no existe.', 404);
        });
    })->create();
