<?php

namespace App\Modules\Identity\Exceptions;

use Exception;

/**
 * El proveedor social no está configurado o rechazó el flujo.
 */
final class SocialAuthFailedException extends Exception
{
    public function __construct(string $message = 'No se pudo completar el acceso con el proveedor.')
    {
        parent::__construct($message);
    }
}
