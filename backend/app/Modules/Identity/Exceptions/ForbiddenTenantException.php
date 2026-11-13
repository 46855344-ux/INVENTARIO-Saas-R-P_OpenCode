<?php

namespace App\Modules\Identity\Exceptions;

use Exception;

/**
 * La empresa activa no está disponible para el usuario autenticado.
 */
final class ForbiddenTenantException extends Exception
{
    private function __construct(
        string $message,
    ) {
        parent::__construct($message);
    }

    public static function missingCompany(): self
    {
        return new self('Debe indicar una empresa activa válida en la cabecera X-Company-Id.');
    }

    public static function notAllowed(): self
    {
        return new self('No tiene acceso a la empresa indicada.');
    }
}
