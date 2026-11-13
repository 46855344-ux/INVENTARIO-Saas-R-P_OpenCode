<?php

namespace App\Modules\Identity\Exceptions;

use Exception;

/**
 * Fallo de autenticación con un código de error de dominio propio.
 * El renderizador de excepciones lo traduce al contrato único de error.
 */
final class AuthenticationFailedException extends Exception
{
    private function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly int $status,
    ) {
        parent::__construct($message);
    }

    public static function invalidCredentials(): self
    {
        return new self(
            'INVALID_CREDENTIALS',
            'El correo electrónico o la contraseña no son válidos.',
            401,
        );
    }

    public static function accountInactive(): self
    {
        return new self(
            'ACCOUNT_INACTIVE',
            'La cuenta está inactiva. Contacte a un administrador.',
            403,
        );
    }
}
