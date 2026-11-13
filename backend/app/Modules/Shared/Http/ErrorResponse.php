<?php

namespace App\Modules\Shared\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * Construye la respuesta de error única de la API
 * (ver ESTANDAR.md, sección 6.8 y SDD-05, sección 5).
 */
final class ErrorResponse
{
    /**
     * @param  array<string, mixed>  $details
     */
    public static function make(string $code, string $message, int $status, array $details = []): JsonResponse
    {
        $error = [
            'code' => $code,
            'message' => $message,
        ];

        if ($details !== []) {
            $error['details'] = $details;
        }

        $error['trace_id'] = (string) Str::ulid();

        return response()->json(['error' => $error], $status);
    }
}
